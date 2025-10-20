<?php
/**
 * Plugin Name: PS Product Site - CSV Importer
 * Description: 为 PS Product Site 插件增加 CSV 导入页面（产品 → 导入）。支持用 UTF-8 带 BOM 的 CSV 导入。
 * Version: 1.0.0
 * Author: 超級の新人
 */

if (!defined('ABSPATH')) exit;

class PS_Product_Site_CSV_Importer {
  const CPT = 'ps_product';
  const TAX = 'ps_category';

  public function __construct() {
    add_action('admin_menu', [$this, 'menu']);
  }

  public function menu() {
    add_submenu_page(
      'edit.php?post_type=' . self::CPT,
      '导入产品 (CSV)',
      '导入 (CSV)',
      'manage_options',
      'ps-importer',
      [$this, 'render_page']
    );
  }

  private function sanitize_text($s) {
    return is_string($s) ? wp_kses_post($s) : '';
  }

  public function render_page() {
    if (!current_user_can('manage_options')) return;
    echo '<div class="wrap"><h1>导入产品（CSV）</h1>';
    if (!empty($_POST['ps_do_import']) && check_admin_referer('ps_import_csv', 'ps_nonce')) {
      $this->handle_import();
    } else {
      echo '<p>请上传 UTF-8（含 BOM）编码的 CSV。建议先用“下载样例 CSV”对照列名。</p>';
      echo '<form method="post" enctype="multipart/form-data">';
      wp_nonce_field('ps_import_csv', 'ps_nonce');
      echo '<input type="file" name="ps_csv" accept=".csv" required>';
      echo '<p class="submit"><input type="submit" class="button button-primary" name="ps_do_import" value="开始导入"></p>';
      echo '</form>';
      echo '<p><a class="button" href="' . esc_url(plugins_url('sample.csv', __FILE__)) . '">下载样例 CSV</a></p>';
    }
    echo '</div>';
  }

  private function handle_import() {
    if (empty($_FILES['ps_csv']['name'])) {
      echo '<div class="notice notice-error"><p>请选择 CSV 文件。</p></div>';
      return;
    }
    $file = $_FILES['ps_csv'];
    $overrides = ['test_form' => false, 'mimes' => ['csv' => 'text/csv']];
    $movefile = wp_handle_upload($file, $overrides);
    if (!empty($movefile['error'])) {
      echo '<div class="notice notice-error"><p>上传失败：' . esc_html($movefile['error']) . '</p></div>';
      return;
    }
    $path = $movefile['file'];
    $created = 0; $updated = 0; $errors = 0;
    $required_cols = ['title'];
    $rownum = 0;

    if (($fh = fopen($path, 'r')) !== false) {
      // Read header
      $header = fgetcsv($fh);
      if (!$header) {
        echo '<div class="notice notice-error"><p>CSV 无表头或空文件。</p></div>';
        fclose($fh); return;
      }
      // Convert header to associative indices
      $idx = array_flip($header);

      while (($row = fgetcsv($fh)) !== false) {
        $rownum++;
        $get = function($key) use ($idx, $row) {
          return isset($idx[$key]) ? $row[$idx[$key]] : '';
        };

        $title = $this->sanitize_text($get('title'));
        if (!$title) { $errors++; continue; }

        $desc  = $this->sanitize_text($get('desc'));
        $sub   = $this->sanitize_text($get('sub'));
        $cat   = $this->sanitize_text($get('内容栏目'));

        $img1 = esc_url_raw($get('img1'));
        $A5   = esc_url_raw($get('A5'));
        $A8   = esc_url_raw($get('A8'));
        $A11  = esc_url_raw($get('A11'));

        $A6  = $this->sanitize_text($get('A6'));
        $A7  = $this->sanitize_text($get('A7'));
        $A9  = $this->sanitize_text($get('A9'));
        $A10 = $this->sanitize_text($get('A10'));
        $A12 = $this->sanitize_text($get('A12'));
        $U17 = $this->sanitize_text($get('Unnamed:_17'));
        $U18 = $this->sanitize_text($get('Unnamed:_18'));

        $table1 = $this->sanitize_text($get('table1'));
        $table2 = $this->sanitize_text($get('table2'));

        // Create or update by title
        $existing = get_page_by_title($title, OBJECT, self::CPT);
        if ($existing) {
          $post_id = $existing->ID;
          wp_update_post([
            'ID' => $post_id,
            'post_content' => $desc,
          ]);
          $updated++;
        } else {
          $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_type'    => self::CPT,
            'post_status'  => 'publish',
            'post_content' => $desc,
          ]);
          if (is_wp_error($post_id)) { $errors++; continue; }
          $created++;
        }

        // Taxonomy
        if ($cat) {
          wp_set_object_terms($post_id, [$cat], self::TAX, false);
        }

        // Meta mapping (对接 ps-product-site 插件字段)
        update_post_meta($post_id, 'ps_sub', $sub);
        update_post_meta($post_id, 'ps_img1', $img1);
        update_post_meta($post_id, 'ps_img2', $A5);
        update_post_meta($post_id, 'ps_img3', $A8);
        update_post_meta($post_id, 'ps_img4', $A11);

        update_post_meta($post_id, 'ps_features_title', $A6);
        update_post_meta($post_id, 'ps_features_lines', $A7);
        update_post_meta($post_id, 'ps_scenarios_title', $A9);
        update_post_meta($post_id, 'ps_scenarios_lines', $A10);

        update_post_meta($post_id, 'ps_table1', $table1);
        update_post_meta($post_id, 'ps_table2', $table2);

        update_post_meta($post_id, 'ps_extra_text', $A12);
        update_post_meta($post_id, 'ps_extra2', $U17);
        update_post_meta($post_id, 'ps_extra3', $U18);
      }
      fclose($fh);
    } else {
      echo '<div class="notice notice-error"><p>CSV 打开失败。</p></div>';
      return;
    }

    echo '<div class="notice notice-success"><p>导入完成：新建 ' . intval($created) . ' 条，更新 ' . intval($updated) . ' 条，失败 ' . intval($errors) . ' 条。</p></div>';
    echo '<p><a class="button" href="' . esc_url(admin_url('edit.php?post_type=' . self::CPT)) . '">查看产品列表</a></p>';
  }
}

new PS_Product_Site_CSV_Importer();

<?php
/**
 * Plugin Name: PS Product Site (Catalog + JSON + Shortcode)
 * Description: 基于上传的前端模板，提供“产品”CPT、后台字段、REST JSON 输出与短代码展示（与 Avada/任意主题兼容）。
 * Version: 1.0.0
 * Author: 超級の新人
 */

if (!defined('ABSPATH')) exit;

class PS_Product_Site_Plugin {
  const CPT = 'ps_product';
  const TAX = 'ps_category';
  const OPTION_PAGE_SLUG = 'ps_product_site_settings';

  public function __construct() {
    add_action('init', [$this, 'register_cpt_tax']);
    add_action('add_meta_boxes', [$this, 'register_metaboxes']);
    add_action('save_post', [$this, 'save_meta'], 10, 2);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    add_action('rest_api_init', [$this, 'register_rest']);
    add_shortcode('product_catalog', [$this, 'shortcode_catalog']);
    register_activation_hook(__FILE__, [$this, 'on_activate']);
  }

  public function register_cpt_tax() {
    // CPT: 产品
    register_post_type(self::CPT, [
      'labels' => [
        'name' => '产品',
        'singular_name' => '产品',
        'add_new' => '新建产品',
        'add_new_item' => '新建产品',
        'edit_item' => '编辑产品',
        'new_item' => '新产品',
        'view_item' => '查看产品',
        'search_items' => '搜索产品',
        'not_found' => '未找到产品',
        'not_found_in_trash' => '回收站无产品',
        'menu_name' => '产品',
      ],
      'public' => true,
      'show_in_menu' => true,
      'menu_icon' => 'dashicons-products',
      'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
      'has_archive' => true,
      'rewrite' => ['slug' => 'products'],
      'show_in_rest' => true,
    ]);

    // Tax: 产品分类（对应前端 JSON 的“内容栏目”字段）
    register_taxonomy(self::TAX, [self::CPT], [
      'labels' => [
        'name' => '产品分类',
        'singular_name' => '产品分类',
        'search_items' => '搜索分类',
        'all_items' => '所有分类',
        'edit_item' => '编辑分类',
        'update_item' => '更新分类',
        'add_new_item' => '新增分类',
        'new_item_name' => '新分类名称',
        'menu_name' => '产品分类',
      ],
      'public' => true,
      'hierarchical' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,
      'rewrite' => ['slug' => 'product-category'],
    ]);
  }

  public function register_metaboxes() {
    add_meta_box('ps_product_info', '产品信息（前端展示字段）', [$this, 'render_metabox'], self::CPT, 'normal', 'default');
  }

  private function field($key, $default = '') {
    $val = get_post_meta(get_the_ID(), $key, true);
    return is_string($val) ? $val : $default;
  }

  public function render_metabox($post) {
    wp_nonce_field('ps_save_meta', 'ps_meta_nonce');
    $meta = [
      'ps_sub' => $this->field('ps_sub'),
      'ps_img1' => $this->field('ps_img1'),
      'ps_img2' => $this->field('ps_img2'),
      'ps_img3' => $this->field('ps_img3'),
      'ps_img4' => $this->field('ps_img4'),
      'ps_features_title' => $this->field('ps_features_title'),
      'ps_features_lines' => $this->field('ps_features_lines'),
      'ps_scenarios_title' => $this->field('ps_scenarios_title'),
      'ps_scenarios_lines' => $this->field('ps_scenarios_lines'),
      'ps_table1' => $this->field('ps_table1'),
      'ps_table2' => $this->field('ps_table2'),
      'ps_extra_text' => $this->field('ps_extra_text'),
      'ps_extra2' => $this->field('ps_extra2'),
      'ps_extra3' => $this->field('ps_extra3'),
    ];
    ?>
    <div class="ps-metabox">
      <p><strong>提示：</strong>正文（编辑器）内容会作为“产品描述（desc）”。分类请在右侧“产品分类”中选择。</p>
      <table class="form-table">
        <tr>
          <th><label for="ps_sub">型号（sub）</label></th>
          <td><input type="text" name="ps_sub" id="ps_sub" class="regular-text" value="<?php echo esc_attr($meta['ps_sub']); ?>"></td>
        </tr>

        <tr><th colspan="2"><h3>图片（建议使用媒体库按钮上传）</h3></th></tr>
        <?php foreach (['ps_img1'=>'主图（img1）','ps_img2'=>'图库 2（A5）','ps_img3'=>'图库 3（A8）','ps_img4'=>'图库 4（A11）'] as $k=>$label): ?>
        <tr>
          <th><label for="<?php echo esc_attr($k); ?>"><?php echo esc_html($label); ?></label></th>
          <td>
            <input type="text" name="<?php echo esc_attr($k); ?>" id="<?php echo esc_attr($k); ?>" class="regular-text" value="<?php echo esc_attr($meta[$k]); ?>">
            <button class="button ps-upload-btn">选择图片</button>
          </td>
        </tr>
        <?php endforeach; ?>

        <tr><th colspan="2"><h3>亮点（features）</h3></th></tr>
        <tr>
          <th><label for="ps_features_title">亮点标题（A6）</label></th>
          <td><input type="text" name="ps_features_title" id="ps_features_title" class="regular-text" value="<?php echo esc_attr($meta['ps_features_title']); ?>"></td>
        </tr>
        <tr>
          <th><label for="ps_features_lines">亮点条目（A7，一行一个）</label></th>
          <td><textarea name="ps_features_lines" id="ps_features_lines" class="large-text code" rows="5"><?php echo esc_textarea($meta['ps_features_lines']); ?></textarea></td>
        </tr>

        <tr><th colspan="2"><h3>应用场景（scenarios）</h3></th></tr>
        <tr>
          <th><label for="ps_scenarios_title">场景标题（A9）</label></th>
          <td><input type="text" name="ps_scenarios_title" id="ps_scenarios_title" class="regular-text" value="<?php echo esc_attr($meta['ps_scenarios_title']); ?>"></td>
        </tr>
        <tr>
          <th><label for="ps_scenarios_lines">场景条目（A10，一行一个）</label></th>
          <td><textarea name="ps_scenarios_lines" id="ps_scenarios_lines" class="large-text code" rows="5"><?php echo esc_textarea($meta['ps_scenarios_lines']); ?></textarea></td>
        </tr>

        <tr><th colspan="2"><h3>参数表（HTML 表格）</h3></th></tr>
        <tr>
          <th><label for="ps_table1">参数表 1（table1）</label></th>
          <td><textarea name="ps_table1" id="ps_table1" class="large-text code" rows="7" placeholder="<table>..."></textarea><?php echo esc_textarea($meta['ps_table1']); ?></td>
        </tr>
        <tr>
          <th><label for="ps_table2">参数表 2（table2，可选）</label></th>
          <td><textarea name="ps_table2" id="ps_table2" class="large-text code" rows="7" placeholder="<table>..."></textarea><?php echo esc_textarea($meta['ps_table2']); ?></td>
        </tr>

        <tr><th colspan="2"><h3>补充说明</h3></th></tr>
        <tr>
          <th><label for="ps_extra_text">补充 1（A12，一行一个）</label></th>
          <td><textarea name="ps_extra_text" id="ps_extra_text" class="large-text code" rows="4"><?php echo esc_textarea($meta['ps_extra_text']); ?></textarea></td>
        </tr>
        <tr>
          <th><label for="ps_extra2">补充 2（Unnamed:_17）</label></th>
          <td><textarea name="ps_extra2" id="ps_extra2" class="large-text code" rows="3"><?php echo esc_textarea($meta['ps_extra2']); ?></textarea></td>
        </tr>
        <tr>
          <th><label for="ps_extra3">补充 3（Unnamed:_18）</label></th>
          <td><textarea name="ps_extra3" id="ps_extra3" class="large-text code" rows="3"><?php echo esc_textarea($meta['ps_extra3']); ?></textarea></td>
        </tr>
      </table>
    </div>
    <?php
  }

  public function enqueue_admin($hook) {
    if (in_array($hook, ['post-new.php', 'post.php'])) {
      $screen = get_current_screen();
      if ($screen && $screen->post_type === self::CPT) {
        wp_enqueue_media();
        wp_enqueue_script('ps-admin-media', plugins_url('assets/admin-media.js', __FILE__), ['jquery'], '1.0', true);
      }
    }
  }

  public function save_meta($post_id, $post) {
    if (!isset($_POST['ps_meta_nonce']) || !wp_verify_nonce($_POST['ps_meta_nonce'], 'ps_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== self::CPT) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $fields = [
      'ps_sub','ps_img1','ps_img2','ps_img3','ps_img4',
      'ps_features_title','ps_features_lines',
      'ps_scenarios_title','ps_scenarios_lines',
      'ps_table1','ps_table2',
      'ps_extra_text','ps_extra2','ps_extra3'
    ];
    foreach ($fields as $key) {
      $val = isset($_POST[$key]) ? wp_kses_post($_POST[$key]) : '';
      update_post_meta($post_id, $key, $val);
    }
  }

  private function get_image_or_featured($post_id, $meta_key) {
    $url = get_post_meta($post_id, $meta_key, true);
    if ($url) return esc_url_raw($url);
    if ($meta_key === 'ps_img1') {
      $thumb = get_the_post_thumbnail_url($post_id, 'large');
      if ($thumb) return esc_url_raw($thumb);
    }
    return '';
  }

  public function register_rest() {
    register_rest_route('ps/v1', '/products', [
      'methods'  => 'GET',
      'callback' => [$this, 'rest_products'],
      'permission_callback' => '__return_true',
    ]);
  }

  public function rest_products($request) {
    $args = [
      'post_type' => self::CPT,
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
    ];
    $query = new WP_Query($args);
    $items = [];
    while ($query->have_posts()) {
      $query->the_post();
      $post_id = get_the_ID();
      // 分类（取第一个作“内容栏目”）
      $terms = get_the_terms($post_id, self::TAX);
      $category = '';
      if ($terms && !is_wp_error($terms)) {
        $category = $terms[0]->name;
      } else {
        $category = '未分类';
      }
      $item = [
        'id' => $post_id,
        'title' => get_the_title(),
        'sub' => get_post_meta($post_id, 'ps_sub', true),
        'desc' => wp_strip_all_tags(get_the_content('', false)),

        // 图片字段匹配前端键名
        'img1' => $this->get_image_or_featured($post_id, 'ps_img1'),
        'A5'   => $this->get_image_or_featured($post_id, 'ps_img2'),
        'A8'   => $this->get_image_or_featured($post_id, 'ps_img3'),
        'A11'  => $this->get_image_or_featured($post_id, 'ps_img4'),

        // 亮点/场景/补充与表格
        'A6' => get_post_meta($post_id, 'ps_features_title', true),
        'A7' => get_post_meta($post_id, 'ps_features_lines', true),
        'A9' => get_post_meta($post_id, 'ps_scenarios_title', true),
        'A10'=> get_post_meta($post_id, 'ps_scenarios_lines', true),
        'A12'=> get_post_meta($post_id, 'ps_extra_text', true),
        'Unnamed:_17' => get_post_meta($post_id, 'ps_extra2', true),
        'Unnamed:_18' => get_post_meta($post_id, 'ps_extra3', true),

        'table1' => get_post_meta($post_id, 'ps_table1', true),
        'table2' => get_post_meta($post_id, 'ps_table2', true),
      ];
      // 将分类写入特定键名：内容栏目
      $item['内容栏目'] = $category;
      $items[] = $item;
    }
    wp_reset_postdata();
    return rest_ensure_response($items);
  }

  public function shortcode_catalog($atts = []) {
    // 读取打包在插件中的前端片段，将占位符替换为 REST 端点
    $path = plugin_dir_path(__FILE__) . 'assets/product-site-fragment.html';
    if (!file_exists($path)) {
      return '<p>前端模板缺失。</p>';
    }
    $html = file_get_contents($path);
    $endpoint = esc_url_raw( rest_url('ps/v1/products') );
    $html = str_replace('__PS_PRODUCTS_ENDPOINT__', $endpoint, $html);
    // 简单包裹一层，避免与主题样式冲突（如有冲突可改为 iframe 方案）
    return $html;
  }

  public function on_activate() {
    // 注册再刷新
    $this->register_cpt_tax();
    flush_rewrite_rules();

    // 自动创建一个页面，插入短代码，便于立即查看
    $title = '产品目录';
    if (!get_page_by_title($title)) {
      wp_insert_post([
        'post_title' => $title,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '[product_catalog]'
      ]);
    }
  }
}

new PS_Product_Site_Plugin();

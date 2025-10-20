<?php
/**
 * Plugin Name: PS Product Site (Catalog + JSON + Shortcode)
 * Description: 基于上传的前端模板，提供“产品”CPT、后台字段、REST JSON 输出与短代码展示（与 Avada/任意主题兼容）。
 * Version: 1.1.0
 * Author: 超級の新人
 */
if (!defined('ABSPATH')) exit;
class PS_Product_Site_Plugin {
  const CPT='ps_product'; const TAX='ps_category';
  function __construct(){
    add_action('init',[$this,'register_cpt_tax']);
    add_action('add_meta_boxes',[$this,'register_metaboxes']);
    add_action('save_post',[$this,'save_meta'],10,2);
    add_action('admin_enqueue_scripts',[$this,'enqueue_admin']);
    add_action('rest_api_init',[$this,'register_rest']);
    add_shortcode('product_catalog',[$this,'shortcode_catalog']);
    register_activation_hook(__FILE__,[$this,'on_activate']);
  }
  function register_cpt_tax(){
    register_post_type(self::CPT,[ 'labels'=>['name'=>'产品','singular_name'=>'产品','menu_name'=>'产品','add_new'=>'新建产品','add_new_item'=>'新建产品','edit_item'=>'编辑产品','new_item'=>'新产品','view_item'=>'查看产品','search_items'=>'搜索产品','not_found'=>'未找到产品','not_found_in_trash'=>'回收站无产品'], 'public'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-products','supports'=>['title','editor','thumbnail','excerpt'],'has_archive'=>true,'rewrite'=>['slug'=>'products'],'show_in_rest'=>true ]);
    register_taxonomy(self::TAX,[self::CPT],[ 'labels'=>['name'=>'产品分类','singular_name'=>'产品分类','menu_name'=>'产品分类'], 'public'=>true,'hierarchical'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'product-category'] ]);
  }
  function register_metaboxes(){ add_meta_box('ps_product_info','产品信息（前端展示字段）',[$this,'render_metabox'],self::CPT,'normal','default'); }
  private function field($k,$d=''){ $v=get_post_meta(get_the_ID(),$k,true); return is_string($v)?$v:$d; }
  function render_metabox($post){
    wp_nonce_field('ps_save_meta','ps_meta_nonce');
    $m=['ps_sub'=>$this->field('ps_sub'),'ps_img1'=>$this->field('ps_img1'),'ps_img2'=>$this->field('ps_img2'),'ps_img3'=>$this->field('ps_img3'),'ps_img4'=>$this->field('ps_img4'),'ps_features_title'=>$this->field('ps_features_title'),'ps_features_lines'=>$this->field('ps_features_lines'),'ps_scenarios_title'=>$this->field('ps_scenarios_title'),'ps_scenarios_lines'=>$this->field('ps_scenarios_lines'),'ps_table1'=>$this->field('ps_table1'),'ps_table2'=>$this->field('ps_table2'),'ps_extra_text'=>$this->field('ps_extra_text'),'ps_extra2'=>$this->field('ps_extra2'),'ps_extra3'=>$this->field('ps_extra3')];
    echo '<div class="ps-metabox"><p><strong>提示：</strong>正文=desc，分类在右侧“产品分类”。</p><table class="form-table">';
    echo '<tr><th>型号（sub）</th><td><input type="text" name="ps_sub" class="regular-text" value="'.esc_attr($m['ps_sub']).'"></td></tr>';
    foreach(['ps_img1'=>'主图（img1）','ps_img2'=>'图库2（A5）','ps_img3'=>'图库3（A8）','ps_img4'=>'图库4（A11）'] as $k=>$lab){
      echo '<tr><th>'.$lab.'</th><td><input type="text" name="'.$k.'" class="regular-text" value="'.esc_attr($m[$k]).'"> <button class="button ps-upload-btn">选择图片</button></td></tr>';
    }
    echo '<tr><th colspan="2"><h3>亮点</h3></th></tr><tr><th>亮点标题（A6）</th><td><input type="text" name="ps_features_title" class="regular-text" value="'.esc_attr($m['ps_features_title']).'"></td></tr>';
    echo '<tr><th>亮点条目（A7，一行一条）</th><td><textarea name="ps_features_lines" class="large-text code" rows="5">'.esc_textarea($m['ps_features_lines']).'</textarea></td></tr>';
    echo '<tr><th colspan="2"><h3>应用场景</h3></th></tr><tr><th>场景标题（A9）</th><td><input type="text" name="ps_scenarios_title" class="regular-text" value="'.esc_attr($m['ps_scenarios_title']).'"></td></tr>';
    echo '<tr><th>场景条目（A10，一行一条）</th><td><textarea name="ps_scenarios_lines" class="large-text code" rows="5">'.esc_textarea($m['ps_scenarios_lines']).'</textarea></td></tr>';
    echo '<tr><th colspan="2"><h3>参数表（HTML）</h3></th></tr><tr><th>table1</th><td><textarea name="ps_table1" class="large-text code" rows="7">'.esc_textarea($m['ps_table1']).'</textarea></td></tr>';
    echo '<tr><th>table2</th><td><textarea name="ps_table2" class="large-text code" rows="7">'.esc_textarea($m['ps_table2']).'</textarea></td></tr>';
    echo '<tr><th colspan="2"><h3>补充说明</h3></th></tr>';
    echo '<tr><th>A12</th><td><textarea name="ps_extra_text" class="large-text code" rows="4">'.esc_textarea($m['ps_extra_text']).'</textarea></td></tr>';
    echo '<tr><th>Unnamed:_17</th><td><textarea name="ps_extra2" class="large-text code" rows="3">'.esc_textarea($m['ps_extra2']).'</textarea></td></tr>';
    echo '<tr><th>Unnamed:_18</th><td><textarea name="ps_extra3" class="large-text code" rows="3">'.esc_textarea($m['ps_extra3']).'</textarea></td></tr>';
    echo '</table></div>';
  }
  function enqueue_admin($hook){ if(in_array($hook,['post-new.php','post.php'])){ $s=get_current_screen(); if($s && $s->post_type===self::CPT){ wp_enqueue_media(); wp_enqueue_script('ps-admin-media',plugins_url('assets/admin-media.js',__FILE__),['jquery'],'1.0',true); } } }
  function save_meta($post_id,$post){ if(!isset($_POST['ps_meta_nonce'])||!wp_verify_nonce($_POST['ps_meta_nonce'],'ps_save_meta'))return; if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return; if($post->post_type!==self::CPT)return; if(!current_user_can('edit_post',$post_id))return; foreach(['ps_sub','ps_img1','ps_img2','ps_img3','ps_img4','ps_features_title','ps_features_lines','ps_scenarios_title','ps_scenarios_lines','ps_table1','ps_table2','ps_extra_text','ps_extra2','ps_extra3'] as $k){ $v=isset($_POST[$k])?wp_kses_post($_POST[$k]):''; update_post_meta($post_id,$k,$v);} }
  private function get_image_or_featured($id,$k){ $u=get_post_meta($id,$k,true); if($u) return esc_url_raw($u); if($k==='ps_img1'){ $t=get_the_post_thumbnail_url($id,'large'); if($t) return esc_url_raw($t);} return ''; }
  function register_rest(){ register_rest_route('ps/v1','/products',['methods'=>'GET','callback'=>[$this,'rest_products'],'permission_callback'=>'__return_true']); }
  function rest_products($req){ $q=new WP_Query(['post_type'=>self::CPT,'post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC']); $items=[]; while($q->have_posts()){ $q->the_post(); $id=get_the_ID(); $terms=get_the_terms($id,self::TAX); $cat=($terms&&!is_wp_error($terms))?$terms[0]->name:'未分类'; $items[]=[ 'id'=>$id,'title'=>get_the_title(),'sub'=>get_post_meta($id,'ps_sub',true),'desc'=>wp_strip_all_tags(get_the_content('',false)),'img1'=>$this->get_image_or_featured($id,'ps_img1'),'A5'=>$this->get_image_or_featured($id,'ps_img2'),'A8'=>$this->get_image_or_featured($id,'ps_img3'),'A11'=>$this->get_image_or_featured($id,'ps_img4'),'A6'=>get_post_meta($id,'ps_features_title',true),'A7'=>get_post_meta($id,'ps_features_lines',true),'A9'=>get_post_meta($id,'ps_scenarios_title',true),'A10'=>get_post_meta($id,'ps_scenarios_lines',true),'A12'=>get_post_meta($id,'ps_extra_text',true),'Unnamed:_17'=>get_post_meta($id,'ps_extra2',true),'Unnamed:_18'=>get_post_meta($id,'ps_extra3',true),'table1'=>get_post_meta($id,'ps_table1',true),'table2'=>get_post_meta($id,'ps_table2',true),'内容栏目'=>$cat ]; } wp_reset_postdata(); return rest_ensure_response($items); }
  function shortcode_catalog($atts=[]){ $atts=shortcode_atts(['fullwidth'=>'0','maxwidth'=>'1280'],$atts); $path=plugin_dir_path(__FILE__).'assets/product-site-fragment.html'; if(!file_exists($path)) return '<p>前端模板缺失。</p>'; $html=file_get_contents($path); $endpoint=esc_url_raw(rest_url('ps/v1/products')); $html=str_replace('__PS_PRODUCTS_ENDPOINT__',$endpoint,$html); if($atts['fullwidth']==='1'){ $max=intval($atts['maxwidth']); if($max<=0) $max=1280; $css='<style id="ps-product-site-fullwidth">.ps-edge-wide{width:100vw;margin-left:50%;transform:translateX(-50%);}#ps-product-site{max-width:'.$max.'px;margin:0 auto;padding:0 16px;}@media(max-width:1024px){#ps-product-site .wrapper.page{display:block !important;}}</style>'; return $css.'<div class="ps-edge-wide">'.$html.'</div>'; } return $html; }
  function on_activate(){ $this->register_cpt_tax(); flush_rewrite_rules(); if(!get_page_by_title('产品目录')){ wp_insert_post(['post_title'=>'产品目录','post_status'=>'publish','post_type'=>'page','post_content'=>'[product_catalog]']); } }
}
new PS_Product_Site_Plugin();

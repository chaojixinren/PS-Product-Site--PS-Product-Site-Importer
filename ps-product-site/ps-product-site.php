<?php
/**
 * Plugin Name: PS Product Site (Catalog + JSON + Shortcode)
 * Description: v1.5.0：修复404问题，自动搜索包含product_catalog短代码的页面；
 * Version: 1.5.0
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
    add_action('template_redirect',[$this,'maybe_render_iframe'],1);
    add_action('wp_enqueue_scripts',[$this,'enqueue_client_redirect'],999);
    register_activation_hook(__FILE__,[$this,'on_activate']);
  }
  
  function enqueue_client_redirect(){
    if(isset($_GET['product']) && is_404()){
      // 获取不带product参数的当前URL路径
      $current_uri = $_SERVER['REQUEST_URI'];
      $path = parse_url($current_uri, PHP_URL_PATH);
      
      // 构建正确的URL
      $clean_url = home_url($path);
      
      // 输出重定向脚本
      wp_add_inline_script('jquery', '
        (function(){
          if(location.search.indexOf("product=") !== -1){
            var cleanPath = "'.esc_js($path).'";
            if(cleanPath && cleanPath !== location.pathname){
              // 如果路径不匹配，尝试跳转到页面目录首页
              var possibleUrls = ["'.esc_js($path).'", "'.esc_js(home_url('/')).'"];
              for(var i = 0; i < possibleUrls.length; i++){
                if(i === 0 || true){
                  location.href = possibleUrls[i] + location.search;
                  return;
                }
              }
            }
          }
        })();
      ');
    }
  }

  function register_cpt_tax(){
    register_post_type(self::CPT,[
      'labels'=>['name'=>'产品','singular_name'=>'产品','menu_name'=>'产品','add_new'=>'新建产品','add_new_item'=>'新建产品','edit_item'=>'编辑产品','new_item'=>'新产品','view_item'=>'查看产品','search_items'=>'搜索产品','not_found'=>'未找到产品','not_found_in_trash'=>'回收站无产品'],
      'public'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-products',
      'supports'=>['title','editor','thumbnail','excerpt'],
      'has_archive'=>true,'rewrite'=>['slug'=>'products'],'show_in_rest'=>true
    ]);
    register_taxonomy(self::TAX,[self::CPT],[
      'labels'=>['name'=>'产品分类','singular_name'=>'产品分类','menu_name'=>'产品分类'],
      'public'=>true,'hierarchical'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'product-category']
    ]);
  }

  function register_metaboxes(){ add_meta_box('ps_product_info','产品信息（前端展示字段）',[$this,'render_metabox'],self::CPT,'normal','default'); }
  private function field($k,$d=''){ $v=get_post_meta(get_the_ID(),$k,true); return is_string($v)?$v:$d; }

  function render_metabox($post){
    wp_nonce_field('ps_save_meta','ps_meta_nonce');
    $m=[
      'ps_sub'=>$this->field('ps_sub'),
      'ps_img1'=>$this->field('ps_img1'),'ps_img2'=>$this->field('ps_img2'),'ps_img3'=>$this->field('ps_img3'),'ps_img4'=>$this->field('ps_img4'),
      'ps_features_title'=>$this->field('ps_features_title'),'ps_features_lines'=>$this->field('ps_features_lines'),
      'ps_scenarios_title'=>$this->field('ps_scenarios_title'),'ps_scenarios_lines'=>$this->field('ps_scenarios_lines'),
      'ps_table1'=>$this->field('ps_table1'),'ps_table2'=>$this->field('ps_table2'),
      'ps_extra_text'=>$this->field('ps_extra_text'),'ps_extra2'=>$this->field('ps_extra2'),'ps_extra3'=>$this->field('ps_extra3')
    ];
    echo '<div class="ps-metabox"><p>提示：文章正文=desc，主图留空将回退到特色图像；table1/table2 可直接粘贴 &lt;table&gt; HTML。</p><table class="form-table">';
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
    echo '<tr><th colspan="2"><h3>补充说明</h3></th></tr><tr><th>A12</th><td><textarea name="ps_extra_text" class="large-text code" rows="4">'.esc_textarea($m['ps_extra_text']).'</textarea></td></tr>';
    echo '<tr><th>Unnamed:_17</th><td><textarea name="ps_extra2" class="large-text code" rows="3">'.esc_textarea($m['ps_extra2']).'</textarea></td></tr>';
    echo '<tr><th>Unnamed:_18</th><td><textarea name="ps_extra3" class="large-text code" rows="3">'.esc_textarea($m['ps_extra3']).'</textarea></td></tr>';
    echo '</table></div>';
  }

  function enqueue_admin($hook){ if(in_array($hook,['post-new.php','post.php'])){ $s=get_current_screen(); if($s && $s->post_type===self::CPT){ wp_enqueue_media(); wp_enqueue_script('ps-admin-media',plugins_url('assets/admin-media.js',__FILE__),['jquery'],'1.0',true); } } }

  function save_meta($post_id,$post){
    if(!isset($_POST['ps_meta_nonce'])||!wp_verify_nonce($_POST['ps_meta_nonce'],'ps_save_meta'))return;
    if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;
    if($post->post_type!==self::CPT)return;
    if(!current_user_can('edit_post',$post_id))return;
    foreach(['ps_sub','ps_img1','ps_img2','ps_img3','ps_img4','ps_features_title','ps_features_lines','ps_scenarios_title','ps_scenarios_lines','ps_table1','ps_table2','ps_extra_text','ps_extra2','ps_extra3'] as $k){
      $v=isset($_POST[$k])?wp_kses_post($_POST[$k]):''; update_post_meta($post_id,$k,$v);
    }
  }

  private function get_image_or_featured($id,$k){
    $u=get_post_meta($id,$k,true); if($u) return esc_url_raw($u);
    if($k==='ps_img1'){ $t=get_the_post_thumbnail_url($id,'large'); if($t) return esc_url_raw($t); }
    return '';
  }

  function register_rest(){
    register_rest_route('ps/v1','/products',[ 'methods'=>'GET','callback'=>[$this,'rest_products'],'permission_callback'=>'__return_true' ]);
  }

  function rest_products($req){
    $q=new WP_Query(['post_type'=>self::CPT,'post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC']);
    $items=[]; while($q->have_posts()){ $q->the_post(); $id=get_the_ID();
      $terms=get_the_terms($id,self::TAX); $cat=($terms&&!is_wp_error($terms))?$terms[0]->name:'未分类';
      $items[]=[ 'id'=>$id,'title'=>get_the_title(),'sub'=>get_post_meta($id,'ps_sub',true),'desc'=>wp_strip_all_tags(get_the_content('',false)),'img1'=>$this->get_image_or_featured($id,'ps_img1'),'A5'=>$this->get_image_or_featured($id,'ps_img2'),'A8'=>$this->get_image_or_featured($id,'ps_img3'),'A11'=>$this->get_image_or_featured($id,'ps_img4'),'A6'=>get_post_meta($id,'ps_features_title',true),'A7'=>get_post_meta($id,'ps_features_lines',true),'A9'=>get_post_meta($id,'ps_scenarios_title',true),'A10'=>get_post_meta($id,'ps_scenarios_lines',true),'A12'=>get_post_meta($id,'ps_extra_text',true),'Unnamed:_17'=>get_post_meta($id,'ps_extra2',true),'Unnamed:_18'=>get_post_meta($id,'ps_extra3',true),'table1'=>get_post_meta($id,'ps_table1',true),'table2'=>get_post_meta($id,'ps_table2',true),'内容栏目'=>$cat ];
    } wp_reset_postdata(); return rest_ensure_response($items);
  }

  private function build_endpoints(){
    $primary  = rest_url('ps/v1/products');
    $fallback = add_query_arg('rest_route', '/ps/v1/products', home_url('/'));
    return ['primary'=>$primary, 'fallback'=>$fallback];
  }

  private function inject_fetch_fallback_js($eps){
    $p = esc_js($eps['primary']);
    $f = esc_js($eps['fallback']);
    return '<script>(function(){var EP_PRIMARY=\"'.$p.'\";var EP_FALLBACK=\"'.$f.'\";var _fetch=window.fetch;if(_fetch){window.fetch=function(i,init){function urlOf(x){if(typeof x===\"string\") return x; if(x&&x.url) return x.url; return \"\";}var u=urlOf(i); if(u.indexOf(EP_PRIMARY)===0){return _fetch(i,init).then(function(r){if(!r.ok){return _fetch(EP_FALLBACK,init);}return r;}).catch(function(){return _fetch(EP_FALLBACK,init);});} return _fetch(i,init);};}})();</script>';
  }

  function shortcode_catalog($atts=[]){
    $atts=shortcode_atts(['mode'=>'iframe','fullwidth'=>'0','maxwidth'=>'1280','minheight'=>'600'], $atts);
    
    // 添加客户端重定向处理
    $o = '';
    if(isset($_GET['product'])){
      $o .= '<script>
        (function(){
          // 如果当前URL包含product参数但被判定为404，尝试重定向
          var urlParams = new URLSearchParams(location.search);
          if(urlParams.has("product") && document.querySelector(".ps-pill") === null){
            // 等待一小段时间，然后尝试在不带参数的URL上加载
            setTimeout(function(){
              var cleanUrl = location.protocol + "//" + location.host + location.pathname;
              if(cleanUrl !== location.href){
                location.href = cleanUrl + "?product=" + encodeURIComponent(urlParams.get("product"));
              }
            }, 100);
          }
        })();
      </script>';
    }
    
    if($atts['mode']==='iframe'){
      $id = 'ps_iframe_'.wp_rand(1000,9999);
      // 将当前页面的URL参数传递给iframe
      $src = home_url('/');
      if(isset($_GET['product'])){
        $src = add_query_arg('product', sanitize_text_field($_GET['product']), $src);
      }
      $src = add_query_arg('ps_catalog_iframe',$id, $src);
      $wrap_start=''; $wrap_end='';
      if($atts['fullwidth']==='1'){
        $max = intval($atts['maxwidth']); if($max<=0) $max=1280;
        $wrap_start = '<style id="ps-catalog-fw">.ps-edge-wide{width:100vw;margin-left:50%;transform:translateX(-50%);} .ps-edge-wide .ps-iframe{max-width:' . $max . 'px;margin:0 auto;display:block;}</style><div class="ps-edge-wide">';
        $wrap_end = '</div>';
      }
      $style = 'width:100%;border:0;display:block;min-height:'.intval($atts['minheight']).'px;';
      $o  .= $wrap_start;
      $o .= '<iframe class="ps-iframe" id="'.$id.'" src="'.esc_url($src).'" style="'.$style.'" loading="lazy"></iframe>';
      $o .= '<script>(function(){var id="'.$id.'";function onMsg(e){try{if(e.data&&e.data.type==="ps-resize"&&e.data.id===id){var f=document.getElementById("'.$id.'");if(f){var h=parseInt(e.data.h,10)||0;if(h>0&&h<200000){f.style.height=h+"px";}}}}catch(err){}}window.addEventListener("message",onMsg,false);})();</script>';
      $o .= $wrap_end;
      return $o;
    }
    $path=plugin_dir_path(__FILE__).'assets/product-site-fragment.html';
    if(!file_exists($path)) return '<p>前端模板缺失。</p>';
    $html=file_get_contents($path);
    $eps=$this->build_endpoints();
    $html=str_replace('__PS_PRODUCTS_ENDPOINT__',$eps['primary'],$html);
    $html=$this->inject_fetch_fallback_js($eps).$html;
    if($atts['fullwidth']==='1'){
      $max=intval($atts['maxwidth']); if($max<=0) $max=1280;
      $css='<style id="ps-product-site-fullwidth">.ps-edge-wide{width:100vw;margin-left:50%;transform:translateX(-50%);}#ps-product-site{max-width:'.$max.'px;margin:0 auto;padding:0 16px;}@media(max-width:1024px){#ps-product-site .wrapper.page{display:block !important;}}</style>';
      return $css.'<div class="ps-edge-wide">'.$html.'</div>';
    }
    return $html;
  }

  function handle_product_param_404(){
    // 如果有product参数但返回404，说明WordPress不识别带参数的URL
    if(isset($_GET['product']) && is_404()){
      global $wp_query, $wp_the_query;
      // 获取当前请求的URI路径（移除查询参数）
      $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      
      // 从路径中提取可能的页面slug
      // 例如：/index.php/slk-prodcucts-sma-cloned-cloned/ 或 /slk-prodcucts-sma-cloned-cloned/
      $parts = explode('/', trim($request_uri, '/'));
      
      // 尝试不同的路径组合
      $possible_slugs = [];
      for($i = count($parts) - 1; $i >= 0; $i--){
        if(!empty($parts[$i])){
          $possible_slugs[] = implode('/', array_slice($parts, $i));
        }
      }
      
      // 查找匹配的页面
      foreach($possible_slugs as $slug){
        $page = get_page_by_path($slug);
        if($page && $page->post_status === 'publish'){
          // 找到了对应页面，修改查询对象
          $wp_query->is_404 = false;
          $wp_query->is_page = true;
          $wp_query->is_singular = true;
          $wp_query->queried_object = $page;
          $wp_query->queried_object_id = $page->ID;
          $wp_query->posts = [$page];
          $wp_query->post_count = 1;
          $wp_query->max_num_pages = 1;
          $wp_query->found_posts = 1;
          
          global $post;
          $post = $page;
          setup_postdata($post);
          
          // 也更新主查询对象
          $wp_the_query = $wp_query;
          
          status_header(200);
          return;
        }
      }
    }
  }

  function prevent_404_for_product_param($template){
    if(isset($_GET['product']) && is_404()){
      global $wp_query;
      
      // 获取所有页面并查找包含 [product_catalog] 短代码的页面
      $query = new WP_Query([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1
      ]);
      
      $target_page = null;
      
      if($query->have_posts()){
        // 遍历所有页面，查找包含 [product_catalog] 短代码的页面
        while($query->have_posts()){
          $query->the_post();
          $post = get_post();
          $content = $post->post_content;
          
          // 检查是否包含 product_catalog 短代码
          if(strpos($content, '[product_catalog') !== false){
            $target_page = $post;
            break;
          }
        }
        $query->reset_postdata();
        
        // 如果找到了目标页面
        if($target_page){
          $wp_query->is_404 = false;
          $wp_query->is_page = true;
          $wp_query->is_singular = true;
          $wp_query->queried_object = $target_page;
          $wp_query->queried_object_id = $target_page->ID;
          $wp_query->posts = [$target_page];
          $wp_query->post_count = 1;
          $wp_query->max_num_pages = 1;
          $wp_query->found_posts = 1;
          
          global $post;
          $post = $target_page;
          setup_postdata($post);
          
          status_header(200);
          
          // 返回合适的模板
          $page_template = get_page_template();
          if($page_template && file_exists($page_template)){
            return $page_template;
          }
          $singular_template = get_singular_template();
          if($singular_template && file_exists($singular_template)){
            return $singular_template;
          }
          $index_template = get_query_template('index');
          return $index_template ?: $template;
        }
      }
    }
    return $template;
  }

  function maybe_render_iframe(){
    // 处理iframe请求
    if(isset($_GET['ps_catalog_iframe'])){
      $id = sanitize_text_field($_GET['ps_catalog_iframe']);
      status_header(200); nocache_headers(); header('Content-Type: text/html; charset=utf-8');
      $path=plugin_dir_path(__FILE__).'assets/product-site-fragment.html';
      if(!file_exists($path)){ echo '<!doctype html><meta charset="utf-8"><p>模板缺失</p>'; exit; }
      $html=file_get_contents($path);
      $eps=$this->build_endpoints();
      $html=str_replace('__PS_PRODUCTS_ENDPOINT__',$eps['primary'],$html);
      echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
      echo '<style>html,body{margin:0;padding:0;height:auto !important;overflow:hidden;}#ps-product-site{padding:0;margin:0}</style>';
      echo '</head><body>';
      echo $this->inject_fetch_fallback_js($eps);
      echo $html;
      echo '<script>(function(){var id="'.esc_js($id).'";var root=document.getElementById("ps-product-site")||document.body;var last=0;function h(){try{var r=root.getBoundingClientRect();var nh=Math.ceil(r.height)+2;if(Math.abs(nh-last)>1){last=nh;parent.postMessage({type:"ps-resize",id:id,h:nh},"*");}}catch(e){}}window.addEventListener("load",h);var ro=new ResizeObserver(function(){h()});ro.observe(root);var mo=new MutationObserver(function(){setTimeout(h,50)});mo.observe(root,{subtree:true,childList:true,attributes:true});setTimeout(h,300);})();</script>';
      echo '</body></html>'; exit;
    }
  }

  function on_activate(){
    $this->register_cpt_tax(); flush_rewrite_rules();
    if(!get_page_by_title('产品目录')){
      wp_insert_post(['post_title'=>'产品目录','post_status'=>'publish','post_type'=>'page','post_content'=>'[product_catalog fullwidth="1" maxwidth="1280"]']);
    }
  }
}
new PS_Product_Site_Plugin();

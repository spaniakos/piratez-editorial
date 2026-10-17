<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Products {
 const TYPE='piratez_product'; const TAX='piratez_product_category';
 public static function boot(){
  add_action('init',[self::class,'register']);
  add_action('update_option_'.Settings::OPTION,function($old,$new){if(!empty($old['catalogue'])!==!empty($new['catalogue']))update_option('pz_editorial_flush_routes',1,false);},10,2);
  add_action('wp_loaded',function(){if(get_option('pz_editorial_flush_routes')){flush_rewrite_rules(false);delete_option('pz_editorial_flush_routes');}});
  add_action('add_meta_boxes',function(){if(post_type_exists(self::TYPE))add_meta_box('pz-product','Product information',[self::class,'box'],self::TYPE);});
  add_action('save_post_'.self::TYPE,[self::class,'save']);
 }
 public static function sanitize($data){
  $data=is_array($data)?$data:[];$out=[];foreach(['model','badge','cta_label']as$key)$out[$key]=sanitize_text_field($data[$key]??'');
  foreach(['cta_url','datasheet']as$key)$out[$key]=esc_url_raw($data[$key]??'',['http','https','mailto']);
  $out['specs']=[];foreach(array_slice((array)($data['specs']??[]),0,50)as$row){if(!is_array($row))continue;$label=sanitize_text_field($row['label']??'');if($label!=='')$out['specs'][]=['label'=>$label,'value'=>sanitize_text_field($row['value']??'')];}
  return $out;
 }
 public static function register(){
  if(Settings::get()['catalogue']){
   if(!post_type_exists(self::TYPE))register_post_type(self::TYPE,['labels'=>['name'=>'Products','singular_name'=>'Product','add_new_item'=>'Add showcase product'],'public'=>true,'show_in_rest'=>true,'show_in_menu'=>'piratez-manager','has_archive'=>true,'rewrite'=>['slug'=>'piratez-products','with_front'=>false],'supports'=>['title','editor','excerpt','thumbnail','revisions','custom-fields'],'map_meta_cap'=>true]);
   if(!taxonomy_exists(self::TAX))register_taxonomy(self::TAX,self::TYPE,['label'=>'Product categories','public'=>true,'hierarchical'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'piratez-product-category']]);
   register_post_meta(self::TYPE,'_pz_product',['single'=>true,'type'=>'object','default'=>self::sanitize([]),'revisions_enabled'=>true,'show_in_rest'=>['schema'=>['type'=>'object','properties'=>['model'=>['type'=>'string'],'badge'=>['type'=>'string'],'cta_label'=>['type'=>'string'],'cta_url'=>['type'=>'string'],'datasheet'=>['type'=>'string'],'specs'=>['type'=>'array','items'=>['type'=>'object','properties'=>['label'=>['type'=>'string'],'value'=>['type'=>'string']],'additionalProperties'=>false]]],'additionalProperties'=>false]],'sanitize_callback'=>[self::class,'sanitize'],'auth_callback'=>fn($allowed,$key,$id)=>current_user_can('edit_post',$id)]);
  }
  if(\WP_Block_Type_Registry::get_instance()->is_registered('piratez-editorial/product-card'))return;
  wp_register_script('pz-editorial-product-blocks',plugins_url('assets/product-blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components','wp-data','wp-core-data','wp-server-side-render'],PIRATEZ_EDITORIAL_VERSION,true);
  wp_add_inline_script('pz-editorial-product-blocks','window.pzEditorialCatalogue='.wp_json_encode((bool)Settings::get()['catalogue']).';','before');
  $attrs=['id'=>['type'=>'number','default'=>0],'title'=>['type'=>'string','default'=>''],'summary'=>['type'=>'string','default'=>''],'image'=>['type'=>'number','default'=>0],'url'=>['type'=>'string','default'=>''],'badge'=>['type'=>'string','default'=>''],'ids'=>['type'=>'array','items'=>['type'=>'number'],'default'=>[]],'rows'=>['type'=>'array','items'=>['type'=>'object'],'default'=>[]],'category'=>['type'=>'number','default'=>0],'count'=>['type'=>'number','default'=>9]];
  foreach(['product-card','specifications','products','product-comparison','product-details']as$name)register_block_type('piratez-editorial/'.$name,['api_version'=>3,'editor_script'=>'pz-editorial-product-blocks','attributes'=>$attrs,'uses_context'=>['postId'],'render_callback'=>function($a,$content,$block)use($name){return self::render($name,$a,$block->context['postId']??get_the_ID());}]);
 }
 public static function public_product($id){$p=get_post($id);return $p&&$p->post_type===self::TYPE&&$p->post_status==='publish'&&$p->post_password===''&&Settings::get()['catalogue']?$p:null;}
 public static function card($id){
  $p=self::public_product($id);if(!$p)return '';$data=self::sanitize(get_post_meta($id,'_pz_product',true));
  return '<article class="pz-product-card">'.get_the_post_thumbnail($id,'medium').($data['badge']?'<span class="pz-product-badge">'.esc_html($data['badge']).'</span>':'').'<h3><a href="'.esc_url(get_permalink($id)).'">'.esc_html($p->post_title).'</a></h3><p>'.esc_html($p->post_excerpt).'</p></article>';
 }
 public static function render($name,$a,$context=0){
  $id=absint($a['id']??0);
  if($name==='product-details'){
   $p=self::public_product($id?:$context);if(!$p)return '';$data=self::sanitize(get_post_meta($p->ID,'_pz_product',true));$out='<section '.get_block_wrapper_attributes(['class'=>'pz-product-details']).'>';
   if($data['model'])$out.='<p>Model: '.esc_html($data['model']).'</p>';
   $out.=self::render('specifications',['id'=>$p->ID],$p->ID);
   foreach(['cta_url'=>$data['cta_label']?:'Contact / request quote','datasheet'=>'Download datasheet']as$key=>$label)if($data[$key])$out.='<p><a class="pz-product-cta" href="'.esc_url($data[$key]).'">'.esc_html($label).'</a></p>';
   return $out.'</section>';
  }
  if($name==='product-card'){
   if($id)return self::card($id);
   $image=absint($a['image']??0);$parent=$image?get_post(wp_get_post_parent_id($image)):null;if($parent&&(!is_post_publicly_viewable($parent)||post_password_required($parent)))$image=0;
   $title=esc_html($a['title']??'');$url=esc_url($a['url']??'',['https','http']);return '<article '.get_block_wrapper_attributes(['class'=>'pz-product-card']).'>'.($image?wp_get_attachment_image($image,'medium'):'').'<span class="pz-product-badge">'.esc_html($a['badge']??'').'</span><h3>'.($url?'<a href="'.$url.'">'.$title.'</a>':$title).'</h3><p>'.esc_html($a['summary']??'').'</p></article>';
  }
  if($name==='specifications'){
   $rows=$a['rows']??[];if(!$rows){$p=self::public_product($id?:$context);if(!$p)return '';$data=self::sanitize(get_post_meta($p->ID,'_pz_product',true));$rows=$data['specs'];}
   if(!$rows)return '';$out='<table '.get_block_wrapper_attributes(['class'=>'pz-specifications']).'><caption>Specifications</caption><tbody>';foreach(array_slice($rows,0,50)as$row)if(is_array($row))$out.='<tr><th scope="row">'.esc_html($row['label']??'').'</th><td>'.esc_html($row['value']??'').'</td></tr>';return $out.'</tbody></table>';
  }
  if($name==='product-comparison'){
   $products=[];foreach(array_slice(array_unique(array_map('absint',$a['ids']??[])),0,6)as$pid)if($p=self::public_product($pid))$products[]=$p;if(!$products)return '';
   $labels=[];$data=[];foreach($products as$p){$fields=self::sanitize(get_post_meta($p->ID,'_pz_product',true));$data[$p->ID]=array_column($fields['specs'],'value','label');$labels=array_unique(array_merge($labels,array_keys($data[$p->ID])));}
   $out='<div class="pz-table-scroll"><table '.get_block_wrapper_attributes(['class'=>'pz-product-comparison']).'><caption>Product comparison</caption><thead><tr><th scope="col">Specification</th>';foreach($products as$p)$out.='<th scope="col"><a href="'.esc_url(get_permalink($p)).'">'.esc_html($p->post_title).'</a></th>';$out.='</tr></thead><tbody>';
   foreach(array_slice($labels,0,100)as$label){$values=array_map(fn($p)=>$data[$p->ID][$label]??'—',$products);$different=count(array_unique($values))>1;$out.='<tr'.($different?' class="pz-different"':'').'><th scope="row">'.esc_html($label).'</th>';foreach($values as$v)$out.='<td>'.esc_html($v).'</td>';$out.='</tr>';}
   return $out.'</tbody></table></div>';
  }
  if($name==='products'){
   if(!Settings::get()['catalogue'])return '';$category=absint($_GET['pz_category']??$a['category']??0);$page=max(1,min(1000,absint($_GET['pz_page']??1)));$count=max(1,min(24,absint($a['count']??9)));
   $args=['post_type'=>self::TYPE,'post_status'=>'publish','has_password'=>false,'posts_per_page'=>$count,'paged'=>$page,'orderby'=>['title'=>'ASC','ID'=>'ASC']];if($category)$args['tax_query']=[['taxonomy'=>self::TAX,'field'=>'term_id','terms'=>$category]];
   $query=new \WP_Query($args);$out='<nav class="pz-product-filters" aria-label="Product categories"><a href="'.esc_url(remove_query_arg(['pz_category','pz_page'])).'">All</a> ';foreach(get_terms(['taxonomy'=>self::TAX,'hide_empty'=>true])as$term)$out.='<a href="'.esc_url(add_query_arg(['pz_category'=>$term->term_id,'pz_page'=>false])).'">'.esc_html($term->name).'</a> ';$out.='</nav><div '.get_block_wrapper_attributes(['class'=>'pz-products-grid']).'>';foreach($query->posts as$p)$out.=self::card($p->ID);$out.='</div>';$out.=paginate_links(['base'=>esc_url_raw(add_query_arg('pz_page','%#%')),'current'=>$page,'total'=>$query->max_num_pages]);return $out;
  }
  return '';
 }
 public static function box($post){
  wp_nonce_field('pz_product','pz_product_nonce');$data=self::sanitize(get_post_meta($post->ID,'_pz_product',true));
  foreach(['model'=>'Model','badge'=>'Badge (truthful label)','cta_label'=>'Contact / quote button label','cta_url'=>'Contact / quote URL','datasheet'=>'Datasheet URL']as$key=>$label)echo '<p><label>'.esc_html($label).' <input class="widefat" name="pz_product['.esc_attr($key).']" value="'.esc_attr($data[$key]).'"></label></p>';
  echo '<p>Specifications: fill label/value pairs. Blank labels are ignored.</p>';for($i=0;$i<max(8,count($data['specs'])+3);$i++)echo '<p><input aria-label="Specification '.($i+1).' label" name="pz_product[specs]['.$i.'][label]" value="'.esc_attr($data['specs'][$i]['label']??'').'"> <input aria-label="Specification '.($i+1).' value" name="pz_product[specs]['.$i.'][value]" value="'.esc_attr($data['specs'][$i]['value']??'').'"></p>';
  echo '<p>Use the product-detail pattern for gallery, features, specifications and contact links. Purchasing features are not enabled.</p>';
 }
 public static function save($id){if(!current_user_can('edit_post',$id)||wp_is_post_revision($id)||wp_is_post_autosave($id)||!wp_verify_nonce($_POST['pz_product_nonce']??'','pz_product'))return;update_post_meta($id,'_pz_product',self::sanitize(wp_unslash($_POST['pz_product']??[])));}
}
Products::boot();

<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Maintenance {
 const QUEUE='pz_editorial_link_queue';
 public static function boot(){
  add_action('init',function(){register_taxonomy('pz_editorial_series',['post'],['label'=>'Article series','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'article-series']]);
   foreach(['post','page']as$type){register_post_meta($type,'_pz_editorial_notes',['type'=>'string','single'=>true,'show_in_rest'=>false,'sanitize_callback'=>'sanitize_textarea_field']);register_post_meta($type,'_pz_expiry_notice',['type'=>'string','single'=>true,'show_in_rest'=>false]);register_post_meta($type,'_pz_series_order',['type'=>'integer','single'=>true,'show_in_rest'=>false]);}
  });
  add_action('add_meta_boxes',function(){foreach(['post','page']as$type)add_meta_box('pz-maintenance','Editorial maintenance',[self::class,'box'],$type);});
  add_action('save_post',[self::class,'save'],20,2);
  add_action('pz_editorial_admin_sections',[self::class,'dashboard']);
  add_action('admin_post_pz_editorial_links',[self::class,'enqueue']);
  add_action('pz_editorial_check_links',[self::class,'run']);
  add_action('init',function(){wp_register_script('pz-editorial-navigation-blocks',plugins_url('assets/navigation-blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components','wp-server-side-render'],PIRATEZ_EDITORIAL_VERSION,true);foreach(['series','page-navigation']as$name)register_block_type('piratez-editorial/'.$name,['api_version'=>3,'editor_script'=>'pz-editorial-navigation-blocks','attributes'=>['mode'=>['type'=>'string','default'=>'children']],'uses_context'=>['postId'],'render_callback'=>fn($a,$c,$b)=>self::navigation($name,$a,$b->context['postId']??get_the_ID())]);});
  add_filter('the_content',function($html){if(is_admin()||!is_singular()||!is_main_query()||!in_the_loop()||post_password_required())return$html;$date=get_post_meta(get_the_ID(),'_pz_review_date',true);$notice=get_post_meta(get_the_ID(),'_pz_expiry_notice',true);if($date&&$date<current_time('Y-m-d')&&$notice)$html='<aside class="pz-update">'.esc_html($notice).'</aside>'.$html;return$html;},31);
 }
 public static function box($post){
  wp_nonce_field('pz_maintenance','pz_maintenance_nonce');
  echo '<p><label>Private editorial notes<textarea class="widefat" rows="4" name="pz_notes">'.esc_textarea(get_post_meta($post->ID,'_pz_editorial_notes',true)).'</textarea></label></p><p><label>Public notice after review date (optional)<textarea class="widefat" name="pz_expiry">'.esc_textarea(get_post_meta($post->ID,'_pz_expiry_notice',true)).'</textarea></label></p><p><label>Series position <input type="number" min="0" name="pz_series_order" value="'.absint(get_post_meta($post->ID,'_pz_series_order',true)).'"></label></p>';
  $results=get_post_meta($post->ID,'_pz_link_results',true);if(is_array($results)){echo '<h3>Last link check</h3><ul>';foreach($results['links']??[]as$row)echo '<li>'.esc_html($row['url'].' — '.$row['status']).'</li>';echo '</ul><p>HTTP responses are advisory; bot restrictions can cause false failures.</p>';}
  // A separate POST form cannot be nested in the post editor form. The dashboard owns queue actions.
 }
 public static function save($id,$post){
  if(!in_array($post->post_type,['post','page'],true)||!current_user_can('edit_post',$id)||wp_is_post_revision($id)||wp_is_post_autosave($id)||!wp_verify_nonce($_POST['pz_maintenance_nonce']??'','pz_maintenance'))return;
  update_post_meta($id,'_pz_editorial_notes',sanitize_textarea_field(wp_unslash($_POST['pz_notes']??'')));update_post_meta($id,'_pz_expiry_notice',sanitize_textarea_field(wp_unslash($_POST['pz_expiry']??'')));update_post_meta($id,'_pz_series_order',absint($_POST['pz_series_order']??0));
 }
 public static function dashboard(){
  $filter=sanitize_key($_GET['pz_view']??'recent');$args=['post_type'=>['post','page'],'post_status'=>['publish','draft','pending','future'],'posts_per_page'=>20,'paged'=>max(1,absint($_GET['pz_content_page']??1))];
  if($filter==='due')$args['meta_query']=[['key'=>'_pz_review_date','value'=>['0001-01-01',current_time('Y-m-d')],'compare'=>'BETWEEN','type'=>'DATE']];
  if($filter==='old')$args['date_query']=[['column'=>'post_modified','before'=>wp_date('Y-m-d',strtotime('-1 year'))]];
  $q=new \WP_Query($args);echo '<hr><h2>Content maintenance</h2><p><a href="'.esc_url(add_query_arg('pz_view','recent')).'">Recent content</a> · <a href="'.esc_url(add_query_arg('pz_view','due')).'">Review due</a> · <a href="'.esc_url(add_query_arg('pz_view','old')).'">Not updated for a year</a></p><table class="widefat"><thead><tr><th>Content</th><th>Review date</th><th>Advisory checks</th><th>Links</th></tr></thead><tbody>';
  foreach($q->posts as$p){if(!current_user_can('edit_post',$p->ID))continue;echo '<tr><td><a href="'.esc_url(get_edit_post_link($p->ID)).'">'.esc_html($p->post_title?:'(Untitled)').'</a></td><td>'.esc_html(get_post_meta($p->ID,'_pz_review_date',true)).'</td><td>'.esc_html(implode(' ',Admin::checks($p))).'</td><td><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="pz_editorial_links"><input type="hidden" name="id" value="'.(int)$p->ID.'">';wp_nonce_field('pz_links_'.$p->ID);submit_button('Queue link check','secondary','submit',false);echo '</form></td></tr>';}
  echo '</tbody></table>'.paginate_links(['base'=>esc_url_raw(add_query_arg('pz_content_page','%#%')),'current'=>$args['paged'],'total'=>$q->max_num_pages]).'<p>Queue: '.count((array)get_option(self::QUEUE,[])).' URLs. Checks run in small WP-Cron batches; no frontend crawl.</p>';
 }
 public static function allowed_url($url){
  $parts=wp_parse_url($url);if(!$parts||!in_array(strtolower($parts['scheme']??''),['https','http'],true)||isset($parts['user'])||isset($parts['pass'])||isset($parts['port'])&&!in_array($parts['port'],[80,443],true))return false;
  $host=strtolower(rtrim($parts['host']??'','.'));if(!$host||$host==='localhost'||str_ends_with($host,'.local')||str_ends_with($host,'.localhost'))return false;
  if(filter_var($host,FILTER_VALIDATE_IP)&&!filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return false;
  $addresses=gethostbynamel($host);if(!$addresses)return false;foreach($addresses as$ip)if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return false;
  return (bool)wp_http_validate_url($url);
 }
 public static function enqueue(){
  $id=absint($_POST['id']??0);if(!current_user_can('manage_options')||!current_user_can('edit_post',$id))wp_die('Forbidden',403);check_admin_referer('pz_links_'.$id);
  self::queue($id);wp_safe_redirect(admin_url('admin.php?page=piratez-editorial&queued=1'));exit;
 }
 public static function queue($id){
  $p=get_post($id);if(!$p)return;$processor=new \WP_HTML_Tag_Processor($p->post_content);$links=[];
  while($processor->next_tag('A')){$url=$processor->get_attribute('href');if(!is_string($url)||$url===''||$url[0]==='#')continue;if(str_starts_with($url,'/'))$url=home_url($url);if(!preg_match('~^https?://~i',$url))continue;$url=preg_replace('/#.*$/','',$url);$links[$url]=['post'=>$id,'url'=>esc_url_raw($url)];if(count($links)>=50)break;}
  $queue=(array)get_option(self::QUEUE,[]);foreach($links as$link){$key=md5($id.':'.$link['url']);$queue[$key]=$link;}update_option(self::QUEUE,array_slice($queue,0,200,true),false);update_post_meta($id,'_pz_link_results',['time'=>time(),'links'=>[]]);if(!wp_next_scheduled('pz_editorial_check_links'))wp_schedule_single_event(time()+5,'pz_editorial_check_links');
 }
 public static function check($url,$hops=0){
  if($hops>3||!self::allowed_url($url))return 'Skipped: unsafe or unresolved destination';
  $cached=get_transient('pz_link_'.md5($url));if($cached!==false)return $cached;
  $r=wp_safe_remote_head($url,['timeout'=>5,'redirection'=>0,'limit_response_size'=>1024]);
  if(is_wp_error($r))return 'Uncertain: '.$r->get_error_code();$code=wp_remote_retrieve_response_code($r);
  if($code>=300&&$code<400){$target=wp_remote_retrieve_header($r,'location');if(!$target)return 'HTTP '.$code.' (no location)';$target=\WP_Http::make_absolute_url($target,$url);return 'HTTP '.$code.' → '.self::check($target,$hops+1);}
  $status='HTTP '.$code.(in_array($code,[403,405,429],true)?' (requires manual review)':'');set_transient('pz_link_'.md5($url),$status,HOUR_IN_SECONDS);return $status;
 }
 public static function run(){
  if(!add_option('pz_editorial_links_lock',time(),'',false)){if((int)get_option('pz_editorial_links_lock')<time()-120)delete_option('pz_editorial_links_lock');if(!wp_next_scheduled('pz_editorial_check_links'))wp_schedule_single_event(time()+60,'pz_editorial_check_links');return;}
  try{$queue=(array)get_option(self::QUEUE,[]);$batch=array_slice($queue,0,3,true);foreach($batch as$key=>$row){$result=self::check($row['url']);$history=get_post_meta($row['post'],'_pz_link_results',true);$history=is_array($history)?$history:['time'=>time(),'links'=>[]];$history['links'][]=['url'=>$row['url'],'status'=>$result];$history['links']=array_slice($history['links'],-50);update_post_meta($row['post'],'_pz_link_results',$history);unset($queue[$key]);}update_option(self::QUEUE,$queue,false);if($queue&&!wp_next_scheduled('pz_editorial_check_links'))wp_schedule_single_event(time()+60,'pz_editorial_check_links');}finally{delete_option('pz_editorial_links_lock');}
 }
 public static function navigation($name,$a,$id){
  if(!$id||post_password_required($id))return '';$out='<nav aria-label="'.($name==='series'?'Article series':'Page navigation').'" class="pz-content-navigation"><ul>';$items=[];
  if($name==='series'){$terms=wp_get_post_terms($id,'pz_editorial_series',['fields'=>'ids']);if(is_wp_error($terms)||!$terms)return '';$items=get_posts(['post_type'=>'post','post_status'=>'publish','has_password'=>false,'posts_per_page'=>200,'tax_query'=>[['taxonomy'=>'pz_editorial_series','field'=>'term_id','terms'=>$terms]]]);usort($items,fn($x,$y)=>[absint(get_post_meta($x->ID,'_pz_series_order',true)),$x->ID]<=>[absint(get_post_meta($y->ID,'_pz_series_order',true)),$y->ID]);}
  else{$p=get_post($id);if(!$p)return '';$parent=($a['mode']??'children')==='siblings'?$p->post_parent:$id;$items=get_posts(['post_type'=>'page','post_status'=>'publish','has_password'=>false,'post_parent'=>$parent,'posts_per_page'=>100,'orderby'=>['menu_order'=>'ASC','title'=>'ASC']]);}
  if(!$items)return '';foreach($items as$p)$out.='<li><a '.($p->ID===$id?'aria-current="page" ':'').'href="'.esc_url(get_permalink($p)).'">'.esc_html($p->post_title).'</a></li>';$out.='</ul>';
  if($name==='series'){$ids=wp_list_pluck($items,'ID');$index=array_search((int)$id,$ids,true);if($index!==false)foreach(['Previous'=>$index-1,'Next'=>$index+1]as$label=>$position)if(isset($items[$position]))$out.='<a href="'.esc_url(get_permalink($items[$position])).'">'.esc_html($label.': '.$items[$position]->post_title).'</a> ';}
  return $out.'</nav>';
 }
}
Maintenance::boot();

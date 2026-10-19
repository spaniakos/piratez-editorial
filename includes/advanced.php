<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Advanced {
 public static function boot(){
  add_action('init',[self::class,'register']);
  add_action('admin_menu',function(){if(Settings::get()['workflow'])add_submenu_page('edit.php','Editorial reviews','Editorial reviews','edit_posts','pz-editorial-review',[self::class,'review_dashboard']);});
  add_filter('render_block',function($html){if(!is_admin()&&str_contains($html,'pz-term'))wp_enqueue_script('pz-editorial-advanced');return $html;},20);
  add_action('add_meta_boxes',function(){if(!Settings::get()['workflow'])return;foreach(['post','page']as$type)add_meta_box('pz-review','Review workflow',[self::class,'review_box'],$type,'side');});
  add_action('save_post',[self::class,'save'],30,2);
  add_action('admin_post_pz_review',[self::class,'review_action']);
  add_action('enqueue_block_editor_assets',function(){if(!Settings::get()['modules']['advanced'])return;wp_enqueue_script('pz-editorial-glossary-format',plugins_url('assets/glossary-format.js',PIRATEZ_EDITORIAL_FILE),['wp-rich-text','wp-block-editor','wp-element','wp-components'],PIRATEZ_EDITORIAL_VERSION,true);});
  add_action('wp_enqueue_scripts',function(){if(!is_singular()||post_password_required())return;if(Settings::enabled('preferences')||Settings::enabled('print'))wp_enqueue_script('pz-editorial-advanced',plugins_url('assets/advanced.js',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION,['in_footer'=>true,'strategy'=>'defer']);});
 }
 public static function register(){
  wp_register_script('pz-editorial-advanced-blocks',plugins_url('assets/advanced-blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components'],PIRATEZ_EDITORIAL_VERSION,true);
  wp_register_script('pz-editorial-advanced',plugins_url('assets/advanced.js',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION,['in_footer'=>true,'strategy'=>'defer']);
  register_block_type('piratez-editorial/itinerary',['api_version'=>3,'editor_script'=>'pz-editorial-advanced-blocks','render_callback'=>function($a,$content,$block){$nights=0;foreach($block->parsed_block['innerBlocks']as$day)if($day['blockName']==='piratez-editorial/itinerary-day')$nights+=max(0,min(365,absint($day['attrs']['nights']??0)));return '<p class="pz-itinerary-total">'.esc_html(sprintf(_n('%d overnight stay','%d overnight stays',$nights,'piratez-editorial'),$nights)).'</p>'.$content;}]);
  register_block_type('piratez-editorial/itinerary-day',['api_version'=>3,'editor_script'=>'pz-editorial-advanced-blocks']);
  register_block_type('piratez-editorial/glossary',['api_version'=>3,'editor_script'=>'pz-editorial-advanced-blocks']);
  register_block_type('piratez-editorial/map',['api_version'=>3,'editor_script'=>'pz-editorial-advanced-blocks','view_script'=>'pz-editorial-advanced','attributes'=>['latitude'=>['type'=>'number','default'=>37.9838],'longitude'=>['type'=>'number','default'=>23.7275],'provider'=>['type'=>'string','default'=>'osm'],'label'=>['type'=>'string','default'=>'Location map']],'render_callback'=>[self::class,'map']]);
 }
 public static function map($a){
  $lat=max(-85,min(85,(float)($a['latitude']??0)));$lon=max(-180,min(180,(float)($a['longitude']??0)));$provider=($a['provider']??'osm')==='google'?'Google Maps':'OpenStreetMap';
  if($provider==='Google Maps'){$src='https://maps.google.com/maps?'.http_build_query(['q'=>$lat.','.$lon,'output'=>'embed']);$link='https://www.google.com/maps/search/?'.http_build_query(['api'=>1,'query'=>$lat.','.$lon]);}
  else{$src='https://www.openstreetmap.org/export/embed.html?'.http_build_query(['bbox'=>($lon-.02).','.($lat-.02).','.($lon+.02).','.($lat+.02),'layer'=>'mapnik','marker'=>$lat.','.$lon]);$link='https://www.openstreetmap.org/?'.http_build_query(['mlat'=>$lat,'mlon'=>$lon]).'#map=13/'.$lat.'/'.$lon;}
  return '<section '.get_block_wrapper_attributes(['class'=>'pz-map','data-map-src'=>$src,'data-map-label'=>sanitize_text_field($a['label']??'Location map')]).'><h3>'.esc_html($a['label']??'Location map').'</h3><p>Loading this map connects your browser to '.esc_html($provider).', which receives your IP address and may use cookies. Your choice applies to this map on this page.</p><p><a href="'.esc_url($link).'" target="_blank" rel="noopener noreferrer">Open location on '.esc_html($provider).'</a></p><button type="button" class="pz-load-map">Load map</button><div class="pz-map-frame"></div></section>';
 }
 public static function review_box($post){
  wp_nonce_field('pz_review_meta','pz_review_meta_nonce');$reviewer=absint(get_post_meta($post->ID,'_pz_reviewer',true));$state=get_post_meta($post->ID,'_pz_review_state',true)?:'Not requested';
  echo '<p>Status: '.esc_html($state).'</p><p><label>Reviewer <select name="pz_reviewer"><option value="0">Unassigned</option>';
  foreach(get_users(['capability'=>'edit_others_posts','number'=>100,'orderby'=>'display_name'])as$user)if(user_can($user,'edit_post',$post->ID))echo '<option value="'.(int)$user->ID.'" '.selected($reviewer,$user->ID,false).'>'.esc_html($user->display_name).'</option>';
  echo '</select></label></p>';foreach((array)get_post_meta($post->ID,'_pz_review_history',true)as$entry){if(!is_array($entry))continue;$actor=get_userdata(absint($entry['actor']??0));echo '<p>'.esc_html(wp_date('Y-m-d H:i',(int)($entry['time']??0)).' · '.($actor?$actor->display_name:'Unknown user').' · '.($entry['state']??'')).'</p>';}echo '<p>Save the post to assign a reviewer. Review actions are available in PirateZ → Editorial. Approval records review; normal WordPress publishing permissions still apply.</p>';
 }
 public static function save($id,$post){
  if(!Settings::get()['workflow']||!in_array($post->post_type,['post','page'],true)||!current_user_can('edit_post',$id)||wp_is_post_revision($id)||wp_is_post_autosave($id))return;
  if(wp_verify_nonce($_POST['pz_review_meta_nonce']??'','pz_review_meta')){$reviewer=absint($_POST['pz_reviewer']??0);if(!$reviewer||(user_can($reviewer,'publish_posts')&&user_can($reviewer,'edit_others_posts')&&user_can($reviewer,'edit_post',$id)))update_post_meta($id,'_pz_reviewer',$reviewer);}
  $approved=get_post_meta($id,'_pz_review_hash',true);if($approved&&!hash_equals($approved,self::review_hash($post))){update_post_meta($id,'_pz_review_state','Changes since approval');delete_post_meta($id,'_pz_review_hash');}
 }
 public static function review_hash($post){return hash('sha256',$post->post_title."\n".$post->post_content."\n".$post->post_excerpt);}
 public static function review($id,$operation,$expected_hash=null){
  $p=get_post($id);if(!Settings::get()['workflow']||!$p||!in_array($p->post_type,['post','page'],true)||!current_user_can('edit_post',$id))return new \WP_Error('forbidden','Review action not permitted.');
  if($expected_hash!==null&&!hash_equals(self::review_hash($p),(string)$expected_hash))return new \WP_Error('stale','Content changed. Reopen the article before recording a review.');
  if(!in_array($operation,['request','approve','changes'],true))return new \WP_Error('invalid','Invalid review action.');
  $reviewer=absint(get_post_meta($id,'_pz_reviewer',true));if($operation!=='request'&&(!current_user_can('edit_others_posts')||!current_user_can('publish_posts')||($reviewer!==get_current_user_id()&&!current_user_can('manage_options'))))return new \WP_Error('forbidden','Assigned reviewer permission required.');
  $state=['request'=>'Review requested','approve'=>'Approved','changes'=>'Changes requested'][$operation];update_post_meta($id,'_pz_review_state',$state);
  if($operation==='approve')update_post_meta($id,'_pz_review_hash',self::review_hash($p));else delete_post_meta($id,'_pz_review_hash');
  // Review never changes publication status or sends messages.
  $history=get_post_meta($id,'_pz_review_history',true);$history=is_array($history)?$history:[];$history[]=['actor'=>get_current_user_id(),'time'=>time(),'state'=>$state];update_post_meta($id,'_pz_review_history',array_slice($history,-20));return true;
 }
 public static function review_dashboard(){
  if(!current_user_can('edit_posts')||!Settings::get()['workflow'])return;
  $args=['post_type'=>['post','page'],'post_status'=>['publish','draft','pending','future'],'posts_per_page'=>20,'paged'=>max(1,absint($_GET['paged']??1))];if(!current_user_can('edit_others_posts'))$args['author']=get_current_user_id();$q=new \WP_Query($args);
  echo '<div class="wrap"><h1>Editorial reviews</h1><p>Review actions record editorial decisions. Publication stays under native WordPress controls. No messages are sent.</p><table class="widefat"><thead><tr><th>Content</th><th>Review state</th><th>Actions</th></tr></thead><tbody>';
  foreach($q->posts as$p){if(!current_user_can('edit_post',$p->ID))continue;echo '<tr><td><a href="'.esc_url(get_edit_post_link($p->ID)).'">'.esc_html($p->post_title).'</a></td><td>'.esc_html(get_post_meta($p->ID,'_pz_review_state',true)?:'Not requested').'</td><td>';
   foreach(['request'=>'Request review','approve'=>'Approve','changes'=>'Request changes']as$operation=>$label){if($operation!=='request'&&(!current_user_can('edit_others_posts')||!current_user_can('publish_posts')||(absint(get_post_meta($p->ID,'_pz_reviewer',true))!==get_current_user_id()&&!current_user_can('manage_options'))))continue;echo '<form style="display:inline-block;margin:.2rem" method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="pz_review"><input type="hidden" name="id" value="'.(int)$p->ID.'"><input type="hidden" name="operation" value="'.esc_attr($operation).'"><input type="hidden" name="hash" value="'.esc_attr(self::review_hash($p)).'">';wp_nonce_field('pz_review_'.$p->ID);submit_button($label,'secondary','submit',false);echo '</form>';}
   echo '</td></tr>';
  }echo '</tbody></table>'.paginate_links(['base'=>esc_url_raw(add_query_arg('paged','%#%')),'current'=>$args['paged'],'total'=>$q->max_num_pages]).'</div>';
 }
 public static function review_action(){
  $id=absint($_POST['id']??0);check_admin_referer('pz_review_'.$id);$result=self::review($id,sanitize_key($_POST['operation']??''),sanitize_text_field(wp_unslash($_POST['hash']??'')));if(is_wp_error($result))wp_die(esc_html($result->get_error_message()),'',403);wp_safe_redirect(admin_url('edit.php?page=pz-editorial-review'));exit;
 }
}
Advanced::boot();

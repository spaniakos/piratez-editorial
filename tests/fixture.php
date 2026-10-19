<?php
require __DIR__.'/wp-load.php';require_once ABSPATH.'wp-admin/includes/plugin.php';
header('Content-Type: application/json');
function pz_assert($value,$message){if(!$value)throw new Exception($message);}
use PirateZ\Editorial\Settings;
use PirateZ\Editorial\Content;
try {
wp_set_current_user(1);
$action=$_GET['action']??'test';
if($action==='switch') { switch_theme(sanitize_key($_GET['theme'])); echo '{}';exit; }
if($action==='content'){wp_update_post(wp_slash(['ID'=>get_option('pz_fixture_id'),'post_content'=>wp_unslash($_POST['content']??'')]));echo '{}';exit;}
if($action==='manager'){activate_plugin('piratez-manager/piratez-manager.php');$items=piratez_manager_catalogue();pz_assert(isset($items['piratez-editorial'])&&$items['piratez-editorial']['installed'],'Manager catalogue identity');echo '{}';exit;}
if($action==='legacy'){pz_assert(function_exists('piratez_note_shortcode'),'legacy theme owns shortcodes');pz_assert(!has_filter('the_content',[Content::class,'decorate']),'new engines passive');pz_assert(str_contains(do_shortcode('[piratez_note]Old theme remains working[/piratez_note]'),'piratez-note'),'old theme shortcodes');pz_assert(has_filter('update_plugins_github.com'),'updater active in migration mode');echo '{}';exit;}
if($action==='plugin-state'){if(($_GET['value']??'')==='off')deactivate_plugins('piratez-editorial/piratez-editorial.php');else activate_plugin('piratez-editorial/piratez-editorial.php');echo '{}';exit;}
if($action==='patterns'){echo wp_json_encode(array_values(array_filter(WP_Block_Patterns_Registry::get_instance()->get_all_registered(),fn($p)=>str_starts_with($p['name'],'piratez-editorial/'))));exit;}
if($action==='portable'){
 $settings=Settings::get();update_option('pz_fixture_settings',$settings);
 foreach(['post','page']as$type)$settings[$type]=array_fill_keys(Settings::FEATURES,false);$settings['modules']=array_fill_keys(array_keys($settings['modules']),false);update_option(Settings::OPTION,$settings);
 $block=wp_insert_post(['post_type'=>'wp_block','post_status'=>'publish','post_title'=>'Synced Editorial','post_content'=>'<!-- wp:piratez-editorial/download {"url":"https://example.test/demo.pdf","label":"Synced download"} /--><!-- wp:paragraph --><p>A <span class="pz-term" data-definition="Synced definition">synced term</span>.</p><!-- /wp:paragraph -->']);
 $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Portable synced pattern','post_content'=>'<!-- wp:block {"ref":'.(int)$block.'} /-->']);echo wp_json_encode(['id'=>$id]);exit;
}
if($action==='restore-settings'){update_option(Settings::OPTION,get_option('pz_fixture_settings'));echo '{}';exit;}
if($action==='reader-state'){$id=get_option('pz_fixture_id');$v=($_GET['value']??'inherit')==='off'?'off':'inherit';update_post_meta($id,'_pz_toc',$v);update_post_meta($id,'_pz_reading_progress',$v);echo '{}';exit;}
if($action==='advanced'){
 $settings=Settings::get();$settings['workflow']=true;$settings['post']['preferences']=true;$settings['post']['print']=true;update_option(Settings::OPTION,$settings);
 $id=get_option('pz_fixture_id');$original=get_post_status($id);pz_assert(is_wp_error(\PirateZ\Editorial\Advanced::review($id,'approve','stale-hash')),'stale approval refused');pz_assert(\PirateZ\Editorial\Advanced::review($id,'request')===true,'request review');pz_assert(get_post_status($id)===$original,'review does not unpublish');pz_assert(\PirateZ\Editorial\Advanced::review($id,'approve')===true,'approve review');wp_update_post(['ID'=>$id,'post_title'=>'Changed after approval']);pz_assert(get_post_meta($id,'_pz_review_state',true)==='Changes since approval','approval invalidated by edit');
 $user=wp_insert_user(['user_login'=>'review-author','user_pass'=>'author-password','role'=>'author']);wp_set_current_user($user);pz_assert(is_wp_error(\PirateZ\Editorial\Advanced::review($id,'approve')),'unauthorized approval denied');wp_set_current_user(1);
 $map=\PirateZ\Editorial\Advanced::map(['latitude'=>37.9,'longitude'=>23.7,'label'=>'Athens','provider'=>'osm']);pz_assert(!str_contains($map,'<iframe'),'no initial iframe');pz_assert(str_contains($map,'data-map-src'),'map URL deferred');
 echo '{}';exit;
}
if($action==='page-layouts'){
 $settings=Settings::get();$settings['page']['reading_time']=true;$settings['page']['sharing']=true;$settings['page']['reading_progress']=true;update_option(Settings::OPTION,$settings);
 $pages=[];foreach(['default','narrow','wide','full-width','landing']as$variant){$id=wp_insert_post(['post_title'=>'Layout '.$variant,'post_type'=>'page','post_status'=>'publish','post_content'=>'<!-- wp:group {"align":"full"} --><div class="wp-block-group alignfull"><!-- wp:paragraph --><p>Full-width content.</p><!-- /wp:paragraph --></div><!-- /wp:group -->']);if($variant!=='default')update_post_meta($id,'_wp_page_template','page-templates/'.$variant.'.php');if($variant==='landing')foreach(['title','header','footer']as$part)update_post_meta($id,'_pz_hide_'.$part,'on');$pages[$variant]=$id;}
 echo wp_json_encode($pages);exit;
}
if($action==='maintenance'){
 $id=get_option('pz_fixture_id');update_post_meta($id,'_pz_editorial_notes','PRIVATE EDITORIAL NOTE');update_post_meta($id,'_pz_review_date','2020-01-01');update_post_meta($id,'_pz_expiry_notice','Review this guide before following it.');
 $term=wp_insert_term('Example series','pz_editorial_series');$term_id=is_wp_error($term)?$term->get_error_data('term_exists'):$term['term_id'];wp_set_post_terms($id,[$term_id],'pz_editorial_series');
 $next=wp_insert_post(['post_title'=>'Next article','post_status'=>'publish']);wp_set_post_terms($next,[$term_id],'pz_editorial_series');update_post_meta($next,'_pz_series_order',2);
 pz_assert(str_contains(\PirateZ\Editorial\Maintenance::navigation('series',[],$id),'Next article'),'series navigation');
 pz_assert(!\PirateZ\Editorial\Maintenance::allowed_url('http://127.0.0.1/private'),'private target refused');pz_assert(!\PirateZ\Editorial\Maintenance::allowed_url('http://localhost/private'),'localhost refused');
 $page=wp_insert_post(['post_title'=>'Parent','post_type'=>'page','post_status'=>'publish']);wp_insert_post(['post_title'=>'Visible child','post_type'=>'page','post_status'=>'publish','post_parent'=>$page]);wp_insert_post(['post_title'=>'Private child','post_type'=>'page','post_status'=>'private','post_parent'=>$page]);$nav=\PirateZ\Editorial\Maintenance::navigation('page-navigation',[],$page);pz_assert(str_contains($nav,'Visible child')&&!str_contains($nav,'Private child'),'public child navigation');
 $request=new WP_REST_Request('GET','/wp/v2/posts/'.$id);wp_set_current_user(0);$response=rest_do_request($request);pz_assert(!str_contains(wp_json_encode($response->get_data()),'PRIVATE EDITORIAL NOTE'),'private notes excluded from REST');wp_set_current_user(1);
 echo '{}';exit;
}
if($action==='products'){
 pz_assert(!post_type_exists('piratez_product'),'catalogue off by default');$settings=Settings::get();$settings['catalogue']=true;update_option(Settings::OPTION,$settings);\PirateZ\Editorial\Products::register();
 $ids=[];foreach(['Public A'=>'publish','Public B'=>'publish','Secret'=>'private']as$title=>$status){$product=wp_insert_post(['post_type'=>'piratez_product','post_title'=>$title,'post_status'=>$status]);update_post_meta($product,'_pz_product',['model'=>'Demo','specs'=>[['label'=>'Depth','value'=>$title==='Public A'?'40 cm':'60 cm']],'cta_url'=>'https://example.test/contact','cta_label'=>'Request a quote']);$ids[]=$product;}
 $comparison=\PirateZ\Editorial\Products::render('product-comparison',['ids'=>$ids]);pz_assert(str_contains($comparison,'40 cm')&&str_contains($comparison,'60 cm')&&!str_contains($comparison,'Secret'),'public comparison only');pz_assert(str_contains($comparison,'pz-different'),'differences highlighted');
 $details=\PirateZ\Editorial\Products::render('product-details',['id'=>$ids[0]]);pz_assert(str_contains($details,'Request a quote'),'product CTA');
 $collection=\PirateZ\Editorial\Products::render('products',[]);pz_assert(str_contains($collection,'Public A')&&!str_contains($collection,'Secret'),'public collection');
 echo wp_json_encode(['ids'=>$ids]);exit;
}
if($action==='test'){
 set_theme_mod('piratez_reading_time',false);set_theme_mod('piratez_social_github','https://github.com/spaniakos');
 delete_option('pz_editorial_imported');$p=Settings::import_preview();pz_assert($p['changes']['post.reading_time']===false,'false import');pz_assert(Settings::import($p['target_hash'])===true,'import');pz_assert(Settings::enabled('reading_time')===false,'off preserved');pz_assert(is_wp_error(Settings::import($p['target_hash'])),'idempotent import');
 $s=Settings::get();$s['post']['reading_time']=true;update_option(Settings::OPTION,$s);
 $id=wp_insert_post(['post_title'=>'Ελληνικό άρθρο','post_status'=>'publish','post_content'=>'<!-- wp:heading --><h2 class="wp-block-heading">Πρώτη ενότητα</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Δοκιμή για ταξίδι.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Δεύτερη ενότητα</h2><!-- /wp:heading --><!-- wp:shortcode -->[piratez_note title="Insight"]<p>Safe Ελληνικά</p>[/piratez_note]<!-- /wp:shortcode -->']);
 update_option('pz_fixture_id',$id);wp_set_post_terms($id,[1],'category');
 $related=wp_insert_post(['post_title'=>'Related','post_status'=>'publish']);wp_set_post_terms($related,[1],'category');
 pz_assert(Content::minutes(str_repeat('Ελληνική λέξη ',200))===2,'Unicode words');
 pz_assert(str_contains(do_shortcode('[piratez_note title="Hello"]<p>Nested [piratez_warning]Warning[/piratez_warning]</p>[/piratez_note]'),'piratez-warning'),'nested shortcodes');
 pz_assert(!str_contains(do_shortcode('[piratez_note]<script>alert(1)</script>safe[/piratez_note]'),'<script>'),'shortcode sanitization');
 pz_assert(str_contains(do_shortcode('[piratez_social_links class="custom"]'),'https://github.com/spaniakos'),'profiles');
 pz_assert(in_array($related,wp_list_pluck(Content::related($id)->posts,'ID'),true),'related selection');wp_update_post(['ID'=>$related,'post_status'=>'draft']);pz_assert(!in_array($related,wp_list_pluck(Content::related($id)->posts,'ID'),true),'related invalidation');
 update_post_meta($id,'_pz_sharing','off');pz_assert(Content::component('sharing',$id)==='','per-post override');
 pz_assert(has_filter('update_plugins_github.com'),'native updater');
 echo wp_json_encode(['id'=>$id,'wp'=>$GLOBALS['wp_version'],'url'=>get_permalink($id)]);exit;
}
echo wp_json_encode(['id'=>get_option('pz_fixture_id'),'theme'=>get_template(),'active'=>has_shortcode(get_post_field('post_content',get_option('pz_fixture_id')),'piratez_note')]);
}catch(Throwable $e){http_response_code(500);echo wp_json_encode(['error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);}

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

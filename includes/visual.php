<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Visual {
 public static function register() {
  wp_register_script('pz-editorial-visual-blocks',plugins_url('assets/visual-blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components'],PIRATEZ_EDITORIAL_VERSION,true);
  wp_register_script('pz-editorial-visual',plugins_url('assets/visual.js',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION,['in_footer'=>true,'strategy'=>'defer']);
  foreach(['slideshow','before-after']as$name)register_block_type('piratez-editorial/'.$name,['api_version'=>3,'editor_script'=>'pz-editorial-visual-blocks','view_script'=>'pz-editorial-visual']);
  register_block_type('piratez-editorial/download',['api_version'=>3,'editor_script'=>'pz-editorial-visual-blocks','attributes'=>['id'=>['type'=>'number','default'=>0],'url'=>['type'=>'string','default'=>''],'label'=>['type'=>'string','default'=>'Download'],'description'=>['type'=>'string','default'=>'']],'render_callback'=>[self::class,'download']]);
 }
 public static function download($a) {
  $url=esc_url($a['url']??'', ['https','http']);$detail='';$id=absint($a['id']??0);
  if($id){$file=get_post($id);if(!$file||$file->post_type!=='attachment')return '';$parent=$file->post_parent?get_post($file->post_parent):null;if(($parent&&(!is_post_publicly_viewable($parent)||post_password_required($parent)))||($file->post_status!=='inherit'&&!is_post_publicly_viewable($file)))return '';$url=wp_get_attachment_url($id);$path=get_attached_file($id);$size=$path&&is_file($path)?filesize($path):false;$detail=strtoupper(pathinfo($url,PATHINFO_EXTENSION)).($size?' · '.size_format($size):'');}
  if(!$url)return '';return '<div '.get_block_wrapper_attributes(['class'=>'pz-download']).'><h3><a href="'.esc_url($url).'">'.esc_html($a['label']??'Download').'</a></h3><p>'.esc_html($a['description']??'').'</p><small>'.esc_html($detail).'</small></div>';
 }
}

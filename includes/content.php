<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Content {
    public static function boot() {
        require_once __DIR__.'/legacy-shortcodes.php';
        add_action('wp_enqueue_scripts',[self::class,'assets']);
        add_action('enqueue_block_assets',function(){if(is_admin())wp_enqueue_style('pz-editorial',plugins_url('assets/content.css',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION);});
        add_filter('the_content',[self::class,'decorate'],30);
        foreach (['save_post','deleted_post','set_object_terms'] as $hook) { add_action($hook,[self::class,'invalidate'],10,1); }
        add_action('init',function(){add_shortcode('piratez_toc',function(){return self::component('toc');});add_shortcode('piratez_related',function(){return self::component('related');});});
    }
    public static function assets() {
        $id=get_queried_object_id(); if (!is_singular()||post_password_required($id)) return;
        $text=get_post_field('post_content',$id);
        $needed=str_contains($text,'piratez_')||str_contains($text,'piratez-editorial/')||array_filter(Settings::FEATURES,fn($f)=>Settings::enabled($f,$id));
        if (!$needed) return;
        wp_enqueue_style('pz-editorial',plugins_url('assets/content.css',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION);
        wp_enqueue_script('pz-editorial-reader',plugins_url('assets/reader.js',PIRATEZ_EDITORIAL_FILE),[],PIRATEZ_EDITORIAL_VERSION,['in_footer'=>true,'strategy'=>'defer']);
        wp_localize_script('pz-editorial-reader','pzEditorial',['toc'=>Settings::enabled('toc',$id),'code'=>Settings::enabled('code',$id)]);
    }
    public static function minutes($content) {
        $content=strip_shortcodes($content); $content=preg_replace('~<br\s*/?>|</(?:p|div|li|h[1-6]|blockquote|pre|section|article|figcaption|tr)>~i',' ',$content);
        $text=wp_check_invalid_utf8(html_entity_decode(wp_strip_all_tags($content),ENT_QUOTES|ENT_HTML5,'UTF-8'),true);
        preg_match_all("/[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/u",$text,$words);
        return (int)ceil(count($words[0])/200);
    }
    public static function invalidate($id) { $post=get_post($id); if($post&&$post->post_type==='post'&&!wp_is_post_revision($id))update_option('pz_editorial_related_revision',(int)get_option('pz_editorial_related_revision',1)+1,false); }
    public static function related($id,$limit=3) {
        $cats=wp_get_post_categories($id);if(!$cats||is_wp_error($cats))return false;
        $limit=max(1,min(12,absint($limit)));$key='pz_related_'.md5($id.':'.$limit.':'.get_option('pz_editorial_related_revision',1).':'.get_locale());$ids=get_transient($key);
        if($ids===false){$ids=get_posts(['category__in'=>$cats,'post__not_in'=>[$id],'post_type'=>'post','post_status'=>'publish','has_password'=>false,'posts_per_page'=>$limit,'orderby'=>['date'=>'DESC','ID'=>'DESC'],'ignore_sticky_posts'=>true,'fields'=>'ids','suppress_filters'=>false]);set_transient($key,$ids,HOUR_IN_SECONDS);}
        return $ids?new \WP_Query(['post_type'=>'post','post_status'=>'publish','has_password'=>false,'post__in'=>$ids,'orderby'=>'post__in','posts_per_page'=>count($ids),'no_found_rows'=>true]):false;
    }
    public static function component($feature,$id=0) {
        $id=$id?:get_the_ID();if(!$id||post_password_required($id)||!Settings::enabled($feature,$id))return '';
        if($feature==='reading_time'){ $n=self::minutes(get_post_field('post_content',$id));return $n?'<span class="reading-time">'.esc_html($n.' '.__('min read','piratez-editorial')).'</span>':''; }
        if($feature==='toc')return '<div id="toc-root" class="pz-toc-root"></div>';
        if($feature==='reading_progress')return '<div class="reading-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="'.esc_attr__('Reading progress','piratez-editorial').'"></div>';
        if($feature==='sharing'){
            $url=get_permalink($id);$title=get_post_field('post_title',$id);$out='<nav class="social-sharing pz-share" aria-label="'.esc_attr__('Share this article','piratez-editorial').'">';
            foreach(['Twitter'=>'https://twitter.com/intent/tweet?'.http_build_query(['url'=>$url,'text'=>$title]),'Facebook'=>'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u'=>$url]),'LinkedIn'=>'https://www.linkedin.com/shareArticle?'.http_build_query(['mini'=>'true','url'=>$url,'title'=>$title])]as$label=>$link)$out.='<a target="_blank" rel="noopener noreferrer" href="'.esc_url($link).'">'.esc_html($label).'</a> ';
            return $out.'<button type="button" class="share-copy" data-url="'.esc_url($url).'">'.esc_html__('Copy link','piratez-editorial').'</button><span class="pz-copy-status" role="status"></span></nav>';
        }
        if($feature==='related'){$query=self::related($id);if(!$query)return '';$out='<section class="related-posts"><h2>'.esc_html__('Related Posts','piratez-editorial').'</h2><div class="related-posts-grid">';foreach($query->posts as$p)$out.='<article class="related-post-card"><a href="'.esc_url(get_permalink($p)).'">'.get_the_post_thumbnail($p,'medium').'<h3>'.esc_html(get_the_title($p)).'</h3></a><time datetime="'.esc_attr(get_the_date('c',$p)).'">'.esc_html(get_the_date('',$p)).'</time></article>';return $out.'</div></section>';}
        return '';
    }
    public static function decorate($html) {
        if(is_admin()||is_feed()||!is_singular()||!in_the_loop()||!is_main_query()||get_the_ID()!==get_queried_object_id()||post_password_required())return $html;
        $notice=get_post_meta(get_the_ID(),'_pz_correction',true);if($notice)$html='<aside class="pz-update"><strong>'.esc_html__('Update / correction','piratez-editorial').'</strong><p>'.nl2br(esc_html($notice)).'</p></aside>'.$html;
        if(current_theme_supports('piratez-editorial-placement'))return '<div class="pz-editorial-content">'.$html.'</div>';
        $before=self::component('reading_time').self::component('toc').self::component('reading_progress');
        $after=self::component('sharing').self::component('related');
        return $before.'<div class="pz-editorial-content">'.$html.'</div>'.$after;
    }
}

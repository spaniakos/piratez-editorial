<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Settings {
    const OPTION = 'piratez_editorial';
    const FEATURES = ['toc','reading_time','reading_progress','sharing','related','code','checklist','preferences','print'];
    public static function defaults() {
        return ['schema'=>1,'post'=>array_merge(array_fill_keys(self::FEATURES,true),['preferences'=>false,'print'=>false]),'page'=>array_merge(array_fill_keys(self::FEATURES,false),['toc'=>true,'code'=>true,'checklist'=>true]),'profiles'=>array_fill_keys(['twitter','facebook','instagram','linkedin','github','youtube','email'],''),'catalogue'=>false,'workflow'=>false,'modules'=>array_fill_keys(['visual','products','maintenance','advanced'],true)];
    }
    public static function get() {
        $saved = get_option(self::OPTION, []); $saved = is_array($saved) ? $saved : [];
        $defaults = self::defaults();
        foreach (['post','page','profiles','modules'] as $key) { $saved[$key] = array_merge($defaults[$key],is_array($saved[$key]??null)?$saved[$key]:[]); }
        return array_merge($defaults,$saved);
    }
    public static function sanitize($input) {
        $out = self::defaults(); $input = is_array($input) ? $input : [];
        foreach (['post','page'] as $type) { foreach (self::FEATURES as $key) { $out[$type][$key] = !empty($input[$type][$key]); } }
        foreach ($out['profiles'] as $key=>$value) { $out['profiles'][$key] = $key==='email' ? sanitize_email($input['profiles'][$key]??'') : esc_url_raw($input['profiles'][$key]??'',['https','http']); }
        $out['catalogue'] = !empty($input['catalogue']);
        foreach($out['modules'] as$key=>$value)$out['modules'][$key]=!empty($input['modules'][$key]);
        $out['workflow'] = !empty($input['workflow']);
        return $out;
    }
    public static function enabled($feature, $id = 0) {
        $id = $id ?: get_queried_object_id();
        $override = get_post_meta($id, '_pz_'.$feature, true);
        if ($override === 'on' || $override === 'off') { return $override === 'on'; }
        $type = get_post_type($id) === 'post' ? 'post' : 'page';
        return !empty(self::get()[$type][$feature]);
    }
    public static function import_preview() {
        $mods = get_theme_mods(); $mods = is_array($mods)?$mods:[]; $current = self::get(); $changes = [];
        $map=['toc'=>'table_of_contents','reading_time'=>'reading_time','reading_progress'=>'reading_progress','sharing'=>'social_sharing','related'=>'related_posts'];
        foreach ($map as $feature=>$old) {
            if (array_key_exists('piratez_'.$old,$mods)) {
                $changes['post.'.$feature]=(bool)$mods['piratez_'.$old];
                if ($feature==='toc') { $changes['page.toc']=(bool)$mods['piratez_'.$old]; }
            }
        }
        foreach ($current['profiles'] as $network=>$unused) { if (array_key_exists('piratez_social_'.$network,$mods)) { $changes['profiles.'.$network]=$network==='email'?sanitize_email($mods['piratez_social_'.$network]):esc_url_raw($mods['piratez_social_'.$network]); } }
        return ['source'=>get_stylesheet(),'changes'=>$changes,'already_imported'=>(bool)get_option('pz_editorial_imported',false),'target_hash'=>hash('sha256',wp_json_encode([$current,get_stylesheet(),$changes]))];
    }
    public static function import($hash) {
        if (!current_user_can('manage_options')) { return new \WP_Error('forbidden','Administrator permission required.'); }
        $preview=self::import_preview();
        if ($preview['already_imported']) { return new \WP_Error('already_imported','Settings have already been imported. Later edits are preserved.'); }
        if (!hash_equals($preview['target_hash'],(string)$hash)) { return new \WP_Error('stale','Settings changed. Preview again.'); }
        $settings=self::get(); foreach ($preview['changes'] as $path=>$value) { [$group,$key]=explode('.',$path); $settings[$group][$key]=$value; }
        update_option(self::OPTION,$settings,false);
        if (self::get()!==$settings) { return new \WP_Error('write_failed','Settings could not be saved.'); }
        add_option('pz_editorial_imported',['source'=>$preview['source'],'time'=>time(),'changes'=>$preview['changes']], '', false);
        return true;
    }
    public static function register() {
        register_setting('piratez_editorial',self::OPTION,['type'=>'array','sanitize_callback'=>[self::class,'sanitize']]);
        foreach (['post','page'] as $type) {
            foreach (self::FEATURES as $feature) { register_post_meta($type,'_pz_'.$feature,['type'=>'string','single'=>true,'default'=>'inherit','show_in_rest'=>true,'sanitize_callback'=>function($v){return in_array($v,['inherit','on','off'],true)?$v:'inherit';},'auth_callback'=>function($allowed,$key,$id){return current_user_can('edit_post',$id);}]); }
            foreach (['_pz_correction','_pz_review_date'] as $key) { register_post_meta($type,$key,['type'=>'string','single'=>true,'show_in_rest'=>true,'sanitize_callback'=>'sanitize_textarea_field','auth_callback'=>function($allowed,$key,$id){return current_user_can('edit_post',$id);}]); }
        }
    }
}

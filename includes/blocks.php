<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Blocks {
    public static function register() {
        add_filter('allowed_block_types_all',function($allowed){
            $groups=['visual'=>['slideshow','before-after','download'],'products'=>['product-card','products','product-comparison','specifications','product-details'],'maintenance'=>['series','page-navigation'],'advanced'=>['itinerary','itinerary-day','map','glossary']];
            $hidden=[];foreach($groups as$key=>$names)if(!Settings::get()['modules'][$key])foreach($names as$name)$hidden[]='piratez-editorial/'.$name;
            if(!$hidden)return $allowed;$names=$allowed===true?array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered()):(is_array($allowed)?$allowed:[]);return array_values(array_diff($names,$hidden));
        });
        wp_register_script('pz-editorial-blocks',plugins_url('assets/blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components','wp-i18n'],PIRATEZ_EDITORIAL_VERSION,true);
        register_block_type('piratez-editorial/callout',['api_version'=>3,'editor_script'=>'pz-editorial-blocks','attributes'=>['kind'=>['type'=>'string','default'=>'note'],'title'=>['type'=>'string','default'=>'Note']],'supports'=>['html'=>false]]);
        register_block_type('piratez-editorial/code',['api_version'=>3,'editor_script'=>'pz-editorial-blocks','attributes'=>['content'=>['type'=>'string','source'=>'text','selector'=>'code'],'language'=>['type'=>'string','default'=>'plain'],'filename'=>['type'=>'string','default'=>'']],'supports'=>['html'=>false]]);
        foreach(['piratez-specifications'=>'PirateZ specifications','piratez-comparison'=>'PirateZ comparison','piratez-compact'=>'PirateZ compact'] as $style=>$label)register_block_style('core/table',['name'=>$style,'label'=>$label]);
        foreach(['core/code','core/footnotes','core/tabs','core/details']as$name)register_block_style($name,['name'=>'piratez','label'=>'PirateZ']);
    }
}

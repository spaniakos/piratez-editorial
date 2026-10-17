<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Blocks {
    public static function register() {
        wp_register_script('pz-editorial-blocks',plugins_url('assets/blocks.js',PIRATEZ_EDITORIAL_FILE),['wp-blocks','wp-element','wp-block-editor','wp-components','wp-i18n'],PIRATEZ_EDITORIAL_VERSION,true);
        register_block_type('piratez-editorial/callout',['api_version'=>3,'editor_script'=>'pz-editorial-blocks','attributes'=>['kind'=>['type'=>'string','default'=>'note'],'title'=>['type'=>'string','default'=>'Note']],'supports'=>['html'=>false]]);
        register_block_type('piratez-editorial/code',['api_version'=>3,'editor_script'=>'pz-editorial-blocks','attributes'=>['content'=>['type'=>'string','source'=>'text','selector'=>'code'],'language'=>['type'=>'string','default'=>'plain'],'filename'=>['type'=>'string','default'=>'']],'supports'=>['html'=>false]]);
        foreach(['core/code','core/footnotes','core/tabs','core/details']as$name)register_block_style($name,['name'=>'piratez','label'=>'PirateZ']);
    }
}

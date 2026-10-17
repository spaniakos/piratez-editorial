<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Admin {
    public static function boot() {
        add_action('admin_menu',function(){add_submenu_page('piratez-manager','Editorial','Editorial','manage_options','piratez-editorial',[self::class,'page']);},20);
        add_action('admin_post_pz_editorial_import',function(){if(!current_user_can('manage_options'))wp_die('Forbidden',403);check_admin_referer('pz_editorial_import');$result=Settings::import(sanitize_text_field(wp_unslash($_POST['hash']??'')));if(is_wp_error($result))wp_die(esc_html($result->get_error_message()));wp_safe_redirect(admin_url('admin.php?page=piratez-editorial&imported=1'));exit;});
        add_action('add_meta_boxes',function(){foreach(['post','page']as$type)add_meta_box('pz-editorial','PirateZ Editorial',[self::class,'meta_box'],$type,'side');});
        add_action('save_post',[self::class,'save'],10,2);
    }
    public static function page() {
        if(!current_user_can('manage_options'))return;$s=Settings::get();echo '<div class="wrap"><h1>PirateZ Editorial</h1><p>Portable content tools. Settings apply independently to posts and pages.</p><form action="options.php" method="post">';settings_fields('piratez_editorial');
        foreach(['post'=>'Posts','page'=>'Pages']as$type=>$label){echo '<h2>'.esc_html($label).'</h2>';foreach(Settings::FEATURES as$key)echo '<label style="display:block"><input type="checkbox" name="piratez_editorial['.esc_attr($type).']['.esc_attr($key).']" value="1" '.checked($s[$type][$key],true,false).'> '.esc_html(ucwords(str_replace('_',' ',$key))).'</label>';}
        echo '<h2>Public social profiles</h2>';foreach($s['profiles']as$key=>$value)echo '<p><label>'.esc_html(ucfirst($key)).' <input class="regular-text" type="'.($key==='email'?'email':'url').'" name="piratez_editorial[profiles]['.esc_attr($key).']" value="'.esc_attr($value).'"></label></p>';
        echo '<h2>Optional product catalogue</h2><label><input type="checkbox" name="piratez_editorial[catalogue]" value="1" '.checked($s['catalogue'],true,false).'> Enable structured products (available in the product milestone)</label>';submit_button();echo '</form><hr><h2>Theme settings migration</h2>';
        $preview=Settings::import_preview();echo '<p>Source: '.esc_html($preview['source']).'. Existing source settings are retained.</p><pre>'.esc_html(wp_json_encode($preview['changes'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</pre>';
        if(!$preview['already_imported']){echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="pz_editorial_import"><input type="hidden" name="hash" value="'.esc_attr($preview['target_hash']).'">';wp_nonce_field('pz_editorial_import');submit_button('Confirm settings import','secondary');echo '</form>';}else echo '<p>Already imported. Later Editorial edits are preserved.</p>';
        do_action('pz_editorial_admin_sections');
        echo '<p>Sources use native WordPress footnotes. Layouts are available in the block inserter under PirateZ. Image alt editing remains in Image Optimize.</p></div>';
    }
    public static function checks($post) {
        $checks=[];$content=$post->post_content;
        if($post->post_type==='post'){if(trim($post->post_excerpt)==='')$checks[]='Add an excerpt.';if(!has_post_thumbnail($post))$checks[]='Choose a featured image.';if(!has_term('','category',$post))$checks[]='Choose a category.';if(!has_term('','post_tag',$post))$checks[]='Review article tags.';}
        if(preg_match('/lorem ipsum|TODO|\[placeholder\]/iu',$content))$checks[]='Review placeholder text.';
        if(!preg_match('/<h[2-6]\b/i',$content))$checks[]='Consider section headings for longer content.';
        $p=new \WP_HTML_Tag_Processor($content);while($p->next_tag('IMG')){if($p->get_attribute('alt')===null||$p->get_attribute('alt')===''){$checks[]='Review image alt text or confirm decorative intent in Image Optimize.';break;}}
        return $checks;
    }
    public static function meta_box($post) {
        wp_nonce_field('pz_editorial_post','pz_editorial_nonce');
        foreach(Settings::FEATURES as$key){$value=get_post_meta($post->ID,'_pz_'.$key,true);echo '<p><label>'.esc_html(ucwords(str_replace('_',' ',$key))).' <select name="pz_editorial['.esc_attr($key).']">';foreach(['inherit'=>'Site default','on'=>'On','off'=>'Off']as$v=>$label)echo '<option value="'.esc_attr($v).'" '.selected($value?:'inherit',$v,false).'>'.esc_html($label).'</option>';echo '</select></label></p>';}
        echo '<p><label>Public update / correction<textarea name="pz_correction" class="widefat">'.esc_textarea(get_post_meta($post->ID,'_pz_correction',true)).'</textarea></label></p><p><label>Review date <input type="date" name="pz_review_date" value="'.esc_attr(get_post_meta($post->ID,'_pz_review_date',true)).'"></label></p>';
        if($post->post_type==='page'){
            echo '<p>Page widths: choose a template in the Page sidebar. PirateZ provides Default, Narrow, Wide, Full width and Landing.</p>';
            if(current_theme_supports('piratez-editorial-page-layouts'))foreach(['title','header','footer']as$part){$value=get_post_meta($post->ID,'_pz_hide_'.$part,true);echo '<p><label><input type="checkbox" name="pz_hide['.esc_attr($part).']" value="on" '.checked($value,'on',false).'> Hide '.esc_html($part).'</label></p>';}
            else echo '<p>The active theme does not advertise PirateZ outer-layout controls. Native block alignment remains available.</p>';
        }
        if(Settings::enabled('checklist',$post->ID)){echo '<h3>Publishing checks (advisory)</h3><ul>';foreach(self::checks($post)as$check)echo '<li>'.esc_html($check).'</li>';echo '</ul>';}
    }
    public static function save($id,$post) {
        if(!in_array($post->post_type,['post','page'],true)||!current_user_can('edit_post',$id)||wp_is_post_revision($id)||wp_is_post_autosave($id)||!wp_verify_nonce($_POST['pz_editorial_nonce']??'','pz_editorial_post'))return;
        if($post->post_type==='page'&&current_theme_supports('piratez-editorial-page-layouts'))foreach(['title','header','footer']as$part)update_post_meta($id,'_pz_hide_'.$part,($_POST['pz_hide'][$part]??'')==='on'?'on':'off');
        foreach(Settings::FEATURES as$key){$v=wp_unslash($_POST['pz_editorial'][$key]??'inherit');update_post_meta($id,'_pz_'.$key,in_array($v,['inherit','on','off'],true)?$v:'inherit');}
        update_post_meta($id,'_pz_correction',sanitize_textarea_field(wp_unslash($_POST['pz_correction']??'')));
        $date=sanitize_text_field(wp_unslash($_POST['pz_review_date']??''));update_post_meta($id,'_pz_review_date',preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)?$date:'');
    }
}

<?php
/**
 * Plugin Name: PirateZ Editorial
 * Plugin URI: https://github.com/spaniakos/piratez-editorial
 * Description: Portable editorial blocks, article tools, page patterns and product showcases.
 * Version: 1.2.0
 * Requires at least: 7.1.3
 * Requires PHP: 8.3
 * Update URI: https://github.com/spaniakos/piratez-editorial
 * Author: Spaniakos
 * License: GPL-2.0-or-later
 * Text Domain: piratez-editorial
 */
if (!defined('ABSPATH')) { exit; }
define('PIRATEZ_EDITORIAL_VERSION', '1.2.0');
define('PIRATEZ_EDITORIAL_FILE', __FILE__);
require_once __DIR__ . '/includes/admin-menu.php';
require_once __DIR__ . '/includes/github-updater.php';
new \PirateZ\Editorial\GitHubUpdater(['name'=>'PirateZ Editorial','type'=>'plugin','slug'=>'piratez-editorial','repo'=>'spaniakos/piratez-editorial','key'=>plugin_basename(__FILE__),'entry'=>'piratez-editorial.php']);
foreach (['settings','content','blocks','admin','patterns','visual','products'] as $part) { require_once __DIR__ . '/includes/' . $part . '.php'; }
add_action('init', function () { \PirateZ\Editorial\Settings::register(); \PirateZ\Editorial\Blocks::register(); \PirateZ\Editorial\Patterns::register(); \PirateZ\Editorial\Visual::register(); });
add_action('after_setup_theme', function () {
    $theme=wp_get_theme(get_template());
    if ($theme->get('TextDomain')==='piratez-cyberpunk' && version_compare($theme->get('Version'),'1.4.0','<')) {
        add_action('admin_notices',function(){if(current_user_can('manage_options'))echo '<div class="notice notice-warning"><p>Editorial is in migration mode with the older PirateZ theme. Preview/import settings in PirateZ → Editorial, then update the theme to 1.4.0. Existing theme engines remain active until the handoff.</p></div>';});
        return;
    }
    \PirateZ\Editorial\Content::boot();
}, 99);
\PirateZ\Editorial\Admin::boot();

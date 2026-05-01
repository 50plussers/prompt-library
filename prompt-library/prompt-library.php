<?php
/**
 * Plugin Name: Prompt Library
 * Plugin URI:  https://50plussers.be
 * Description: Toon een doorzoekbare prompt-bibliotheek op elke WordPress-pagina.
 * Version:     1.0.0
 * Author:      Blueblot
 * Author URI:  https://blueblot.be
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: prompt-library
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PL_VERSION',     '1.0.0' );
define( 'PL_PLUGIN_FILE', __FILE__ );
define( 'PL_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'PL_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

require_once PL_PLUGIN_DIR . 'includes/class-plugin.php';

function pl_run() {
    $plugin = new PL_Plugin();
    $plugin->run();
}
pl_run();

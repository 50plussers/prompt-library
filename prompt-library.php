<?php
/**
 * Plugin Name: Prompt Library
 * Plugin URI:  https://50plussers.be
 * Description: Doorzoekbare AI-promptbibliotheek voor 50plussers.be met PMPro-integratie.
 * Version:     2.0.0
 * Author:      Blueblot
 * Author URI:  https://blueblot.be
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: prompt-library
 * Domain Path: /languages
 * GitHub Plugin URI: 50plussers/prompt-library
 * Primary Branch: main
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PL_VERSION',    '2.0.0' );
define( 'PL_PLUGIN_FILE', __FILE__ );
define( 'PL_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'PL_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

require_once PL_PLUGIN_DIR . 'includes/class-cpt.php';
require_once PL_PLUGIN_DIR . 'includes/class-ajax.php';
require_once PL_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once PL_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, 'pl_activate' );

function pl_activate() {
    $cpt = new PL_CPT();
    $cpt->register();
    flush_rewrite_rules();
    pl_insert_default_categories();
}

function pl_insert_default_categories() {
    $cats = [
        'gezondheid-welzijn'     => 'Gezondheid & Welzijn',
        'reizen'                 => 'Reizen',
        'familie-kleinkinderen'  => 'Familie & Kleinkinderen',
        'financien-pensioen'     => 'Financiën & Pensioen',
        'hobbys-vrije-tijd'      => "Hobby's & Vrije tijd",
        'schrijven-communicatie' => 'Schrijven & Communicatie',
        'digitaal-technologie'   => 'Digitaal & Technologie',
        'werk-vrijwilligerswerk' => 'Werk & Vrijwilligerswerk',
    ];
    foreach ( $cats as $slug => $name ) {
        if ( ! term_exists( $slug, 'pl_category' ) ) {
            wp_insert_term( $name, 'pl_category', [ 'slug' => $slug ] );
        }
    }
}

function pl_run() {
    $plugin = new PL_Plugin();
    $plugin->run();
}
pl_run();

<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_Plugin {

    public function run() {
        $cpt = new PL_CPT();
        $cpt->register();

        $ajax = new PL_Ajax();
        $ajax->init();

        $shortcode = new PL_Shortcode();
        $shortcode->init();

        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        if ( is_admin() ) {
            require_once PL_PLUGIN_DIR . 'admin/class-admin.php';
            $admin = new PL_Admin();
            $admin->init();
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'prompt-library',
            false,
            dirname( plugin_basename( PL_PLUGIN_FILE ) ) . '/languages/'
        );
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'prompt-library-frontend',
            PL_PLUGIN_URL . 'public/css/frontend.css',
            [],
            PL_VERSION
        );

        wp_enqueue_script(
            'prompt-library-frontend',
            PL_PLUGIN_URL . 'public/js/frontend.js',
            [ 'jquery' ],
            PL_VERSION,
            true
        );

        wp_localize_script( 'prompt-library-frontend', 'plData', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'pl_nonce' ),
            'isLoggedIn' => is_user_logged_in(),
            'strings'    => [
                'copied'      => __( 'Gekopieerd!', 'prompt-library' ),
                'copy'        => __( 'Kopieer prompt', 'prompt-library' ),
                'loginToLike' => __( 'Log in om te liken', 'prompt-library' ),
                'noResults'   => __( 'Geen prompts gevonden.', 'prompt-library' ),
            ],
        ] );
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'prompt-library-admin',
            PL_PLUGIN_URL . 'admin/css/admin.css',
            [],
            PL_VERSION
        );

        wp_enqueue_script(
            'prompt-library-admin',
            PL_PLUGIN_URL . 'admin/js/admin.js',
            [ 'jquery' ],
            PL_VERSION,
            true
        );
    }
}

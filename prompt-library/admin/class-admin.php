<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_Admin {

    public function init() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_menu_pages() {
        add_menu_page(
            __( 'Prompt Library', 'prompt-library' ),
            __( 'Prompt Library', 'prompt-library' ),
            'manage_options',
            'prompt-library-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-book-alt',
            60
        );
    }

    public function register_settings() {
        register_setting( 'pl_options_group', 'pl_options' );

        add_settings_section(
            'pl_main_section',
            __( 'Algemene instellingen', 'prompt-library' ),
            null,
            'prompt-library-settings'
        );

        add_settings_field(
            'pl_prompts_per_page',
            __( 'Prompts per pagina', 'prompt-library' ),
            [ $this, 'render_prompts_per_page_field' ],
            'prompt-library-settings',
            'pl_main_section'
        );
    }

    public function render_prompts_per_page_field() {
        $options = get_option( 'pl_options', [] );
        $value   = $options['prompts_per_page'] ?? 12;
        echo '<input type="number" name="pl_options[prompts_per_page]" value="' . esc_attr( $value ) . '" min="1" max="100" class="small-text">';
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Gebruik de shortcode [prompt_library] om de bibliotheek op een pagina te tonen.', 'prompt-library' ); ?></p>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'pl_options_group' );
                do_settings_sections( 'prompt-library-settings' );
                submit_button( __( 'Instellingen opslaan', 'prompt-library' ) );
                ?>
            </form>
        </div>
        <?php
    }
}

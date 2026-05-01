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

        add_submenu_page(
            'prompt-library-settings',
            __( 'Instellingen', 'prompt-library' ),
            __( 'Instellingen', 'prompt-library' ),
            'manage_options',
            'prompt-library-settings',
            [ $this, 'render_settings_page' ]
        );

        add_submenu_page(
            'prompt-library-settings',
            __( 'Prompts', 'prompt-library' ),
            __( 'Prompts', 'prompt-library' ),
            'manage_options',
            'edit.php?post_type=pl_prompt'
        );

        add_submenu_page(
            'prompt-library-settings',
            __( 'Nieuwe prompt', 'prompt-library' ),
            __( 'Nieuwe prompt', 'prompt-library' ),
            'manage_options',
            'post-new.php?post_type=pl_prompt'
        );

        add_submenu_page(
            'prompt-library-settings',
            __( 'Categorieën', 'prompt-library' ),
            __( 'Categorieën', 'prompt-library' ),
            'manage_options',
            'edit-tags.php?taxonomy=pl_category&post_type=pl_prompt'
        );
    }

    public function register_settings() {
        register_setting( 'pl_options_group', 'pl_options', [ $this, 'sanitize_options' ] );

        // Section: Algemeen
        add_settings_section( 'pl_section_general', __( 'Weergave', 'prompt-library' ), null, 'prompt-library-settings' );

        add_settings_field( 'pl_prompts_per_page', __( 'Prompts per pagina', 'prompt-library' ),
            [ $this, 'field_prompts_per_page' ], 'prompt-library-settings', 'pl_section_general' );

        // Section: Content
        add_settings_section( 'pl_section_content', __( 'Paginacontent', 'prompt-library' ), null, 'prompt-library-settings' );

        add_settings_field( 'pl_intro_text', __( 'Introtekst (voor iedereen zichtbaar)', 'prompt-library' ),
            [ $this, 'field_intro_text' ], 'prompt-library-settings', 'pl_section_content' );

        // Section: Toegang
        add_settings_section( 'pl_section_access', __( 'Toegangsbeperking (PMPro)', 'prompt-library' ), null, 'prompt-library-settings' );

        add_settings_field( 'pl_pmpro_level', __( 'PMPro membership level', 'prompt-library' ),
            [ $this, 'field_pmpro_level' ], 'prompt-library-settings', 'pl_section_access' );

        add_settings_field( 'pl_cta_text', __( 'Tekst voor niet-leden', 'prompt-library' ),
            [ $this, 'field_cta_text' ], 'prompt-library-settings', 'pl_section_access' );

        add_settings_field( 'pl_cta_url', __( 'URL "Word lid"-knop', 'prompt-library' ),
            [ $this, 'field_cta_url' ], 'prompt-library-settings', 'pl_section_access' );
    }

    public function sanitize_options( $input ) {
        $clean = [];
        $clean['prompts_per_page'] = min( 100, max( 1, intval( $input['prompts_per_page'] ?? 12 ) ) );
        $clean['intro_text']       = wp_kses_post( $input['intro_text'] ?? '' );
        $clean['pmpro_level']      = sanitize_text_field( $input['pmpro_level'] ?? 'premium' );
        $clean['cta_text']         = sanitize_text_field( $input['cta_text'] ?? '' );
        $clean['cta_url']          = esc_url_raw( $input['cta_url'] ?? '' );
        return $clean;
    }

    private function get( $key, $default = '' ) {
        $options = get_option( 'pl_options', [] );
        return $options[ $key ] ?? $default;
    }

    public function field_prompts_per_page() {
        $v = $this->get( 'prompts_per_page', 12 );
        echo '<input type="number" name="pl_options[prompts_per_page]" value="' . esc_attr( $v ) . '" min="1" max="100" class="small-text">';
    }

    public function field_intro_text() {
        $v = $this->get( 'intro_text' );
        echo '<textarea name="pl_options[intro_text]" rows="5" class="large-text">' . esc_textarea( $v ) . '</textarea>';
        echo '<p class="description">' . esc_html__( 'HTML toegestaan. Zichtbaar voor iedereen, ook niet-leden.', 'prompt-library' ) . '</p>';
    }

    public function field_pmpro_level() {
        $v = $this->get( 'pmpro_level', 'premium' );
        echo '<input type="text" name="pl_options[pmpro_level]" value="' . esc_attr( $v ) . '" class="regular-text">';
        echo '<p class="description">' . esc_html__( 'Naam of ID van het PMPro-level dat toegang heeft. Laat leeg om iedereen toe te laten.', 'prompt-library' ) . '</p>';
    }

    public function field_cta_text() {
        $v = $this->get( 'cta_text', __( 'Word lid om alle prompts te bekijken.', 'prompt-library' ) );
        echo '<input type="text" name="pl_options[cta_text]" value="' . esc_attr( $v ) . '" class="large-text">';
    }

    public function field_cta_url() {
        $v = $this->get( 'cta_url' );
        echo '<input type="url" name="pl_options[cta_url]" value="' . esc_attr( $v ) . '" class="large-text" placeholder="https://">';
        echo '<p class="description">' . esc_html__( 'URL van de lidmaatschapspagina.', 'prompt-library' ) . '</p>';
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap pl-admin-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p class="pl-shortcode-hint">
                <?php esc_html_e( 'Gebruik de shortcode', 'prompt-library' ); ?>
                <code>[prompt_library]</code>
                <?php esc_html_e( 'op een pagina of post.', 'prompt-library' ); ?>
            </p>
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

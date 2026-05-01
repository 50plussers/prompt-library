<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_CPT {

    public function register() {
        add_action( 'init',             [ $this, 'register_post_type' ] );
        add_action( 'init',             [ $this, 'register_taxonomy' ] );
        add_action( 'add_meta_boxes',   [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_pl_prompt', [ $this, 'save_meta' ] );
    }

    public function register_post_type() {
        $labels = [
            'name'               => __( 'Prompts', 'prompt-library' ),
            'singular_name'      => __( 'Prompt', 'prompt-library' ),
            'add_new'            => __( 'Nieuwe prompt', 'prompt-library' ),
            'add_new_item'       => __( 'Nieuwe prompt toevoegen', 'prompt-library' ),
            'edit_item'          => __( 'Prompt bewerken', 'prompt-library' ),
            'search_items'       => __( 'Prompts zoeken', 'prompt-library' ),
            'not_found'          => __( 'Geen prompts gevonden', 'prompt-library' ),
            'menu_name'          => __( 'Prompts', 'prompt-library' ),
        ];

        register_post_type( 'pl_prompt', [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'prompt-library-settings',
            'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
            'rewrite'       => false,
            'show_in_rest'  => true,
        ] );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'pl_description_box',
            __( 'Beschrijving (zichtbaar op de kaart)', 'prompt-library' ),
            [ $this, 'render_description_box' ],
            'pl_prompt',
            'normal',
            'high'
        );
    }

    public function render_description_box( $post ) {
        $desc = get_post_meta( $post->ID, 'pl_description', true );
        wp_nonce_field( 'pl_save_meta', 'pl_meta_nonce' );
        echo '<textarea name="pl_description" rows="3" style="width:100%;font-size:14px;padding:8px;">' . esc_textarea( $desc ) . '</textarea>';
        echo '<p class="description">' . esc_html__( 'Korte beschrijving die zichtbaar is op de kaart. De tekst hieronder is de eigenlijke prompt.', 'prompt-library' ) . '</p>';
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['pl_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pl_meta_nonce'], 'pl_save_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( isset( $_POST['pl_description'] ) ) {
            update_post_meta( $post_id, 'pl_description', sanitize_textarea_field( wp_unslash( $_POST['pl_description'] ) ) );
        }
    }

    public function register_taxonomy() {
        $labels = [
            'name'          => __( 'Categorieën', 'prompt-library' ),
            'singular_name' => __( 'Categorie', 'prompt-library' ),
            'search_items'  => __( 'Categorieën zoeken', 'prompt-library' ),
            'all_items'     => __( 'Alle categorieën', 'prompt-library' ),
            'edit_item'     => __( 'Categorie bewerken', 'prompt-library' ),
            'add_new_item'  => __( 'Nieuwe categorie toevoegen', 'prompt-library' ),
            'menu_name'     => __( 'Categorieën', 'prompt-library' ),
        ];

        register_taxonomy( 'pl_category', 'pl_prompt', [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_admin_column' => true,
            'rewrite'           => false,
            'show_in_rest'      => true,
        ] );
    }
}

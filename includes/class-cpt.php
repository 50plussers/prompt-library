<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_CPT {

    public function register() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomy' ] );
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

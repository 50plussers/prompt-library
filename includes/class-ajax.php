<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_Ajax {

    public function init() {
        $actions = [ 'pl_load_prompts', 'pl_track_copy', 'pl_track_view', 'pl_toggle_like', 'pl_get_prompt' ];
        foreach ( $actions as $action ) {
            add_action( 'wp_ajax_' . $action,        [ $this, str_replace( 'pl_', '', $action ) ] );
            add_action( 'wp_ajax_nopriv_' . $action, [ $this, str_replace( 'pl_', '', $action ) ] );
        }
    }

    public function load_prompts() {
        check_ajax_referer( 'pl_nonce', 'nonce' );

        $options  = get_option( 'pl_options', [] );
        $per_page = intval( $options['prompts_per_page'] ?? 12 );
        $search   = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
        $category = sanitize_text_field( wp_unslash( $_POST['category'] ?? '' ) );
        $page     = max( 1, intval( $_POST['page'] ?? 1 ) );

        $args = [
            'post_type'      => 'pl_prompt',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ( $search ) {
            $args['s'] = $search;
        }

        if ( $category ) {
            $args['tax_query'] = [ [
                'taxonomy' => 'pl_category',
                'field'    => 'slug',
                'terms'    => $category,
            ] ];
        }

        $query = new WP_Query( $args );

        ob_start();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                PL_Shortcode::render_card( get_the_ID() );
            }
            wp_reset_postdata();
        } else {
            echo '<p class="pl-no-results">' . esc_html__( 'Geen prompts gevonden.', 'prompt-library' ) . '</p>';
        }
        $html = ob_get_clean();

        wp_send_json_success( [
            'html'      => $html,
            'total'     => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'page'      => $page,
        ] );
    }

    public function get_prompt() {
        check_ajax_referer( 'pl_nonce', 'nonce' );
        $post_id = intval( $_POST['id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'pl_prompt' ) {
            wp_send_json_error( [ 'message' => 'invalid' ] );
        }
        $text = get_post_meta( $post_id, 'pl_prompt', true );
        if ( ! $text ) {
            $text = get_post_meta( $post_id, 'pl_description', true );
        }
        wp_send_json_success( [ 'text' => (string) $text ] );
    }

    public function track_copy() {
        check_ajax_referer( 'pl_nonce', 'nonce' );
        $post_id = intval( $_POST['id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'pl_prompt' ) {
            wp_send_json_error();
        }
        $count = intval( get_post_meta( $post_id, 'pl_copies', true ) ) + 1;
        update_post_meta( $post_id, 'pl_copies', $count );
        wp_send_json_success( [ 'copies' => $count ] );
    }

    public function track_view() {
        check_ajax_referer( 'pl_nonce', 'nonce' );
        $post_id = intval( $_POST['id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'pl_prompt' ) {
            wp_send_json_error();
        }
        $count = intval( get_post_meta( $post_id, 'pl_views', true ) ) + 1;
        update_post_meta( $post_id, 'pl_views', $count );
        wp_send_json_success( [ 'views' => $count ] );
    }

    public function toggle_like() {
        check_ajax_referer( 'pl_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'not_logged_in' ] );
        }
        $post_id = intval( $_POST['id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'pl_prompt' ) {
            wp_send_json_error();
        }
        $user_id  = get_current_user_id();
        $liked_by = get_post_meta( $post_id, 'pl_liked_by', true ) ?: [];
        $liked    = in_array( $user_id, $liked_by, true );

        if ( $liked ) {
            $liked_by = array_values( array_diff( $liked_by, [ $user_id ] ) );
        } else {
            $liked_by[] = $user_id;
        }

        update_post_meta( $post_id, 'pl_liked_by', $liked_by );
        $count = count( $liked_by );
        update_post_meta( $post_id, 'pl_likes', $count );

        wp_send_json_success( [
            'liked' => ! $liked,
            'count' => $count,
        ] );
    }
}

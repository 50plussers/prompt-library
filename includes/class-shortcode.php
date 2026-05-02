<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PL_Shortcode {

    public function init() {
        add_shortcode( 'prompt_library', [ $this, 'render' ] );
    }

    public function render( $atts ) {
        $options     = get_option( 'pl_options', [] );
        $intro_text  = $options['intro_text'] ?? '';
        $cta_text    = $options['cta_text'] ?? __( 'Word lid om alle prompts te bekijken.', 'prompt-library' );
        $cta_url     = $options['cta_url'] ?? '';
        $per_page    = intval( $options['prompts_per_page'] ?? 12 );
        $has_access  = $this->user_has_access( $options );

        $categories = get_terms( [
            'taxonomy'   => 'pl_category',
            'hide_empty' => true,
        ] );

        $initial_prompts = [];
        $max_pages       = 1;

        if ( $has_access ) {
            $query = new WP_Query( [
                'post_type'      => 'pl_prompt',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );
            $initial_prompts = $query->posts;
            $max_pages       = $query->max_num_pages;
            wp_reset_postdata();
        }

        ob_start();
        include PL_PLUGIN_DIR . 'public/templates/prompt-library.php';
        return ob_get_clean();
    }

    private function user_has_access( $options ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
            return true;
        }
        $level = $options['pmpro_level'] ?? 'premium';
        return pmpro_hasMembershipLevel( $level );
    }

    public static function render_card( $post_id ) {
        $post    = get_post( $post_id );
        $options = get_option( 'pl_options', [] );

        $title       = get_the_title( $post_id );
        $description = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
        $prompt_text = get_post_meta( $post_id, 'pl_prompt', true )
                    ?: get_post_meta( $post_id, 'pl_description', true );
        $date        = get_the_date( 'd/m/Y', $post_id );

        $views  = intval( get_post_meta( $post_id, 'pl_views', true ) );
        $likes  = intval( get_post_meta( $post_id, 'pl_likes', true ) );
        $copies = intval( get_post_meta( $post_id, 'pl_copies', true ) );

        $liked_by = get_post_meta( $post_id, 'pl_liked_by', true ) ?: [];
        $user_liked = is_user_logged_in() && in_array( get_current_user_id(), $liked_by );

        $terms     = get_the_terms( $post_id, 'pl_category' );
        $term_name = ( $terms && ! is_wp_error( $terms ) ) ? esc_html( $terms[0]->name ) : '';
        $term_slug = ( $terms && ! is_wp_error( $terms ) ) ? esc_attr( $terms[0]->slug ) : '';

        $prompt_text = wp_strip_all_tags( $content );
        ?>
        <div class="pl-card" data-id="<?php echo $post_id; ?>">
            <div class="pl-card-top">
                <?php if ( $term_name ) : ?>
                    <span class="pl-cat-badge" data-slug="<?php echo $term_slug; ?>"><?php echo $term_name; ?></span>
                <?php endif; ?>
                <span class="pl-date"><?php echo esc_html( $date ); ?></span>
            </div>

            <h3 class="pl-card-title"><?php echo esc_html( $title ); ?></h3>

            <?php if ( $description ) : ?>
            <p class="pl-card-excerpt"><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>

            <div class="pl-prompt-box" data-post-id="<?php echo $post_id; ?>">
                <span class="pl-prompt-label"><?php esc_html_e( 'AI-Prompt', 'prompt-library' ); ?></span>
                <p class="pl-prompt-preview"><?php echo esc_html( $prompt_text ); ?></p>
            </div>

            <div class="pl-stats">
                <span class="pl-stat" title="<?php esc_attr_e( 'Bekeken', 'prompt-library' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="pl-views-count"><?php echo number_format_i18n( $views ); ?></span>
                </span>
                <span class="pl-stat pl-like-stat" title="<?php esc_attr_e( 'Likes', 'prompt-library' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span class="pl-likes-count"><?php echo number_format_i18n( $likes ); ?></span>
                </span>
                <span class="pl-stat" title="<?php esc_attr_e( 'Gekopieerd', 'prompt-library' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <span class="pl-copies-count"><?php echo number_format_i18n( $copies ); ?></span>
                </span>
            </div>

            <textarea class="pl-prompt-raw" readonly aria-hidden="true"><?php echo esc_textarea( $prompt_text ); ?></textarea>

            <div class="pl-actions">
                <button class="pl-copy-btn" data-id="<?php echo $post_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <span class="pl-copy-label"><?php esc_html_e( 'Kopieer prompt', 'prompt-library' ); ?></span>
                </button>

                <div class="pl-open-in">
                    <a href="https://chat.openai.com/" target="_blank" rel="noopener noreferrer" class="pl-tool-btn pl-chatgpt" title="<?php esc_attr_e( 'Open in ChatGPT', 'prompt-library' ); ?>">GPT</a>
                    <a href="https://claude.ai/" target="_blank" rel="noopener noreferrer" class="pl-tool-btn pl-claude" title="<?php esc_attr_e( 'Open in Claude', 'prompt-library' ); ?>">Claude</a>
                    <a href="https://gemini.google.com/" target="_blank" rel="noopener noreferrer" class="pl-tool-btn pl-gemini" title="<?php esc_attr_e( 'Open in Gemini', 'prompt-library' ); ?>">Gemini</a>
                    <a href="https://www.perplexity.ai/" target="_blank" rel="noopener noreferrer" class="pl-tool-btn pl-perplexity" title="<?php esc_attr_e( 'Open in Perplexity', 'prompt-library' ); ?>">Pplx</a>
                </div>

                <button class="pl-like-btn<?php echo $user_liked ? ' pl-liked' : ''; ?>" data-id="<?php echo $post_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
            </div>
        </div>
        <?php
    }
}

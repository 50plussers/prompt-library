<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Variables available: $options, $categories, $has_access, $intro_text, $cta_text, $cta_url, $initial_prompts, $max_pages, $per_page
?>
<div class="pl-wrap">

    <?php if ( $intro_text ) : ?>
        <div class="pl-intro">
            <?php echo wp_kses_post( $intro_text ); ?>
        </div>
    <?php endif; ?>

    <?php if ( ! $has_access ) : ?>
        <div class="pl-access-blocked">
            <div class="pl-locked-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <p><?php echo esc_html( $cta_text ); ?></p>
            <?php if ( $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="pl-cta-btn">
                    <?php esc_html_e( 'Word nu lid', 'prompt-library' ); ?>
                </a>
            <?php endif; ?>
        </div>

    <?php else : ?>

        <div class="pl-toolbar">
            <div class="pl-search-wrap">
                <svg class="pl-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="pl-search" class="pl-search-input" placeholder="<?php esc_attr_e( 'Zoek een prompt...', 'prompt-library' ); ?>" autocomplete="off">
            </div>

            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                <div class="pl-cat-filters">
                    <button class="pl-cat-btn active" data-cat=""><?php esc_html_e( 'Alles', 'prompt-library' ); ?></button>
                    <?php foreach ( $categories as $cat ) : ?>
                        <button class="pl-cat-btn" data-cat="<?php echo esc_attr( $cat->slug ); ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pl-grid" id="pl-grid">
            <?php
            if ( ! empty( $initial_prompts ) ) {
                foreach ( $initial_prompts as $prompt ) {
                    PL_Shortcode::render_card( $prompt->ID );
                }
            } else {
                echo '<p class="pl-no-results">' . esc_html__( 'Nog geen prompts beschikbaar.', 'prompt-library' ) . '</p>';
            }
            ?>
        </div>

        <div class="pl-pagination" id="pl-pagination" data-max-pages="<?php echo intval( $max_pages ); ?>" data-current-page="1">
            <?php if ( $max_pages > 1 ) : ?>
                <button class="pl-load-more" id="pl-load-more">
                    <?php esc_html_e( 'Meer prompts laden', 'prompt-library' ); ?>
                </button>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<?php
/**
 * Cinematic Studio hero: semantic H1, typography mask in CSS.
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
$lead    = isset( $lead ) ? (string) $lead : '';
$actions = isset( $actions ) && is_array( $actions ) ? $actions : [];
?>
<section class="hero-section hero-section--studio" data-studio-hero aria-labelledby="studio-hero-title">
    <div
        class="studio-hero__art"
        aria-hidden="true"
        data-studio-art
        data-three-module="<?php echo esc_url( get_theme_file_uri( '/assets/vendor/three.module.js' ) ); ?>"
    >
        <div class="studio-hero__static-object"></div>
    </div>
    <div class="container hero-section__inner">
        <div class="hero-section__content">
            <h1 class="hero-section__title" id="studio-hero-title">
                <span class="studio-hero__wordmark">Sobolle</span>
                <span class="studio-hero__studio">Studio</span>
            </h1>
            <?php if ( '' !== $lead ) : ?>
                <p class="hero-section__lead"><?php echo esc_html( $lead ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $actions ) ) : ?>
                <div class="hero-section__actions">
                    <?php foreach ( $actions as $action ) : ?>
                        <?php
                        if ( ! is_array( $action ) ) {
                            continue;
                        }
                        $action_label = isset( $action['label'] ) ? (string) $action['label'] : '';
                        $action_url   = isset( $action['url'] ) ? (string) $action['url'] : '';
                        $action_style = isset( $action['style'] ) ? sanitize_html_class( (string) $action['style'] ) : 'secondary';
                        if ( '' === $action_label || '' === $action_url ) {
                            continue;
                        }
                        ?>
                        <a class="button button--<?php echo esc_attr( $action_style ); ?> studio-hero__link" href="<?php echo esc_url( $action_url ); ?>">
                            <span class="studio-hero__link-label"><?php echo esc_html( $action_label ); ?></span>
                            <span class="studio-hero__link-arrow" aria-hidden="true">&#8599;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
/**
 * Foreground logotype and procedural Three.js ribbon sculpture.
 *
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
$lead = isset( $lead ) ? (string) $lead : '';
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
        </div>
    </div>
</section>

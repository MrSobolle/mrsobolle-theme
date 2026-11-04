<?php
/**
 * Quiet foreground wordmark. The experimental 3D scene is temporarily disabled.
 *
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
$lead = isset( $lead ) ? (string) $lead : '';
?>
<section class="hero-section hero-section--studio" data-studio-hero aria-labelledby="studio-hero-title">
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

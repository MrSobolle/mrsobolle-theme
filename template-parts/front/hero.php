<?php
/**
 * Front page hero section.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$eyebrow = isset( $eyebrow ) ? (string) $eyebrow : '';
$title   = isset( $title ) ? (string) $title : '';
$lead    = isset( $lead ) ? (string) $lead : '';
$actions = isset( $actions ) && is_array( $actions ) ? $actions : [];
$profile = isset( $profile ) && is_array( $profile ) ? $profile : [];

$profile_label = isset( $profile['label'] ) ? (string) $profile['label'] : '';
$profile_items = isset( $profile['items'] ) && is_array( $profile['items'] ) ? $profile['items'] : [];
?>

<section class="hero-section">
    <div class="container hero-section__inner">

        <div class="hero-section__content">
            <?php if ( '' !== $eyebrow ) : ?>
                <p class="hero-section__eyebrow">
                    <?php echo esc_html( $eyebrow ); ?>
                </p>
            <?php endif; ?>

            <?php if ( '' !== $title ) : ?>
                <h1 class="hero-section__title">
                    <?php echo esc_html( $title ); ?>
                </h1>
            <?php endif; ?>

            <?php if ( '' !== $lead ) : ?>
                <p class="hero-section__lead">
                    <?php echo esc_html( $lead ); ?>
                </p>
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

                        <a class="button button--<?php echo esc_attr( $action_style ); ?>" href="<?php echo esc_url( $action_url ); ?>">
                            <?php echo esc_html( $action_label ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( '' !== $profile_label || ! empty( $profile_items ) ) : ?>
            <div class="hero-section__panel" aria-label="<?php echo esc_attr( $profile_label ); ?>">
                <div class="profile-card">
                    <?php if ( '' !== $profile_label ) : ?>
                        <p class="profile-card__label">
                            <?php echo esc_html( $profile_label ); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ( ! empty( $profile_items ) ) : ?>
                        <ul class="profile-card__list">
                            <?php foreach ( $profile_items as $item ) : ?>
                                <li><?php echo esc_html( (string) $item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
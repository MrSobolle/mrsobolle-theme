<?php
/**
 * Site header.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Site_Data;

defined( 'ABSPATH' ) || exit;

$site_name        = Site_Data::site_name();
$site_description = Site_Data::site_description();
$home_url         = Site_Data::home_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e( 'Skip to content', 'mrsobolle' ); ?>
    </a>

    <header id="masthead" class="site-header">
        <div class="site-header__inner">

            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>

                    <div class="site-branding__logo">
                        <?php the_custom_logo(); ?>
                    </div>

                <?php else : ?>

                    <a class="site-branding__title" href="<?php echo esc_url( $home_url ); ?>" rel="home">
                        <?php echo esc_html( $site_name ); ?>
                    </a>

                    <?php if ( '' !== $site_description || is_customize_preview() ) : ?>
                        <p class="site-branding__description">
                            <?php echo esc_html( $site_description ); ?>
                        </p>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'mrsobolle' ); ?>">
                <?php if ( has_nav_menu( 'primary' ) ) : ?>

                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'menu_class'     => 'main-navigation__list',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 2,
                        ]
                    );
                    ?>

                <?php else : ?>

                    <ul id="primary-menu" class="main-navigation__list">
                        <li><a href="#about"><?php esc_html_e( 'About', 'mrsobolle' ); ?></a></li>
                        <li><a href="#projects"><?php esc_html_e( 'Projects', 'mrsobolle' ); ?></a></li>
                        <li><a href="#contact"><?php esc_html_e( 'Contact', 'mrsobolle' ); ?></a></li>
                    </ul>

                <?php endif; ?>
            </nav>

        </div>
    </header>
<?php
/**
 * Site header.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Site_Data;
use MrSobolle\Data\Directory_Data;

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

    <header id="masthead" class="site-header" data-studio-glass>
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
                        <?php
                        $work_url = Directory_Data::directory_url( 'work' );
                        $apps_url = Directory_Data::directory_url( 'apps' );
                        ?>
                        <?php if ( '' !== $work_url ) : ?>
                            <li><a href="<?php echo esc_url( $work_url ); ?>"><?php esc_html_e( 'Work', 'mrsobolle' ); ?></a></li>
                        <?php endif; ?>
                        <?php if ( '' !== $apps_url ) : ?>
                            <li><a href="<?php echo esc_url( $apps_url ); ?>"><?php esc_html_e( 'Apps', 'mrsobolle' ); ?></a></li>
                        <?php endif; ?>
                        <?php $about_url = Site_Data::content_page_url( 'about' ); ?>
                        <?php if ( '' !== $about_url ) : ?>
                            <li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About', 'mrsobolle' ); ?></a></li>
                        <?php endif; ?>
                        <?php $contact_url = Site_Data::content_page_url( 'contact' ); ?>
                        <?php if ( '' !== $contact_url ) : ?>
                            <li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'mrsobolle' ); ?></a></li>
                        <?php endif; ?>
                    </ul>

                <?php endif; ?>
            </nav>

            <?php get_template_part( 'template-parts/site/language-switcher' ); ?>

        </div>
    </header>

<?php
/**
 * Optional, accessible language navigation provided by Polylang.
 *
 * No fictional languages or non-functional switcher are shown until
 * a multilingual provider is actually configured.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_the_languages' ) ) {
    return;
}

$languages = pll_the_languages(
    [
        'raw'                    => 1,
        'hide_if_no_translation' => 1,
    ]
);

if ( ! is_array( $languages ) ) {
    return;
}

$languages = array_filter(
    $languages,
    static function ( $language ): bool {
        if ( ! is_array( $language ) || empty( $language['url'] ) || empty( $language['slug'] ) || empty( $language['name'] ) ) {
            return false;
        }

        return array_key_exists( 'has_translations', $language )
            ? ! empty( $language['has_translations'] )
            : empty( $language['no_translation'] );
    }
);

if ( count( $languages ) < 2 ) {
    return;
}
?>
<nav class="language-switcher" aria-label="<?php esc_attr_e( 'Language', 'mrsobolle' ); ?>">
    <ul class="language-switcher__list">
        <?php foreach ( $languages as $language ) : ?>
            <?php
            if ( ! is_array( $language ) || empty( $language['slug'] ) || empty( $language['url'] ) || empty( $language['name'] ) ) {
                continue;
            }
            $current = array_key_exists( 'is_current', $language )
                ? ! empty( $language['is_current'] )
                : ! empty( $language['current_lang'] );
            ?>
            <li class="language-switcher__item">
                <a
                    class="language-switcher__link<?php echo $current ? ' is-current' : ''; ?>"
                    href="<?php echo esc_url( $language['url'] ); ?>"
                    lang="<?php echo esc_attr( $language['slug'] ); ?>"
                    hreflang="<?php echo esc_attr( $language['slug'] ); ?>"
                    <?php if ( $current ) : ?>aria-current="page"<?php endif; ?>
                ><?php echo esc_html( $language['name'] ); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
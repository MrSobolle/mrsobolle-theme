<?php
/**
 * Template view helper.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Renders theme template parts with local data.
 */
final class View {

    /**
     * Renders a template part from template-parts directory.
     *
     * Example:
     * View::render( 'front/hero', [ 'title' => 'Hello' ] );
     *
     * Loads:
     * template-parts/front/hero.php
     *
     * @param string               $template Relative template path without .php.
     * @param array<string, mixed> $data     Template data.
     */
    public static function render( string $template, array $data = [] ): void {
        $template = trim( $template, '/\\' );

        if ( '' === $template ) {
            return;
        }

        $file = get_template_directory() . '/template-parts/' . $template . '.php';

        if ( ! file_exists( $file ) ) {
            Logger::warning(
                'Template part not found.',
                [
                    'template' => $template,
                ]
            );

            return;
        }

        if ( ! empty( $data ) ) {
            extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        }

        require $file;
    }
}
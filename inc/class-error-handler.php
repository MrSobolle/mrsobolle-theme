<?php
/**
 * Theme error handler.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Duplicates theme-related PHP errors into the custom theme log.
 */
final class Error_Handler {

    /**
     * Whether handlers have already been registered during current request.
     *
     * @var bool
     */
    private static bool $registered = false;

    /**
     * Previous PHP error handler.
     *
     * @var callable|null
     */
    private $previous_error_handler = null;

    /**
     * Registers error handling hooks.
     */
    public function register(): void {
        add_action( 'init', [ $this, 'register_handlers' ], 1 );
    }

    /**
     * Registers PHP error and shutdown handlers.
     */
    public function register_handlers(): void {
        if ( self::$registered ) {
            return;
        }

        self::$registered = true;

        $this->previous_error_handler = set_error_handler( [ $this, 'handle_error' ] );

        register_shutdown_function( [ $this, 'handle_shutdown' ] );
    }

    /**
     * Handles non-fatal PHP errors.
     *
     * @param int    $severity Error severity.
     * @param string $message  Error message.
     * @param string $file     File where error happened.
     * @param int    $line     Line number.
     * @return bool
     */
    public function handle_error( int $severity, string $message, string $file, int $line ): bool {
        if ( 0 === ( error_reporting() & $severity ) ) {
            return false;
        }

        if ( $this->is_theme_file( $file ) ) {
            Logger::error(
                'PHP error in theme.',
                [
                    'severity' => $severity,
                    'message'  => $message,
                    'file'     => $this->make_relative_path( $file ),
                    'line'     => $line,
                ]
            );
        }

        return $this->call_previous_error_handler( $severity, $message, $file, $line );
    }

    /**
     * Handles fatal PHP errors on shutdown.
     */
    public function handle_shutdown(): void {
        $error = error_get_last();

        if ( null === $error ) {
            return;
        }

        if ( ! $this->is_fatal_error_type( (int) $error['type'] ) ) {
            return;
        }

        $file = isset( $error['file'] ) ? (string) $error['file'] : '';

        if ( ! $this->is_theme_file( $file ) ) {
            return;
        }

        Logger::error(
            'Fatal PHP error in theme.',
            [
                'type'    => (int) $error['type'],
                'message' => isset( $error['message'] ) ? (string) $error['message'] : '',
                'file'    => $this->make_relative_path( $file ),
                'line'    => isset( $error['line'] ) ? (int) $error['line'] : 0,
            ]
        );
    }

    /**
     * Calls previous custom error handler if it exists.
     *
     * @param int    $severity Error severity.
     * @param string $message  Error message.
     * @param string $file     File where error happened.
     * @param int    $line     Line number.
     * @return bool
     */
    private function call_previous_error_handler( int $severity, string $message, string $file, int $line ): bool {
        if ( is_callable( $this->previous_error_handler ) ) {
            return (bool) call_user_func(
                $this->previous_error_handler,
                $severity,
                $message,
                $file,
                $line
            );
        }

        return false;
    }

    /**
     * Checks whether file belongs to the active theme directory.
     *
     * @param string $file Absolute file path.
     */
    private function is_theme_file( string $file ): bool {
        if ( '' === $file ) {
            return false;
        }

        $theme_dir = wp_normalize_path( get_template_directory() );
        $file      = wp_normalize_path( $file );

        return 0 === strpos( $file, $theme_dir );
    }

    /**
     * Checks whether error type is fatal.
     *
     * @param int $type PHP error type.
     */
    private function is_fatal_error_type( int $type ): bool {
        return in_array(
            $type,
            [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
                E_RECOVERABLE_ERROR,
            ],
            true
        );
    }

    /**
     * Converts absolute file path to a shorter relative path.
     *
     * @param string $file Absolute file path.
     */
    private function make_relative_path( string $file ): string {
        $theme_dir = wp_normalize_path( get_template_directory() );
        $file      = wp_normalize_path( $file );

        return ltrim( str_replace( $theme_dir, '', $file ), '/' );
    }
}
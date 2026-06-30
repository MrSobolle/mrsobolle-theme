<?php
/**
 * Theme logger.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Writes theme diagnostic messages to a protected local log file.
 */
final class Logger {

    /**
     * Log directory name inside uploads.
     */
    private const LOG_DIR_NAME = 'mrsobolle-logs';

    /**
     * Log file name.
     *
     * The PHP extension is intentional: the file starts with "<?php exit; ?>"
     * to reduce the risk of direct public reading.
     */
    private const LOG_FILE_NAME = 'theme-error-log.php';

    /**
     * Maximum log file size in bytes.
     */
    private const MAX_FILE_SIZE = 1048576; // 1 MB.

    /**
     * Writes an info message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Optional context.
     */
    public static function info( string $message, array $context = [] ): void {
        self::write( 'INFO', $message, $context );
    }

    /**
     * Writes a warning message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Optional context.
     */
    public static function warning( string $message, array $context = [] ): void {
        self::write( 'WARNING', $message, $context );
    }

    /**
     * Writes an error message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Optional context.
     */
    public static function error( string $message, array $context = [] ): void {
        self::write( 'ERROR', $message, $context );
    }

    /**
     * Writes a log entry.
     *
     * @param string               $level   Log level.
     * @param string               $message Log message.
     * @param array<string, mixed> $context Optional context.
     */
    private static function write( string $level, string $message, array $context = [] ): void {
        if ( ! self::is_enabled() ) {
            return;
        }

        $file = self::get_log_file_path();

        if ( null === $file ) {
            return;
        }

        self::rotate_if_needed( $file );

        $line = sprintf(
            "[%s] [%s] %s%s\n",
            gmdate( 'Y-m-d H:i:s' ),
            strtoupper( $level ),
            $message,
            self::format_context( $context )
        );

        file_put_contents( $file, $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    }

    /**
     * Checks whether custom theme logging is enabled.
     */
    private static function is_enabled(): bool {
        if ( defined( 'MRSOBOLLE_THEME_LOG' ) ) {
            return (bool) MRSOBOLLE_THEME_LOG;
        }

        return true;
    }

    /**
     * Returns log file path and creates the log directory if needed.
     */
    private static function get_log_file_path(): ?string {
        $upload_dir = wp_upload_dir( null, false );

        if ( empty( $upload_dir['basedir'] ) ) {
            return null;
        }

        $log_dir = trailingslashit( $upload_dir['basedir'] ) . self::LOG_DIR_NAME;

        if ( ! wp_mkdir_p( $log_dir ) ) {
            return null;
        }

        self::protect_log_directory( $log_dir );

        $log_file = trailingslashit( $log_dir ) . self::LOG_FILE_NAME;

        if ( ! file_exists( $log_file ) ) {
            file_put_contents( $log_file, "<?php exit; ?>\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        }

        return $log_file;
    }

    /**
     * Adds simple protection files to the log directory.
     *
     * @param string $log_dir Absolute log directory path.
     */
    private static function protect_log_directory( string $log_dir ): void {
        $index_file = trailingslashit( $log_dir ) . 'index.php';

        if ( ! file_exists( $index_file ) ) {
            file_put_contents( $index_file, "<?php\n// Silence is golden.\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        }

        $htaccess_file = trailingslashit( $log_dir ) . '.htaccess';

        if ( ! file_exists( $htaccess_file ) ) {
            file_put_contents( $htaccess_file, "Deny from all\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        }
    }

    /**
     * Rotates log file if it becomes too large.
     *
     * @param string $file Absolute log file path.
     */
    private static function rotate_if_needed( string $file ): void {
        if ( ! file_exists( $file ) || filesize( $file ) < self::MAX_FILE_SIZE ) {
            return;
        }

        $previous_file = dirname( $file ) . '/theme-error-log-previous.php';

        if ( file_exists( $previous_file ) ) {
            unlink( $previous_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
        }

        rename( $file, $previous_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename

        file_put_contents( $file, "<?php exit; ?>\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    }

    /**
     * Formats context array for log output.
     *
     * @param array<string, mixed> $context Context data.
     */
    private static function format_context( array $context ): string {
        if ( empty( $context ) ) {
            return '';
        }

        $context = self::sanitize_context( $context );

        $json = wp_json_encode(
            $context,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ( ! is_string( $json ) ) {
            return '';
        }

        return ' | ' . $json;
    }

    /**
     * Removes sensitive values from context.
     *
     * @param array<string, mixed> $context Context data.
     * @return array<string, mixed>
     */
    private static function sanitize_context( array $context ): array {
        $sensitive_keys = [
            'password',
            'passwd',
            'pwd',
            'token',
            'secret',
            'key',
            'cookie',
            'authorization',
            'auth',
        ];

        foreach ( $context as $key => $value ) {
            $lower_key = strtolower( (string) $key );

            foreach ( $sensitive_keys as $sensitive_key ) {
                if ( false !== strpos( $lower_key, $sensitive_key ) ) {
                    $context[ $key ] = '[hidden]';
                    continue 2;
                }
            }

            if ( is_array( $value ) ) {
                $context[ $key ] = self::sanitize_context( $value );
            }
        }

        return $context;
    }
}

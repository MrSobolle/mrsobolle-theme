<?php
/**
 * Main theme class.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Main theme bootstrap class.
 */
final class Theme {

    /**
     * Whether theme has already been booted.
     *
     * @var bool
     */
    private bool $booted = false;

    /**
     * Boots theme modules.
     */
    public function boot(): void {
        if ( $this->booted ) {
            return;
        }

        $this->booted = true;

        $this->register_modules();
    }

    /**
     * Registers theme modules.
     */
    private function register_modules(): void {
        $modules = [
            Error_Handler::class,
            Setup::class,
            Assets::class,
        ];

        foreach ( $modules as $module_class ) {
            if ( ! class_exists( $module_class ) ) {
                continue;
            }

            $module = new $module_class();

            if ( method_exists( $module, 'register' ) ) {
                $module->register();
            }
        }
    }
}
/**
 * Main frontend scripts.
 *
 * @package MrSobolle
 */

(function ($) {
    'use strict';

    /**
     * Handles smooth anchor navigation with header offset.
     */
    function initAnchorScroll() {
        $('a[href^="#"]').on('click', function (event) {
            var targetId = $(this).attr('href');
            var $target;
            var headerHeight;

            if (!targetId || targetId === '#') {
                return;
            }

            $target = $(targetId);

            if (!$target.length) {
                return;
            }

            event.preventDefault();

            headerHeight = $('.site-header').outerHeight() || 0;

            $('html, body').animate(
                {
                    scrollTop: $target.offset().top - headerHeight - 16
                },
                350
            );
        });
    }

    /**
     * Adds a body class after the page is ready.
     *
     * Useful for CSS transitions that should not run before initial render.
     */
    function initReadyClass() {
        $('body').addClass('is-ready');
    }

    $(function () {
        initReadyClass();
        initAnchorScroll();
    });
})(jQuery);
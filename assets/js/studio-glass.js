/**
 * Sobolle Studio: low-overhead pointer highlight for the unified glass panels.
 * No WebGL, parallax, timers, or background animations.
 */
(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(pointer: fine)');

    // Both the header and the two-zone bottom panel respond as one glass sheet.
    document.querySelectorAll('[data-studio-glass]').forEach(panel => {
        panel.addEventListener('pointermove', event => {
            if (!finePointer.matches || reducedMotion.matches) return;
            const rect = panel.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) return;
            const x = Math.max(0, Math.min(100, (event.clientX - rect.left) / rect.width * 100));
            const y = Math.max(0, Math.min(100, (event.clientY - rect.top) / rect.height * 100));
            panel.style.setProperty('--glass-x', x.toFixed(1) + '%');
            panel.style.setProperty('--glass-y', y.toFixed(1) + '%');
        }, {passive: true});
        panel.addEventListener('pointerleave', () => {
            panel.style.removeProperty('--glass-x');
            panel.style.removeProperty('--glass-y');
        });
    });

    // Preserve the subtle hover aura on individual navigation text links.
    document.querySelectorAll('.site-header .main-navigation a, .site-header .language-switcher__link')
        .forEach(link => {
            link.addEventListener('pointermove', event => {
                if (!finePointer.matches || reducedMotion.matches) return;
                const rect = link.getBoundingClientRect();
                if (rect.width <= 0 || rect.height <= 0) return;
                link.style.setProperty('--studio-pointer-x',
                    Math.round((event.clientX - rect.left) / rect.width * 100) + '%');
                link.style.setProperty('--studio-pointer-y',
                    Math.round((event.clientY - rect.top) / rect.height * 100) + '%');
            }, {passive: true});
            link.addEventListener('pointerleave', () => {
                link.style.removeProperty('--studio-pointer-x');
                link.style.removeProperty('--studio-pointer-y');
            });
        });
})();
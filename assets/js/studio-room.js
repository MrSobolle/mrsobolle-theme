/**
 * Single-window Studio room parallax and roaming glass highlights.
 * Purely decorative, no dependency on WebGL or network access.
 */
(() => {
    'use strict';

    const screen = document.querySelector('[data-studio-screen]');
    const room = document.querySelector('[data-studio-room]');
    if (!screen || !room) return;

    const finePointer = window.matchMedia('(pointer: fine)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const target = {x: 0, y: 0};
    const current = {x: 0, y: 0};
    let scheduled = false;

    const animate = () => {
        scheduled = false;
        if (!room.isConnected || reducedMotion.matches || document.hidden) return;

        current.x += (target.x - current.x) * .085;
        current.y += (target.y - current.y) * .085;
        room.style.setProperty('--room-x', current.x.toFixed(2) + 'px');
        room.style.setProperty('--room-y', current.y.toFixed(2) + 'px');

        if (Math.abs(target.x - current.x) > .08 ||
            Math.abs(target.y - current.y) > .08) {
            requestFrame();
        }
    };

    function requestFrame() {
        if (!scheduled && !reducedMotion.matches && !document.hidden) {
            scheduled = true;
            window.requestAnimationFrame(animate);
        }
    }

    const reset = () => {
        target.x = 0;
        target.y = 0;
        requestFrame();
    };

    if (finePointer.matches && !reducedMotion.matches) {
        // The room lags subtly behind the pointer: ~22px X, ~14px Y.
        window.addEventListener('pointermove', event => {
            target.x = (event.clientX / window.innerWidth - .5) * 44;
            target.y = (event.clientY / window.innerHeight - .5) * 28;
            requestFrame();
        }, {passive: true});
        document.addEventListener('pointerleave', reset);
        window.addEventListener('blur', reset);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) reset();
        });
    }

    const panels = document.querySelectorAll('[data-studio-glass]');
    panels.forEach(panel => {
        if (!finePointer.matches || reducedMotion.matches) return;
        panel.addEventListener('pointermove', event => {
            const rect = panel.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            const x = Math.min(100, Math.max(0, (event.clientX - rect.left) * 100 / rect.width));
            const y = Math.min(100, Math.max(0, (event.clientY - rect.top) * 100 / rect.height));
            panel.style.setProperty('--glass-x', x.toFixed(1) + '%');
            panel.style.setProperty('--glass-y', y.toFixed(1) + '%');
        }, {passive: true});
    });

    reducedMotion.addEventListener('change', event => {
        if (event.matches) {
            target.x = 0; target.y = 0;
            room.style.removeProperty('--room-x');
            room.style.removeProperty('--room-y');
        }
    });
})();

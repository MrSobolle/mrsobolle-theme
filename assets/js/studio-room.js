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
    const target = {x: 0};
    const current = {x: 0};
    let scheduled = false;

    const animate = () => {
        scheduled = false;
        if (!room.isConnected || reducedMotion.matches || document.hidden) return;

        current.x += (target.x - current.x) * .085;
        room.style.setProperty('--room-x', current.x.toFixed(2) + 'px');

        if (Math.abs(target.x - current.x) > .08) {
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

        requestFrame();
    };

    if (finePointer.matches && !reducedMotion.matches) {
        // Horizontal room parallax only: about +/-22px X.
        window.addEventListener('pointermove', event => {
            target.x = (event.clientX / window.innerWidth - .5) * 44;

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

    // All text below is an intentionally fictional, decorative log stream.
    // No real requests, server responses, credentials or user data are read.
    const terminalRows = Array.from(room.querySelectorAll('[data-studio-terminal-line]'));
    const compactScreen = window.matchMedia('(max-width: 620px)');
    const demoMessages = [
        '[core] loading adapters',
        '[api] request 028 accepted',
        '[api] response 200 (43ms)',
        '[tools] extension manifest OK',
        '[build] tasks completed: 6/6',
        'PS> inspecting viewport',
        '[render] ultrawide profile ready',
        '[sync] received next packet',
        '[core] queue is stable',
        'PS> awaiting next request'
    ];
    const terminalInitial = terminalRows.map(row => row.textContent);
    let logHistory = terminalInitial.slice();
    let logTimer = null;
    let messageIndex = 0;
    let cursor = 0;
    let typing = false;

    const terminalCanAnimate = () =>
        terminalRows.length === 8 && room.isConnected &&
        !document.hidden && !reducedMotion.matches && !compactScreen.matches;

    const stopLogTimer = () => {
        if (logTimer !== null) {
            window.clearTimeout(logTimer);
            logTimer = null;
        }
    };

    const paintLog = () => {
        terminalRows.forEach((row, i) => { row.textContent = logHistory[i]; });
    };

    const logStep = () => {
        logTimer = null;
        if (!terminalCanAnimate()) return;

        if (!typing) {
            logHistory.shift();
            logHistory.push('');
            cursor = 0;
            typing = true;
            paintLog();
        }
        const message = demoMessages[messageIndex % demoMessages.length];
        if (cursor < message.length) {
            cursor++;
            terminalRows[terminalRows.length - 1].textContent =
                message.slice(0, cursor) + (cursor < message.length ? '_' : '');
            logTimer = window.setTimeout(logStep, 72);
        } else {
            logHistory[logHistory.length - 1] = message;
            messageIndex++;
            typing = false;
            logTimer = window.setTimeout(logStep, 850);
        }
    };

    const syncTerminal = () => {
        stopLogTimer();
        if (reducedMotion.matches) {
            logHistory = terminalInitial.slice();
            typing = false;
            paintLog();
        } else if (terminalCanAnimate()) {
            logTimer = window.setTimeout(logStep, 580);
        }
    };

    document.addEventListener('visibilitychange', syncTerminal);
    compactScreen.addEventListener('change', syncTerminal);
    window.addEventListener('pagehide', stopLogTimer);
    syncTerminal();

    reducedMotion.addEventListener('change', event => {
        if (event.matches) {
            target.x = 0;
            room.style.removeProperty('--room-x');
        } else {
            requestFrame();
        }
        syncTerminal();
    });
})();

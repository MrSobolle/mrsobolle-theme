/* Browser-native anchors retain keyboard focus, history and reduced motion. */
(() => {
    'use strict';
    document.body.classList.add('is-ready');
    const form = document.querySelector('[data-contact-demo]');
    if (!form) return;
    const button = form.querySelector('[data-demo-submit]');
    button.disabled = false;
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const result = form.querySelector('[data-demo-result]');
        result.hidden = false;
        result.focus();
    });
    form.addEventListener('reset', () => { form.querySelector('[data-demo-result]').hidden = true; });
})();

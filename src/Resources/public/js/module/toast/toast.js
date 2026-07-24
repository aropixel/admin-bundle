/**
 * Toast — floating flash notifications (see css/components/_toast.css). Shown programmatically:
 *
 *   import { showToast } from '…/module/toast/toast.js';
 *   showToast({ type: 'success', title: 'Saved', message: 'Your changes were published.' });
 *
 * app.js exposes it as `window.aroToast(...)`. Bootstrap's `.toast` drives the show / auto-hide;
 * the toast DOM is built here (text via textContent — a caller's message is never markup) and
 * removed once hidden. Toasts stack in a single fixed top-right container.
 *
 * type: success | danger | warning | info | primary   ·   autohide (default true), delay (ms).
 */

const ICONS = {
    success: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12l2 2l4-4"/></g></svg>',
    danger: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></g></svg>',
    warning: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3M12 9v4m0 4h.01"/></svg>',
    info: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></g></svg>',
    primary: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="M6 14a12 12 0 0 0 2.4 7.2a2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14M8 6v8"/></g></svg>',
};

function getContainer() {
    let container = document.querySelector('.aro-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container aro-toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    return container;
}

export function showToast(options = {}) {
    const o = Object.assign({
        type: 'info',
        title: '',
        message: '',
        autohide: true,
        delay: 5000,
    }, options);

    const type = ICONS[o.type] ? o.type : 'info';

    const el = document.createElement('div');
    el.className = 'toast aro-toast aro-toast--' + type;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'polite');
    el.setAttribute('aria-atomic', 'true');
    el.innerHTML =
        '<span class="aro-toast__icon">' + ICONS[type] + '</span>' +
        '<div class="aro-toast__body">' +
            (o.title ? '<div class="aro-toast__title"></div>' : '') +
            (o.message ? '<div class="aro-toast__message"></div>' : '') +
        '</div>' +
        '<button type="button" class="aro-toast__close" data-bs-dismiss="toast" aria-label="Close">&times;</button>';

    if (o.title) {
        el.querySelector('.aro-toast__title').textContent = o.title;
    }
    if (o.message) {
        el.querySelector('.aro-toast__message').textContent = o.message;
    }

    getContainer().appendChild(el);

    const toast = bootstrap.Toast.getOrCreateInstance(el, { autohide: o.autohide, delay: o.delay });
    el.addEventListener('hidden.bs.toast', () => {
        toast.dispose();
        el.remove();
    });
    toast.show();

    return toast;
}

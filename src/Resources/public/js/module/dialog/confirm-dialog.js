/**
 * ConfirmDialog — shows the Dialog design-system component (see css/components/_dialog.css)
 * as a Bootstrap 5 modal, for confirmations previously rendered by the generic ModalDyn.
 *
 * The markup mirrors the catalogued Dialog: an intent icon chip beside the title/message, and
 * a Cancel / confirm footer whose button variant follows the intent. Text is set with
 * `textContent`, never innerHTML, so a caller's message can never inject markup.
 *
 * Usage:
 *   new ConfirmDialog({
 *     intent: 'danger',            // danger | warning | success | info | primary
 *     title: 'Delete this item?',  // bold line (optional if message is set)
 *     message: '',                 // muted detail (optional if title is set)
 *     confirmLabel: 'Delete',
 *     cancelLabel: 'Cancel',
 *     onConfirm: () => form.submit(),
 *   });
 */

import { restoreBodyScrollLock, stackAboveOpenModals } from '/bundles/aropixeladmin/js/utils/modal-stack.js';

const ICONS = {
    danger: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3M12 9v4m0 4h.01"/></svg>',
    success: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12l2 2l4-4"/></g></svg>',
    info: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="currentColor" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></g></svg>',
};
ICONS.warning = ICONS.danger;
ICONS.primary = ICONS.info;

const CONFIRM_CLASS = {
    danger: 'btn-danger',
    warning: 'btn-primary',
    success: 'btn-primary',
    info: 'btn-primary',
    primary: 'btn-primary',
};

export class ConfirmDialog {
    constructor(options = {}) {
        // Translated fallbacks rendered by base.html.twig; safe if absent.
        const i18n = (typeof window !== 'undefined' && window.aroDialogI18n) || {};
        const o = Object.assign({
            intent: 'danger',
            size: 'md',
            title: '',
            message: '',
            confirmLabel: i18n.confirm || 'Confirm',
            cancelLabel: i18n.cancel || 'Cancel',
            onConfirm: () => {},
        }, options);

        // Size scale shared with modals: sm 400 · md 520 (default) · lg 720 · xl 960.
        const sizeClass = { sm: 'modal-sm', lg: 'modal-lg', xl: 'modal-xl' }[o.size] || '';

        // The dialog always shows a title line: fall back to the generic one when none given.
        if (!o.title) {
            o.title = i18n.title || 'Confirmation';
        }

        const intent = ICONS[o.intent] ? o.intent : 'danger';
        const confirmClass = CONFIRM_CLASS[intent] || 'btn-primary';

        const el = document.createElement('div');
        el.className = 'modal fade';
        el.tabIndex = -1;
        el.innerHTML =
            '<div class="modal-dialog modal-dialog-centered ' + sizeClass + '">' +
                '<div class="modal-content aro-dialog aro-dialog--' + intent + '">' +
                    '<div class="modal-body">' +
                        '<div class="aro-dialog__head">' +
                            '<span class="aro-dialog__icon">' + ICONS[intent] + '</span>' +
                            '<div>' +
                                (o.title ? '<h2 class="aro-dialog__title"></h2>' : '') +
                                (o.message ? '<p class="aro-dialog__text"></p>' : '') +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-default" data-bs-dismiss="modal"></button>' +
                        '<button type="button" class="btn ' + confirmClass + '" data-dialog-confirm></button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        // Text via textContent only — a caller's message is never treated as markup.
        if (o.title) {
            el.querySelector('.aro-dialog__title').textContent = o.title;
        }
        if (o.message) {
            el.querySelector('.aro-dialog__text').textContent = o.message;
        }
        el.querySelector('[data-bs-dismiss]').textContent = o.cancelLabel;
        const confirmBtn = el.querySelector('[data-dialog-confirm]');
        confirmBtn.textContent = o.confirmLabel;

        // A confirmation is usually raised from inside another modal, which Bootstrap would
        // render *over* it — see utils/modal-stack.js.
        stackAboveOpenModals(el);

        document.body.appendChild(el);
        const modal = bootstrap.Modal.getOrCreateInstance(el);

        confirmBtn.addEventListener('click', () => {
            modal.hide();
            o.onConfirm();
        });

        // Remove the element from the DOM once the modal has fully closed.
        el.addEventListener('hidden.bs.modal', () => {
            modal.dispose();
            el.remove();
            restoreBodyScrollLock();
        });

        modal.show();

        this.element = el;
        this.modal = modal;
    }
}

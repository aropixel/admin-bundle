/**
 * FileDialog — the file-library counterpart of ImageLightbox: it shows which file is being
 * worked on (its type icon and a link to open it) and turns its header into a text field so
 * the title can be renamed on the spot.
 *
 * A file is not necessarily viewable — a PDF, an archive or a spreadsheet has nothing to
 * show inline — so where the image modal displays the image, this one displays the file's
 * identity and hands the viewing back to the browser through a plain link.
 *
 * Built in JS for the same reason as the lightbox: the rows it is opened from are re-rendered
 * by the DataTable on every reload, and it is raised from inside the file-library modal,
 * hence the stacking helpers.
 *
 * Usage:
 *   new FileDialog({
 *     title: 'Contract',                    // header when no field is shown
 *     icon: '/bundles/…/icons/pdf.svg',     // file-type icon (optional)
 *     url: '/uploads/contract.pdf',         // opens in a new tab (optional)
 *     openLabel: 'Open the file',
 *     edit: { … },                          // optional: see module/dialog/field-editor.js
 *   });
 */

import { fieldMarkup, footerMarkup, mountFieldEditor } from '/bundles/aropixeladmin/js/module/dialog/field-editor.js';
import { restoreBodyScrollLock, stackAboveOpenModals } from '/bundles/aropixeladmin/js/utils/modal-stack.js';

export class FileDialog {
    constructor(options = {}) {
        const { title = '', icon = '', url = '', openLabel = '', edit = null } = options;

        const i18n = (typeof window !== 'undefined' && window.aroDialogI18n) || {};

        const el = document.createElement('div');
        el.className = 'modal fade aro-file-dialog';
        el.tabIndex = -1;
        el.innerHTML =
            '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        // As in the lightbox, the field replaces the title rather than
                        // doubling it.
                        (edit ? fieldMarkup() : '<h5 class="modal-title"></h5>') +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body aro-file-dialog__body">' +
                        (icon ? '<img class="aro-file-dialog__icon" alt="">' : '') +
                        (url ? '<a class="aro-file-dialog__link" target="_blank" rel="noopener"></a>' : '') +
                    '</div>' +
                    (edit ? footerMarkup() : '') +
                '</div>' +
            '</div>';

        // Text and URLs are set as data, never as markup.
        if (!edit) {
            el.querySelector('.modal-title').textContent = title;
        }

        if (icon) {
            el.querySelector('.aro-file-dialog__icon').setAttribute('src', icon);
        }

        if (url) {
            const link = el.querySelector('.aro-file-dialog__link');
            link.setAttribute('href', url);
            link.textContent = openLabel || i18n.openFile || 'Open the file';
        }

        stackAboveOpenModals(el);
        document.body.appendChild(el);

        const modal = bootstrap.Modal.getOrCreateInstance(el);

        if (edit) {
            mountFieldEditor(el, modal, edit, title);
        }

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

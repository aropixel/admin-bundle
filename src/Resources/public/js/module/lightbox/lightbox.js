/**
 * ImageLightbox — shows one image at preview size in a Bootstrap modal, so a thumbnail can
 * be inspected without leaving the page, and optionally turns its header into a text field
 * so the image title can be renamed on the spot.
 *
 * Like ConfirmDialog it is a content pattern layered on Bootstrap's `.modal` (see
 * css/components/_media-library.css for the classes it styles), built in JS because the rows
 * it is opened from are re-rendered by the DataTable on every reload. It is routinely raised
 * from inside the media-library modal, hence the stacking helpers. FileDialog is its
 * counterpart for the file library.
 *
 * Usage:
 *   new ImageLightbox({
 *     src: '/media/cache/admin_preview/…',  // required
 *     title: 'Cover',                       // modal header (optional)
 *     alt: '',                              // falls back to the title
 *     edit: { … },                          // optional: see module/dialog/field-editor.js
 *   });
 */

import { fieldMarkup, footerMarkup, mountFieldEditor } from '/bundles/aropixeladmin/js/module/dialog/field-editor.js';
import { restoreBodyScrollLock, stackAboveOpenModals } from '/bundles/aropixeladmin/js/utils/modal-stack.js';

export class ImageLightbox {
    constructor(options = {}) {
        const { src, title = '', alt = '', edit = null } = options;

        if (!src) return;

        const el = document.createElement('div');
        el.className = 'modal fade aro-lightbox';
        el.tabIndex = -1;
        el.innerHTML =
            '<div class="modal-dialog modal-dialog-centered modal-xl">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        // The field takes the header's place: the title *is* what is edited,
                        // so showing it twice would only invite the two to disagree.
                        (edit ? fieldMarkup() : '<h5 class="modal-title"></h5>') +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body aro-lightbox__body">' +
                        '<img class="aro-lightbox__image" alt="">' +
                    '</div>' +
                    (edit ? footerMarkup() : '') +
                '</div>' +
            '</div>';

        // Text and URLs are set as data, never as markup: a title typed by an admin user is
        // never treated as HTML.
        if (!edit) {
            el.querySelector('.modal-title').textContent = title;
        }

        const image = el.querySelector('.aro-lightbox__image');
        image.setAttribute('src', src);
        image.setAttribute('alt', alt || title);

        stackAboveOpenModals(el);
        document.body.appendChild(el);

        const modal = bootstrap.Modal.getOrCreateInstance(el);

        if (edit) {
            mountFieldEditor(el, modal, edit, title);
        }

        // Single use: the element is dropped once closed, like ConfirmDialog's.
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

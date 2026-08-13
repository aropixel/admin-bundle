/**
 * Single-field editor shared by the media modals that rename an item — the image lightbox
 * and the file dialog. It owns the header field, the Save / Close footer and their wiring:
 * value, placeholder, focus, submit on Enter, and the inline error when a save is refused.
 *
 * Nothing here persists anything: `edit.save` is the caller's promise, so the request and
 * whatever it refreshes stay with the caller.
 *
 * Shape of the `edit` option, common to every host modal:
 *   {
 *     label: 'Title',        // placeholder and accessible name — no visible label fits a header
 *     value: 'Cover',        // initial value; falls back to the host's title
 *     autofocus: true,       // focus and select on open: only when opened *to* edit
 *     save: value => …,      // promise — resolve closes the modal, reject shows the error
 *     error: 'Failed.',      // fallback message when the rejection carries none
 *     saveLabel: 'Save',     // both default to the translated window.aroDialogI18n
 *     closeLabel: 'Close',
 *   }
 */

/** The field itself, to be placed in the host's `.modal-header` in place of its title. */
export function fieldMarkup() {
    return '<div class="aro-field-editor">' +
        '<input type="text" class="form-control">' +
        '<div class="invalid-feedback"></div>' +
    '</div>';
}

/** The footer that goes with it. */
export function footerMarkup() {
    return '<div class="modal-footer">' +
        '<button type="button" class="btn btn-outline" data-bs-dismiss="modal"></button>' +
        '<button type="button" class="btn btn-primary" data-field-editor-save></button>' +
    '</div>';
}

/**
 * Wire the markup above, already inserted in `el`, to `edit`.
 * `fallbackValue` is used when `edit.value` is not given.
 */
export function mountFieldEditor(el, modal, edit, fallbackValue = '') {
    const i18n = (typeof window !== 'undefined' && window.aroDialogI18n) || {};
    const field = el.querySelector('.aro-field-editor input');
    const feedback = el.querySelector('.aro-field-editor .invalid-feedback');
    const saveButton = el.querySelector('[data-field-editor-save]');

    // No visible label in a header: the placeholder names the field, and screen readers get
    // the same wording.
    if (edit.label) {
        field.setAttribute('placeholder', edit.label);
        field.setAttribute('aria-label', edit.label);
    }

    field.value = edit.value !== undefined ? edit.value : fallbackValue;
    saveButton.textContent = edit.saveLabel || i18n.save || 'Save';
    el.querySelector('.modal-footer [data-bs-dismiss]').textContent =
        edit.closeLabel || i18n.close || 'Close';

    // Focus is the caller's call: it belongs to the field only when the modal was opened to
    // edit it. Opened to look at the media, the field must not steal the keyboard.
    if (edit.autofocus) {
        // Value selected, so a rename is a single keystroke away.
        el.addEventListener('shown.bs.modal', () => field.select());
    }

    const save = () => {
        if (typeof edit.save !== 'function') return;

        field.classList.remove('is-invalid');
        saveButton.disabled = true;

        Promise.resolve(edit.save(field.value))
            .then(() => modal.hide())
            .catch(error => {
                feedback.textContent = (error && error.message)
                    || edit.error
                    || i18n.saveFailed
                    || 'Could not be saved.';
                field.classList.add('is-invalid');
                saveButton.disabled = false;
                field.focus();
            });
    };

    saveButton.addEventListener('click', save);

    // Enter submits, as it would in a one-field form.
    field.addEventListener('keydown', event => {
        if (event.key !== 'Enter') return;

        event.preventDefault();
        save();
    });
}

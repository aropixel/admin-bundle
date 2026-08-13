/**
 * Stacked-modal helpers.
 *
 * Bootstrap 5 has no support for a modal opened on top of another one: every `.modal` shares
 * a single z-index and every backdrop another, and closing any of them clears `modal-open`
 * from the body. The admin does stack them — a confirmation or an image preview is raised
 * from the media library, which lifts itself to z-index 9996 (image-manager/modal.js) or
 * 1080 (files.js). Both helpers below are no-ops when nothing else is open, so a modal shown
 * on a plain page keeps Bootstrap's own behaviour.
 */

/**
 * Raise `el` — and, once shown, its backdrop — above every modal currently open.
 * Call it before `show()`, while `el` is still hidden.
 */
export function stackAboveOpenModals(el) {
    const openZIndex = Math.max(0, ...Array.from(document.querySelectorAll('.modal.show'))
        .map(modal => parseInt(window.getComputedStyle(modal).zIndex, 10) || 0));

    if (!openZIndex) return;

    el.style.zIndex = String(openZIndex + 20);

    // The backdrop does not exist until shown, and ours is the last one appended.
    el.addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const backdrop = backdrops[backdrops.length - 1];
        if (backdrop) backdrop.style.zIndex = String(openZIndex + 10);
    });
}

/**
 * Re-lock the page scroll once a stacked modal has closed: Bootstrap removes `modal-open`
 * from the body even though the modal underneath is still open.
 */
export function restoreBodyScrollLock() {
    if (document.querySelector('.modal.show')) {
        document.body.classList.add('modal-open');
    }
}

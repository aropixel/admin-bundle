// launcher.js
import { IM_Widget } from '/bundles/aropixeladmin/js/module/image-manager/widget.js';
import { IM_Gallery } from '/bundles/aropixeladmin/js/module/image-manager/gallery.js';
import { IM_Editor } from '/bundles/aropixeladmin/js/module/image-manager/editor.js';

/**
 * Initialise un gestionnaire d'image sur un élément DOM
 */
export function initImageManager(element, options = {}) {
    const config = {
        type: 'image',
        multiple: false,
        ...element.dataset,
        ...options,
    };

    const launcher = { element, config };

    if (config.imType === 'editor' && options.editor) {
        config.imType = 'editor';
        config.imLibrary = options.category;
        config.imAttachClass = options.category;
        config.imAttachEditor = options.attach_path;
        launcher.editor = new IM_Editor(launcher, options.editor);
    } else if (config.imType === 'gallery') {
        launcher.gallery = new IM_Gallery(launcher);
    } else {
        launcher.widget = new IM_Widget(launcher);
    }

    element.__imLauncher = launcher;

    return launcher;
}

document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-im-library="modal"]');
    if (!trigger) return;

    e.preventDefault();

    const modal = document.getElementById('modalLibrary');
    if (!modal) return;

    // Stocke le déclencheur
    modal.__relatedTarget = trigger;

    // Rebind proprement l'instance et appel explicite à ta logique
    const instance = bootstrap.Modal.getOrCreateInstance(modal);
    instance.show();

    // Appelle directement ta logique au lieu d'attendre show.bs.modal
    if (window.imLibrary?.modal?.onShow) {
        window.imLibrary.modal.onShow({ target: modal });
    }
});

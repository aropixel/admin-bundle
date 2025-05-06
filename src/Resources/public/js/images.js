import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';
import { initImageManager } from '/bundles/aropixeladmin/js/module/image-manager/launcher.js';
import { IM_Library } from '/bundles/aropixeladmin/js/module/image-manager/library.js';

let imLibrary = null;

function initializeImageManager() {
    // (Ré)initialise les widgets .im-manager de la page
    document.querySelectorAll('.im-manager').forEach((el) => {
        initImageManager(el);
    });

    // Initialise la bibliothèque (modale + uploader)
    if (!imLibrary) {
        imLibrary = new IM_Library();
        window.imLibrary = imLibrary; // pour accès dans launcher.js
    } else {
        imLibrary.modal.init(); // rebind la modale (DOM peut être remplacé par Turbo)
    }
}

// Support de Turbo (si actif)
document.addEventListener('turbo:load', initializeImageManager);

// Fallback pour projet sans Turbo (chargement initial)
document.addEventListener('DOMContentLoaded', initializeImageManager);

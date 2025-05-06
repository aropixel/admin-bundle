// core.js
import { IM_Modal } from '/bundles/aropixeladmin/js/module/image-manager/modal.js';
import { IM_Uploader } from '/bundles/aropixeladmin/js/module/image-manager/uploader.js';

export class IM_Library {
    constructor() {
        this.modal = new IM_Modal();
        this.uploader = new IM_Uploader('#modalLibrary .image-uploader', () => {
            this.modal?.loadPictures?.(); // appeler rechargement s'il existe
        });
    }
}

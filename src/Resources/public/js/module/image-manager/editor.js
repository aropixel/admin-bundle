import { showModal,hideModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';

export class IM_Editor {
    constructor(launcher, editorInstance) {
        this.launcher = launcher;
        this.editor = editorInstance;
        this.modal = document.querySelector('#modalLibrary');
        this.attachButton = this.modal.querySelector('.attach-images');

        this.init();
        window.imLibrary.modal.setLauncher(launcher);
        showModal('#modalLibrary');
    }

    init() {
    }

    insertImage() {
        this.attachButton.innerHTML = '<i class="icon-spinner2 spinner position-left"></i> Création des vignettes';
        this.attachButton.disabled = true;

        const selectedImages = [...this.modal.querySelectorAll('input[type="checkbox"][name^="image"]:checked')]
            .map(cb => cb.value);

        const width = this.modal.querySelector('#img_size')?.value;
        const filter = this.modal.querySelector('#img_filter')?.value;
        const alt = this.modal.querySelector('#img_alt')?.value;

        const params = {
            images: selectedImages,
            width,
            filter,
            alt
        };

        const attachParams = this.launcher.config.attach_params;
        if (typeof attachParams === 'function') {
            Object.assign(params, attachParams());
        } else if (attachParams) {
            Object.assign(params, attachParams);
        }

        fetch(this.launcher.config.imAttachEditor, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(params)
        })
            .then(res => res.text())
            .then(html => {
                if (typeof this.editor.insertHtml === 'function') {
                    this.editor.insertHtml(html);
                    this.editor.focusManager?.focus();
                } else if (this.editor.constructor.name === 'Quill') {
                    const range = this.editor.getSelection(true);
                    this.editor.clipboard.dangerouslyPasteHTML(range.index, html);
                }

                hideModal('#modalLibrary');
                this.attachButton.innerHTML = "Ajouter l'image";
                this.attachButton.disabled = false;
            });
    }
}

// widget.js
import { hideModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';
import { IM_Cropper } from '/bundles/aropixeladmin/js/module/image-manager/cropper.js';

export class IM_Widget {
    constructor(launcher) {
        this.launcher = launcher;
        this.element = launcher.element;
        this.init();
    }

    init() {
        const unlinkButton = this.element.querySelector('.btnUnlink');
        if (unlinkButton) {
            unlinkButton.addEventListener('click', () => this.detach());
        }

        new IM_Cropper(this.launcher);
    }

    attach() {
        const button = document.querySelector('#modalLibrary .attach-images');
        if (!button) return;

        button.innerHTML = '<i class="icon-spinner2 spinner position-left"></i> Création des vignettes';
        button.disabled = true;

        const modal = document.querySelector('#modalLibrary');
        const checkboxes = modal.querySelectorAll('input[type="checkbox"][name^="image"]:checked');
        const images = Array.from(checkboxes).map(cb => cb.value);

        const size = document.querySelector('#img_size')?.value;
        const filter = document.querySelector('#img_filter')?.value;
        const alt = document.querySelector('#img_alt')?.value;

        const thumb = this.element.querySelector('.thumbnail');
        const data = thumb?.dataset || {};

        const params = {
            route: this.launcher.config.imRoute,
            multiple: '0',
            images,
            data_type: this.launcher.config.imDataType,
            attach_id: data.imAttachId,
            attach_class: data.imAttachClass,
            attach_value: data.imAttachValue,
            entity_class: this.launcher.config.imEntityClass,
            crops_slugs: data.imCropsSlugs,
            crops_labels: data.imCropsLabels,
            width: size,
            filter,
            alt,
        };

        fetch(data.imAttachPath, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(params),
        })
            .then(res => res.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;

                const newImg = temp.querySelector('.preview > img');
                const currentPreview = this.element.querySelector('.preview');
                const oldImg = currentPreview.querySelector('img');

                if (oldImg) oldImg.replaceWith(newImg);
                else currentPreview.querySelector('.no-img')?.replaceWith(newImg);

                const newCaption = temp.querySelector('.caption');
                if (newCaption) this.element.querySelector('.caption').innerHTML = newCaption.innerHTML;

                const fileInput = temp.querySelector("input[name$='[file_name]']");
                const imageInput = temp.querySelector("input[name$='[image]']");
                if (fileInput) this.element.querySelector("input[name$='[file_name]']").value = fileInput.value;
                if (imageInput) this.element.querySelector("input[name$='[image]']").value = imageInput.value;

                // z-index, modals, actions
                this.element.querySelector('.image-actions .btnUnlink')?.remove();
                this.element.querySelector('.image-actions .iconCrop')?.remove();
                this.element.querySelector('.image-actions .btnUpload')?.insertAdjacentElement(
                    'afterend', temp.querySelector('.image-actions .btnUnlink')
                );

                hideModal('#modalLibrary');
                button.innerHTML = 'Sélectionner une image';
                button.disabled = false;
            });
    }

    detach() {
        // à compléter avec la logique de confirmation/suppression
        const preview = this.element.querySelector('.preview');
        if (!preview) return;

        preview.innerHTML = preview.dataset.new || '';
        const actions = this.element.querySelector('.image-actions');
        actions?.classList.add('visually-hidden');

        ['image', 'title', 'alt', 'file_name'].forEach(field => {
            const input = this.element.querySelector(`input[name$='[${field}]']`);
            if (input) input.removeAttribute('value');
        });
    }
}

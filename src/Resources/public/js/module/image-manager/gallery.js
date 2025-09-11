// gallery.js
import { hideModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';
import { IM_Gallery_Widget } from '/bundles/aropixeladmin/js/module/image-manager/gallery-widget.js';

export class IM_Gallery {
    constructor(launcher) {
        this.launcher = launcher;
        this.container = launcher.element.querySelector('.galleryContent');

        this.init();
    }

    init() {
        const thumbnails = this.launcher.element.querySelectorAll('.thumbnail');
        thumbnails.forEach(thumb => new IM_Gallery_Widget(this, thumb));
        this.makeSortable();
    }

    attach() {
        const modal = document.querySelector('#modalLibrary');
        const checkboxes = modal.querySelectorAll('input[type="checkbox"][name^="image"]:checked');
        const galleryContent = this.container;

        checkboxes.forEach((checkbox, index) => {
            const prototype = galleryContent.dataset.prototype;
            const currentIndex = galleryContent.children.length;
            const html = prototype.replace(/__name__/g, currentIndex);

            const div = document.createElement('div');
            div.innerHTML = html;
            const newItem = div.firstElementChild;

            // Set image ID or filename
            const imgPreview = checkbox.closest('tr').querySelector('.img-preview');
            const img = document.createElement('img');
            img.src = imgPreview.src;
            newItem.querySelector('.no-img')?.replaceWith(img);

            const imageIdInput = newItem.querySelector("[name$='[image]']");
            if (imageIdInput) imageIdInput.value = checkbox.value;

            const fileNameInput = newItem.querySelector("[name$='[file_name]']");
            if (fileNameInput) fileNameInput.value = img.src.split('/').pop();

            galleryContent.appendChild(newItem);
            const galleryWidget = new IM_Gallery_Widget(this, newItem.querySelector('.thumbnail'));
            newItem.querySelector('.thumbnail').__imGalleryWidget = galleryWidget;

            hideModal('#modalLibrary');
        });
    }

    makeSortable() {
        // Vous pouvez utiliser SortableJS ici si nécessaire
        // Sinon, on garde une logique simple à l'aide de drag & drop natif ou à compléter
    }

    reIndex() {
        const thumbnails = this.container.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, i) => {
            const inputs = thumb.querySelectorAll('input[name]');
            inputs.forEach(input => {
                input.name = input.name.replace(/\[[0-9]+\]/, `[${i}]`);
            });
        });
    }
}

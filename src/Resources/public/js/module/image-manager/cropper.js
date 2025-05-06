export class IM_Cropper {
    constructor(launcher) {
        this.launcher = launcher;
        this.modal = launcher.element.querySelector('.modalCrop');
        this.initialized = false;

        this.setup();
    }

    setup() {
        if (!this.modal || this.initialized) return;

        this.modal.addEventListener('shown.bs.modal', (event) => {
            const cropButton = event.relatedTarget;
            const thumb = cropButton?.closest('.image-widget')?.querySelector('.thumbnail');
            const imgId = thumb?.dataset.imImageId;
            const imgSrc = thumb?.dataset.imCropPath;

            const imageContainer = this.modal.querySelector('#crop_zone');
            imageContainer.innerHTML = '';

            const img = document.createElement('img');
            img.src = imgSrc;
            img.dataset.id = imgId;
            img.style.width = '600px';
            imageContainer.appendChild(img);

            const defaultRatio = this.modal.querySelector('#crop_options input[type="radio"]:checked');
            const options = this.getCropperOptions(defaultRatio);
            new Cropper(img, options);

            this.modal.querySelectorAll('#crop_options input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const newOptions = this.getCropperOptions(e.target);
                    img.cropper?.destroy();
                    new Cropper(img, newOptions);
                });
            });

            this.modal.querySelector('.crop-file')?.addEventListener('click', () => {
                this.saveCrops();
            });
        });

        this.initialized = true;
    }

    getCropperOptions(button) {
        const cropData = button?.dataset.crop;
        const ratio = parseFloat(button?.dataset.ratio) || 1;
        const data = cropData ? cropData.split(',').map(Number) : null;

        return {
            viewMode: 2,
            aspectRatio: ratio,
            autoCropArea: 1,
            zoomable: false,
            data: data ? {
                x: data[0],
                y: data[1],
                width: data[2],
                height: data[3]
            } : undefined,
            crop: (event) => {
                const rect = event.detail;
                button.dataset.crop = `${rect.x},${rect.y},${rect.width},${rect.height}`;
            }
        };
    }

    saveCrops() {
        const thumb = this.modal.querySelector('#crop_zone').dataset.thumb;
        const collection = document.querySelector(`#${thumb}_crops`);
        const prototype = collection.dataset.prototype;
        collection.innerHTML = '';

        this.modal.querySelectorAll('#crop_options input[type="radio"]').forEach((radio, index) => {
            const newForm = prototype.replace(/__name__/g, index);
            const container = document.createElement('div');
            container.innerHTML = newForm;

            container.querySelector('[name$="[filter]"]').value = radio.value;
            container.querySelector('[name$="[crop]"]').value = radio.dataset.crop;

            collection.appendChild(container);
        });

        this.modal.classList.remove('show');
    }
}

export class IM_Cropper {
    constructor(launcher) {
        this.launcher = launcher;
        this.modal = launcher.element.querySelector('.modalCrop');
        this.thumb = false;
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
            $('#crop_zone > img').cropper('destroy').attr('src', imgSrc).cropper(options);

            this.modal.querySelectorAll('#crop_options input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const newOptions = this.getCropperOptions(e.target);
                    $('#crop_zone > img').cropper('destroy').cropper(newOptions);
                });
            });

            this.modal.querySelector('.crop-file')?.addEventListener('click', () => {
                this.saveCrops(thumb);
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
            minContainerWidth: 200,
            minContainerHeight: 200,
            data: data ? {
                x: data[0],
                y: data[1],
                width: data[2],
                height: data[3]
            } : undefined,
            crop: (data) => {
                button.dataset.crop = `${data.x},${data.y},${data.width},${data.height}`;
            }
        };
    }

    saveCrops(thumb) {
        const collection = thumb.querySelector(`[id$="_crops"]`);
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

        const modal = document.querySelector('.modalCrop');
        const instance = bootstrap.Modal.getOrCreateInstance(modal);
        instance.hide();
    }
}

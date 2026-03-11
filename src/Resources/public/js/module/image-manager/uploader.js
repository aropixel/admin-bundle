export class IM_Uploader {
    constructor(buttonSelector, modal = null) {
        this.button = document.querySelector(buttonSelector);
        this.progress = this.button?.nextElementSibling;
        this.dataTable = document.querySelector('#libraryDataTable');
        this.modal = modal;
        this.category = this.button?.dataset.category;

        if (this.button) {
            this.initUploader(() => { this.modal?.loadPictures?.() });
        }
    }

    initUploader(onComplete) {
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = 'image/jpeg,image/png,image/gif';
        input.style.display = 'none';
        document.body.appendChild(input);

        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            input.click();
        });

        input.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            if (!files.length) return;

            this.clearErrors();

            for (const file of files) {
                await this.uploadFile(file, onComplete);
            }

            input.value = ''; // Reset input for next selection
        });
    }

    async uploadFile(file, onComplete) {
        const formData = new FormData();
        const category = this.modal?.getCategory() || this.category || '';

        formData.append('aropixel_admin_library_image[file]', file);
        formData.append('aropixel_admin_library_image[category]', category);
        formData.append('aropixel_admin_library_image[title]', file.name);
        formData.append('_http_accept', 'application/javascript');

        const fileId = 'file-' + Math.random().toString(36).substring(2);
        this.progress.insertAdjacentHTML('beforeend', `<li id="${fileId}" class="width-200">
            <div class="info">${file.name}</div>
            <div class="progress"><div class="progress-bar" style="width: 0;"></div></div>
          </li>`);

        const progressBar = document.querySelector(`#${fileId} .progress-bar`);

        try {
            const response = await this.xhrUpload(this.button.dataset.path, formData, (percent) => {
                if (progressBar) progressBar.style.width = `${percent}%`;
            });

            if (response.status >= 200 && response.status < 300) {
                // Succès
                const listItem = document.getElementById(fileId);
                if (listItem) listItem.remove();

                if (this.progress.children.length === 0) {
                    this.progress.innerHTML = '';
                    if (typeof onComplete === 'function') onComplete();
                }
            } else {
                throw new Error(response.responseText || 'Upload failed');
            }

        } catch (error) {
            const alert = document.getElementById('alertUploadError');
            if (alert) {
                alert.innerHTML = error.message;
                alert.style.display = 'block';
            }
        }
    }

    xhrUpload(url, formData, onProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    onProgress(percent);
                }
            });

            xhr.onload = () => resolve({
                status: xhr.status,
                responseText: xhr.responseText
            });

            xhr.onerror = () => reject(new Error('Network error'));
            xhr.send(formData);
        });
    }

    clearErrors() {
        const alert = document.getElementById('alertUploadError');
        if (alert) {
            alert.innerHTML = '';
            alert.style.display = 'none';
        }
    }
}

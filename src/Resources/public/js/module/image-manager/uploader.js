export class IM_Uploader {
    constructor(buttonSelector, onComplete = null) {
        this.button = document.querySelector(buttonSelector);
        this.progress = this.button?.nextElementSibling;
        this.dataTable = document.querySelector('#libraryDataTable');
        this.category = this.button?.dataset.category;

        if (this.button) {
            this.initUploader(onComplete);
        }
    }

    initUploader(onComplete) {
        const id = 'upload-' + Math.random().toString(36).substring(2);
        this.button.id = id;

        this.uploader = new plupload.Uploader({
            browse_button: id,
            url: this.button.dataset.path,
            file_data_name: 'plupload_image[file]',
            multipart_params: {
                '_http_accept': 'application/javascript'
            },
            filters: [
                { title: 'Images', extensions: 'jpg,gif,png,jpeg' }
            ],
            init: {
                FilesAdded: (up, files) => {
                    this.clearErrors();
                    this.uploader.start();
                },
                BeforeUpload: (up, file) => {
                    up.settings.multipart_params = {
                        'plupload_image[category]': this.category,
                        'plupload_image[title]': file.name
                    };
                },
                UploadFile: (up, file) => {
                    this.progress.innerHTML = `<li id="${file.id}" class="width-200">
            <div class="info">${file.name}</div>
            <div class="progress"><div class="progress-bar" style="width: 0;"></div></div>
          </li>`;
                },
                UploadProgress: (up, file) => {
                    const bar = document.querySelector(`#${file.id} .progress-bar`);
                    if (bar) bar.style.width = `${file.percent}%`;
                },
                UploadComplete: () => {
                    this.progress.innerHTML = '';
                    if (typeof onComplete === 'function') onComplete();
                },
                Error: (up, err) => {
                    const alert = document.getElementById('alertUploadError');
                    if (alert) {
                        alert.innerHTML = err.response;
                        alert.style.display = 'block';
                    }
                },
                FileUploaded: () => {
                    // Reload table or preview
                }
            }
        });

        this.uploader.init();
    }

    clearErrors() {
        const alert = document.getElementById('alertUploadError');
        if (alert) {
            alert.innerHTML = '';
            alert.style.display = 'none';
        }
    }
}

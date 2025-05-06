// modal.js
import { hideModal, showModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';

export class IM_Modal {
    constructor() {
        this._initialized = false;
        this.launcher = null;
        this.modal = document.querySelector('#modalLibrary');
        if (!this.modal) return;

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();

        this.attachButton = this.modal.querySelector('.attach-images');
        this.checkboxes = this.modal.querySelectorAll('input[type="checkbox"][name^="image"]');

        this.init();
    }

    init() {
        console.log('init modal');

        this.modal.removeEventListener('show.bs.modal', this._boundOnShow);
        this.modal.removeEventListener('hide.bs.modal', this._boundOnHide);
        this.attachButton.removeEventListener('click', this._boundValidate);

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();

        this.modal.addEventListener('show.bs.modal', this._boundOnShow);
        this.modal.addEventListener('hide.bs.modal', this._boundOnHide);
        this.attachButton.addEventListener('click', () => this._boundValidate);
    }

    setLauncher(launcher) {
        this.launcher = launcher;
    }

    onShow(event) {
        console.log('on show modal');
        const button = this.modal.__relatedTarget;
        const root = button?.closest('[data-im-type]');
        console.log(button);
        console.log(root);

        this.launcher = root?.__imLauncher;
        console.log(this.launcher);
        if (!this.launcher) return;

        this.modal.style.zIndex = 9996;
        this.checkboxes.forEach(cb => (cb.checked = false));

        const library = document.querySelector('#library_container');
        const settings = document.querySelector('#image_options');

        if (this.launcher?.config.imType === 'editor') {
            library.classList.remove('col-md-12');
            library.classList.add('col-md-8');
            settings.style.display = '';
        } else {
            library.classList.remove('col-md-8');
            library.classList.add('col-md-12');
            settings.style.display = 'none';
        }

        this.loadPictures();
    }

    onHide() {
        document.getElementById('alertUploadError')?.classList.add('hidden');
    }

    validate() {
        const checked = this.modal.querySelectorAll('input[type="checkbox"][name^="image"]:checked');
        if (!checked.length) {
            document.getElementById('alertNoImg')?.classList.remove('hidden');
        } else {
            this.validPictures();
        }
    }

    validPictures() {
        if (!this.launcher) return;

        if (this.launcher.config.imType === 'editor') {
            this.launcher.editor.insertImage();
        } else if (this.launcher.config.imType === 'gallery') {
            this.launcher.gallery.attach();
        } else {
            this.launcher.widget.attach();
        }
    }

    loadPictures() {
        const dataTable = this.modal.querySelector('#libraryDataTable');
        const src = dataTable?.getAttribute('data-src');
        const library = this.launcher?.element?.dataset?.imLibrary;

        if (!dataTable || !src || !library) return;

        const url = src + '?category=' + library;

        let _params = {
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": $.fn.dataTable.pipeline( {
                url: encodeURI(url),
                pages: 5 // number of pages to cache
            } )
        };

        const that = this;

        $('#libraryDataTable').DataTable().clearPipeline().destroy();
        $('#libraryDataTable')
            .on( 'init.dt', function () {

                // Add placeholder to the datatable filter option
                document.querySelector('.dataTables_filter input[type=search]').setAttribute('placeholder', 'Taper pour filtrer...');

                // Enable Select2 select for the length option
                $('.dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });

                that.modal.querySelectorAll('input[type="checkbox"][name^="image"]').forEach(checkbox => {
                    checkbox.addEventListener('click', e => {
                        if (that.launcher?.config.imType === 'image') {
                            that.modal.querySelectorAll('input[type="checkbox"][name^="image"]').forEach(cb => {
                                if (cb !== e.target) cb.checked = false;
                            });
                        }
                    });
                });
            } )
            .dataTable(_params);


    }
}

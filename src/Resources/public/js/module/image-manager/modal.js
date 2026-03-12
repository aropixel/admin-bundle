// modal.js
import { hideModal, showModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';

export class IM_Modal {
    constructor() {
        this._initialized = false;
        this.launcher = null;

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();

        this.init();
    }

    loadElements() {
        this.modal = document.querySelector('#modalLibrary');
        if (!this.modal) return;

        this.attachButton = this.modal.querySelector('.attach-images');
        this.checkboxes = this.modal.querySelectorAll('input[type="checkbox"][name^="image"]');
    }

    init() {
        this.loadElements();
        if (!this.modal) return;

        this.modal.removeEventListener('show.bs.modal', this._boundOnShow);
        this.modal.removeEventListener('hide.bs.modal', this._boundOnHide);
        this.attachButton.removeEventListener('click', this._boundValidate);

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();

        this.modal.addEventListener('show.bs.modal', this._boundOnShow);
        this.modal.addEventListener('hide.bs.modal', this._boundOnHide);
        this.attachButton.addEventListener('click', this._boundValidate);
    }

    setLauncher(launcher) {
        this.launcher = launcher;
    }

    onShow(event) {
        this.loadElements();

        if (!this.modal) return;
        const button = event.relatedTarget;
        const root = button?.closest('[data-im-type]');

        if (!this.launcher) {
            this.launcher = root?.__imLauncher;
        }

        if (root) {
            const uploaderButton = this.modal.querySelector('.image-uploader');
            if (uploaderButton) {
                const accept = root.dataset.imAccept || root.dataset.flAccept;
                if (accept) {
                    uploaderButton.dataset.accept = accept;
                } else {
                    delete uploaderButton.dataset.accept;
                }
            }
        }

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

        // Modifier le texte du bouton selon le mode
        if (this.modal.__isEditMode) {
            this.attachButton.textContent = 'Remplacer l\'image';
        } else {
            this.attachButton.textContent = 'Sélectionner une image';
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
            // Vérifier si on est en mode édition
            if (this.modal.__isEditMode && this.modal.__editingWidget) {
                this.replaceGalleryImage();
            } else {
                this.launcher.gallery.attach();
            }
        } else {
            this.launcher.widget.attach();
        }
    }

    replaceGalleryImage() {
        const modal = document.querySelector('#modalLibrary');
        const checkbox = modal.querySelector('input[type="checkbox"][name^="image"]:checked');
        const editingWidget = modal.__editingWidget;

        if (!checkbox || !editingWidget) return;

        // Récupérer les informations de la nouvelle image
        const imgPreview = checkbox.closest('tr').querySelector('.img-preview');
        const newImageData = {
            id: checkbox.value,
            src: imgPreview.src,
            filename: imgPreview.src.split('/').pop()
        };

        // Trouver le widget correspondant dans la galerie et le remplacer
        const galleryWidgets = this.launcher.element.querySelectorAll('.thumbnail');
        for (const widget of galleryWidgets) {
            if (widget === editingWidget) {
                // Créer une instance de IM_Gallery_Widget pour ce widget
                const galleryWidget = widget.__imGalleryWidget;
                if (galleryWidget && galleryWidget.replaceImage) {
                    galleryWidget.replaceImage(newImageData);
                } else {
                    // Fallback si l'instance n'existe pas
                    this.directReplaceImage(widget, newImageData);
                }
                break;
            }
        }

        hideModal('#modalLibrary');
    }

    directReplaceImage(widget, newImageData) {
        // Remplacer directement l'image dans le DOM
        const preview = widget.querySelector('.preview img');
        if (preview && newImageData.src) {
            preview.src = newImageData.src;
        }

        const imageIdInput = widget.querySelector("[name$='[image]']");
        if (imageIdInput && newImageData.id) {
            imageIdInput.value = newImageData.id;
        }

        const fileNameInput = widget.querySelector("[name$='[file_name]']");
        if (fileNameInput && newImageData.filename) {
            fileNameInput.value = newImageData.filename;
        }
    }


    getCategory() {
        return this.launcher.config.imLibrary ?
            this.launcher.config.imLibrary :
            this.launcher?.element?.dataset?.imLibrary;
    }

    loadPictures() {
        const dataTable = this.modal.querySelector('#libraryDataTable');
        const src = dataTable?.getAttribute('data-src');
        const library = this.getCategory();

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

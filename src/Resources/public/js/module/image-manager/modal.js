// modal.js
import { hideModal, showModal } from '/bundles/aropixeladmin/js/module/image-manager/ui.js';
import { ConfirmDialog } from '/bundles/aropixeladmin/js/module/dialog/confirm-dialog.js';
import { ImageLightbox } from '/bundles/aropixeladmin/js/module/lightbox/lightbox.js';

export class IM_Modal {
    constructor() {
        this._initialized = false;
        this.launcher = null;

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();
        this._boundOnDelete = (e) => this.onDelete(e);
        this._boundOnPreview = (e) => this.onPreview(e);

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
        this.modal.removeEventListener('click', this._boundOnDelete);
        this.modal.removeEventListener('click', this._boundOnPreview);

        this._boundOnShow = (e) => this.onShow(e);
        this._boundOnHide = () => this.onHide();
        this._boundValidate = () => this.validate();
        this._boundOnDelete = (e) => this.onDelete(e);
        this._boundOnPreview = (e) => this.onPreview(e);

        this.modal.addEventListener('show.bs.modal', this._boundOnShow);
        this.modal.addEventListener('hide.bs.modal', this._boundOnHide);
        this.attachButton.addEventListener('click', this._boundValidate);
        // délégation : les lignes du datatable sont réécrites à chaque rechargement
        this.modal.addEventListener('click', this._boundOnDelete);
        this.modal.addEventListener('click', this._boundOnPreview);
    }

    setLauncher(launcher) {
        this.launcher = launcher;
    }

    onShow(event) {
        this.loadElements();

        if (!this.modal) return;

        const button = event.relatedTarget || this.modal.__relatedTarget;
        const root = button?.closest('[data-im-type]');

        if (root) {
            this.launcher = root.__imLauncher;
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

                const maxSize = root.dataset.imMaxSize || root.dataset.flMaxSize;
                if (maxSize) {
                    uploaderButton.dataset.maxSize = maxSize;
                } else {
                    delete uploaderButton.dataset.maxSize;
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
        this.launcher = null;
        this.clearError();
        this.clearNoImageAlert();
    }

    onDelete(event) {
        const button = event.target.closest('[data-library="delete"]');
        if (!button) return;

        event.preventDefault();

        const i18n = window.aroDialogI18n || {};

        new ConfirmDialog({
            intent: 'danger',
            title: i18n.deleteLibraryImage || 'Remove this image from the library?',
            message: i18n.deleteDetail || '',
            onConfirm: () => this.deleteImage(button),
        });
    }

    // L'aperçu s'ouvre depuis la vignette comme depuis le nom : c'est aussi là que le nom
    // se modifie, l'édition en ligne (x-editable) n'existant plus.
    onPreview(event) {
        const trigger = event.target.closest('[data-library="preview"], [data-library="title"]');
        if (!trigger) return;

        // Sans JS la vignette ouvre l'aperçu en quittant la page : on garde l'image sur place.
        event.preventDefault();

        const row = trigger.closest('tr');
        if (!row) return;

        const link = row.querySelector('[data-library="preview"]');
        const titleCell = row.querySelector('[data-library="title"]');

        if (!link) return;

        const i18n = window.aroDialogI18n || {};
        const title = titleCell?.textContent.trim() || '';

        new ImageLightbox({
            src: link.getAttribute('href'),
            title: title,
            alt: link.querySelector('img')?.getAttribute('alt') || '',
            edit: titleCell && titleCell.dataset.path ? {
                label: i18n.mediaTitle || 'Title',
                value: title,
                // Ouvert depuis le nom, on vient renommer : le champ prend le clavier.
                // Ouvert depuis la vignette, on vient regarder l'image.
                autofocus: trigger === titleCell,
                error: i18n.titleSaveFailed || 'The title could not be saved.',
                save: value => this.saveTitle(titleCell, value),
            } : null,
        });
    }

    saveTitle(titleCell, value) {
        return fetch(titleCell.dataset.path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({ pk: titleCell.dataset.id, value }),
        }).then(response => {
            if (!response.ok) throw new Error();

            // La cellule est mise à jour sur place : recharger le datatable ferait perdre
            // la page courante et la recherche en cours.
            titleCell.textContent = value;
        });
    }

    deleteImage(button) {
        const path = button.dataset.path;
        const id = button.dataset.id;

        if (!path || !id) return;

        const i18n = window.aroDialogI18n || {};
        const failed = i18n.deleteImageFailed || 'The image could not be deleted.';

        fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({ image_id: id }),
        })
            .then(response => response.text())
            .then(answer => {
                const status = answer.trim();

                if (status === 'OK') {
                    this.clearError();
                    this.loadPictures();

                    return;
                }

                this.showError(status === 'FOREIGN_KEY'
                    ? (i18n.deleteImageInUse || 'This image is used in one or more contents.')
                    : failed);
            })
            .catch(() => this.showError(failed));
    }

    showError(message) {
        const alert = document.getElementById('alertUploadError');
        const messageElement = document.getElementById('alertUploadErrorMessage');

        if (alert && messageElement) {
            messageElement.textContent = message;
            alert.style.display = 'block';
        } else {
            console.error(message);
        }
    }

    clearError() {
        const alert = document.getElementById('alertUploadError');
        if (alert) alert.style.display = 'none';
    }

    showNoImageAlert() {
        const alert = document.getElementById('alertNoImg');
        if (alert) alert.style.display = 'block';
    }

    clearNoImageAlert() {
        const alert = document.getElementById('alertNoImg');
        if (alert) alert.style.display = 'none';
    }

    validate() {
        const checked = this.modal.querySelectorAll('input[type="checkbox"][name^="image"]:checked');
        if (!checked.length) {
            this.showNoImageAlert();
        } else {
            this.clearNoImageAlert();
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

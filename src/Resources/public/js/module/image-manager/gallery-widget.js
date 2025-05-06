// gallery-widget.js
import { ModalDyn } from '/bundles/aropixeladmin/js/module/modal-dyn/modal-dyn.js';

export class IM_Gallery_Widget {
    constructor(gallery, widget) {
        this.gallery = gallery;
        this.launcher = gallery.launcher;
        this.widget = widget;

        this.init();
    }

    init() {
        const unlink = this.widget.querySelector('.btnUnlink');
        if (unlink) unlink.addEventListener('click', () => this.detach());

        const edit = this.widget.querySelector('.iconEdit');
        if (edit) edit.addEventListener('click', () => this.edit());
    }

    detach() {
        const modal = new ModalDyn("Supprimer", "Voulez-vous supprimer l'image de la galerie ?", {
            "Fermer": modal => modal.hide(),
            "Supprimer": {
                class: 'btn-danger',
                callback: modal => {
                    this.widget.parentElement.remove();
                    this.gallery.reIndex();
                    modal.hide();
                }
            }
        }, { modalClass: 'modal_mini', headerClass: 'bg-danger' });
    }

    edit() {
        // Implémentation simplifiée, requiert adaptation
        const imageId = this.widget.querySelector('[name^="attach_edit"]')?.value;
        const newId = this.widget.querySelector('[name^="attach_new"]')?.value;
        const url = imageId
            ? Routing.generate('gallery_image_infos_edit', { id: imageId })
            : Routing.generate('gallery_image_infos_new', { id: newId });

        fetch(url)
            .then(res => res.text())
            .then(content => {
                new ModalDyn("Modifier l'image", content, {
                    "Fermer": modal => modal.hide(),
                    "Modifier": {
                        class: 'btn-primary',
                        callback: modal => {
                            const form = modal.element.querySelector('form');
                            fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                            })
                                .then(res => res.text())
                                .then(info => {
                                    // Met à jour les infos dans le widget
                                    const temp = document.createElement('div');
                                    temp.innerHTML = info;

                                    const newInfos = temp.querySelector('[name^="attach_infos"]');
                                    if (newInfos) {
                                        this.widget.querySelector('[name^="attach_infos"]')?.remove();
                                        this.widget.querySelector('.preview')?.appendChild(newInfos);
                                    }

                                    const newCaption = temp.querySelector('.caption h6');
                                    if (newCaption) {
                                        const caption = this.widget.querySelector('.caption');
                                        caption.querySelector('h6')?.replaceWith(newCaption);
                                        caption.querySelector('h6+div')?.replaceWith(newCaption.nextElementSibling);
                                    }

                                    modal.hide();
                                });
                        }
                    }
                }, { modalClass: 'modal_lg', headerClass: 'bg-primary' });
            });
    }
}

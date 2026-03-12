/*
 Tagger - Converts any text input box into an easy-to-edit multiple-tag interface.
 */


import {ModalDyn} from '/bundles/aropixeladmin/js/module/modal-dyn/modal-dyn.js';

(function($){


    // Paramètres par défaut
    let selectors = {

        modal: {
            id: '#modalFilesLibrary',
            uploader: '#modalFilesLibrary .file-uploader',
            dataTable: '#filesLibraryDataTable',
            checkbox: 'input[type="checkbox"][name^="file"]',
            attach: '.attach-files',
            delete: '.deleteFile',
        },

        table: {
            item: '.tableFiles tr.itemFile',
            unlink: '.iconUnlinkFile',
            edit: '.iconEditFile',
        },

        settings: {
            id: '#image_options',
            library: '#library_container',
            size: '#image_options .size-option',
            sizeField: '#image_options .size-option #img_size',
            alt: '#image_options .seo-option',
            altField: '#image_options .seo-option #img_alt',
        }


    }



    let FL_Launcher = function(element, options)
    {
        // Paramètres par défaut
        let defaults = {
            type: 'file',
            multiple: $(element).data('flMultiple')!='undefined' ? $(element).data('flMultiple') : false,
        }

        this.config = $.extend(defaults, options || {});
        this.element = $(element);
        this.selectors = selectors;

        if (typeof options === 'object' && ("editor" in options))
        {
            this.element.data('flAttachClass', options.category);
            this.editor = new FL_Editor(this, options.editor);
            this.editor.open_modal();
        }
        else
        {
            this.widget = new FL_Widget(this);
        }

        return this;
    };



    let FL_Editor = function(launcher, editor)
    {

        // Ouvre la modal et charge les images
        flcore.modal.set_launcher(launcher);

        this.open_modal = function()
        {
            $('.cke_dialog_background_cover').css('z-index', 960);
            $('.cke_dialog').css('z-index', 980);
            $(selectors.modal.id).modal('show');
        }

        this.insert_file = function()
        {

            let protomatch = /^(https?|ftp):\/\//;
            let _src = $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').closest('tr').find('.file-type').attr('data-src');
            _src = _src.replace(protomatch, '');

            $(selectors.modal.id).modal('hide');

            $('.cke_dialog_tabs > a').removeClass('cke_dialog_tab_selected');
            $('.cke_dialog_tabs > a:first').addClass('cke_dialog_tab_selected');
            $('.cke_dialog_contents > tbody > tr > td:first > div').hide();
            $('.cke_dialog_contents > tbody > tr > td:first > div:first').show();
            $('.cke_dialog_ui_input_text').val(_src);

        }

        return this;

    }



    let FL_Widget = function(launcher)
    {

        let obj = this;
        this.launcher = launcher;

        let filesWidget = this;
        launcher.element.find(selectors.table.item).each(function() {

            new FL_File_Widget(filesWidget, $(this));

        });


        this.sortable_list = function() {

            obj.launcher.element.find('.row').sortable({
                connectWith: ".row",
                update: function( event, ui ) {
                    obj.launcher.element.find('.thumbnail').each(function(index) {

                        let _input = $(this).find('input:hidden[name^=attach_gallery]');
                        if (_input.length) {
                            let new_name = _input.attr('name').replace(/attach\_gallery\[\d+\]/g, "attach_gallery["+(index)+"]");
                            _input.attr('name', new_name);
                        }

                    });
                }
            });

        }



        this.attach = function()
        {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.modal.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Association des fichiers').attr('disabled', 'disabled');

            let _fileData = launcher.element.data();

            let _attach_params = {};
            let _attach = [];
            let $selectedFile;
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {

                _attach.push($(this).val());
                $selectedFile = $(this).closest('tr');
            });


            _attach_params['multiple']     = '0';
            _attach_params['files']       = _attach;
            _attach_params['attach_id']    = _fileData.flAttachId;
            _attach_params['attach_class'] = _fileData.flAttachClass;
            _attach_params['entity_class'] = launcher.config.flEntityClass;


            $.post(_fileData.flAttachPath,  _attach_params, function(result) {

                // Nom des champs de formulaires à conserver
                let formFieldFile = launcher.element.find("input[name$='[file]']");
                let formFieldTitle = launcher.element.find("input[name$='[title]']");
                let formFieldAlt = launcher.element.find("input[name$='[alt]']");

                let $filesContent = launcher.element.find('.tableFiles');
                let $fileTbody = $filesContent.find('tbody');

                $fileTbody.html($(result));
                $fileTbody.find('div').empty();
                $fileTbody.find('div').append(formFieldAlt);
                $fileTbody.find('div').append(formFieldTitle);
                $fileTbody.find('div').append(formFieldFile);

                launcher.element.find("input[name$='[file]']").val($(result).find("[name$='[file]']").val());
                launcher.element.find("input[name$='[title]']").val($selectedFile.find('[data-modal-xeditable]').html());
                launcher.element.find("input[name$='[alt]']").val($(result).find("[name$='[alt]']").val());
                launcher.element.attr("data-fl-attach-id", $(result).attr('data-fl-attach-id'));

                new FL_File_Widget(filesWidget, $fileTbody);

                // params.onAttach(launcher, result);
                $(selectors.modal.id).modal('hide');

                // $.uniform.update($('.checkbox-library-thumb input').attr('checked',false));
                _button.html("Ajouter le fichier").attr('disabled', false);


            });

        }



        this.addToGallery = function()
        {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.modal.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Association des fichiers').attr('disabled', 'disabled');

            let _attach_params = {};
            let _attach = [];
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {

                let $filesContent = launcher.element.find('.tableFiles');
                let prototype = $filesContent.data('prototype');
                let index = 0;
                if (obj.launcher.config.multiple) {
                    index = $filesContent.find('.itemFile').length;
                }
                let newItem = prototype.replace(/__name__/g, index);


                let $selectedFile = $(this).closest('tr');
                $(newItem).find('span').html($selectedFile.find('.file-type').data('src'));

                $filesContent.find(".itemNew").remove();
                if (obj.launcher.config.multiple) {
                    $filesContent.append($(newItem));
                }
                else {
                    $filesContent.html($(newItem));
                }

                $filesContent.find('[name$="['+index+'][file]"]').val($(this).val());
                $filesContent.find('[name$="['+index+'][file]"]').closest('td').find('span').data('src', $selectedFile.find('.file-type').data('src'));
                $filesContent.find('[name$="['+index+'][file]"]').closest('td').find('span').html($selectedFile.find('[data-modal-xeditable]').html());
                $filesContent.find('[name$="['+index+'][title]"]').val($selectedFile.find('[data-modal-xeditable]').html());

                let $fileRow = $filesContent.find('[name$="['+index+'][file]"]').closest('tr');
                $fileRow.attr('data-fl-file-id', $(this).val());

                new FL_File_Widget(filesWidget, $fileRow);

                _button.html("Ajouter les fichiers").attr('disabled', false);
                $(selectors.modal.id).modal('hide');

            });


        }

        this.sortable_list();


    }



    let FL_File_Widget = function(filesWidget, fileRow)
    {
        let obj = this;
        let launcher = filesWidget.launcher;

        fileRow.on('click', selectors.table.unlink, function() {

            obj.detach($(this));

        });

        fileRow.on('click', selectors.table.edit, function() {

            obj.edit();

        });


        this.edit = function() {

            let _edit_params = {};
            let _imageGalleryId = fileRow.find('input[name^="attach_edit"]').val();
            let _imageAdminId = fileRow.find('input[name^="attach_new"]').val();


            if (_imageGalleryId) {
                let _params = {};
                let _url = Routing.generate('gallery_image_infos_edit', {id:_imageGalleryId});
            }
            else {
                let _params = widget.find('[name^="attach_infos"]').serialize();
                let _url = Routing.generate('gallery_image_infos_new', {id:_imageAdminId});
            }

            $.get(_url, _params, function(_modal_content) {

                let _buttons = {

                    "Fermer": function() {
                        $(this).closest('.modal').modal('hide');
                    },

                    "Modifier": {

                        'class' : 'btn-primary',
                        'callback' : function() {

                            let _modal = $(this).closest('.modal');
                            let _form = $(this).closest('.modal').find('form');
                            $.post(_form.attr('action'), _form.serialize(), function(info_fields) {

                                if ($(info_fields).find('[name^="attach_infos"]').length) {

                                    widget.find('[name^="attach_infos"]').remove();
                                    $(info_fields).find('[name^="attach_infos"]').insertAfter(widget.find('.preview img'));

                                }

                                if ($(info_fields).find('h6').length) {
                                    widget.find('.caption h6').replaceWith($(info_fields).find('h6'));
                                    widget.find('.caption h6+div').replaceWith($(info_fields).find('h6').next());
                                }

                                _modal.modal('hide');

                            })

                        },

                    }
                }

                new ModalDyn("Modifier l'image", _modal_content, _buttons, {modalClass: 'modal_lg', headerClass: 'bg-primary'});

            })
        }



        this.detach = function(button) {

            let _buttons = {

                "Fermer": function() {
                    $(this).closest('.modal').modal('hide');
                },

                "Supprimer": {

                    'class' : 'btn-danger',
                    'callback' : function() {

                        let $filesTbody = button.closest('tbody');
                        let placeholder = button.closest('table').data('placeholder');

                        button.closest('tr').fadeOut(400, function() {
                            let formFields = $filesTbody.find('div');
                            $(this).remove();
                            if ($filesTbody.children('tr').length == 0) {

                                // if (launcher.config.multiple == 0) {
                                $filesTbody.html(placeholder);
                                $filesTbody.find('tr td:first').append(formFields);
                                $filesTbody.find('input:hidden').removeAttr('value');
                                // }
                                // else {
                                //     $filesTbody.html(placeholder);
                                // }
                            }
                        });
                        $(this).closest('.modal').modal('hide');

                    },

                }
            }

            new ModalDyn("Supprimer", "Voulez-vous supprimer le fichier ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

        }


    }



    let FL_Modal = function()
    {

        let obj = this;
        let images = new Array();
        let config = {};

        this.launcher = false;


        // Attribue le launcher du widget (et ses réglages) à la popup
        // lors de l'ouverture de la popup
        $(selectors.modal.id).on('show.bs.modal', function (event) {

            // Remet toutes les checkboxes non cochées
            $(selectors.modal.id).css('z-index', 1080);
            $(selectors.modal.checkbox).attr('checked', false);

            let button = $(event.relatedTarget);
            if (button.length) {
                obj.launcher = button.closest('[data-fl-type]').data('launcher');
            }

            config = obj.launcher.config;
            obj.load_files();
        });



        this.load_files = function(button) {

            let _public = (typeof obj.launcher.editor != "undefined");
            let _class = obj.launcher.element.data('flAttachClass');
            let _src = $(selectors.modal.dataTable).attr('data-src')
            let _params = {
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": $.fn.dataTable.pipeline( {
                    url: encodeURI(_src + '/' + _class + '?editor=' + _public),
                    pages: 5 // number of pages to cache
                } )
            };

            $(selectors.modal.dataTable).DataTable().clearPipeline().destroy();
            $(selectors.modal.dataTable)
                .on( 'init.dt', function () {


                    // External table additions
                    // ------------------------------

                    // Add placeholder to the datatable filter option
                    $('.dataTables_filter input[type=search]').attr('placeholder','Taper pour filtrer...');


                    // Enable Select2 select for the length option
                    $('.dataTables_length select').select2({
                        minimumResultsForSearch: Infinity,
                        width: 'auto'
                    });

                } )
                .dataTable(_params);

            $(selectors.modal.id).data('flAttachClass', _class);

        };



        // Event // Clic sur le bouton de validation
        $(selectors.modal.attach).click(function() {

            obj.valid_files();

        });


        // Event // Clic sur le bouton de validation
        $(selectors.modal.dataTable).on('click', selectors.modal.delete, function() {

            let $deleteButton = $(this);

            let _detach_params = {};
            _detach_params['category'] = obj.launcher.element.data('flAttachClass');
            _detach_params['entity_id'] = obj.launcher.config.flEntityId;
            _detach_params['file_id'] = $(this).data('id');

            let _buttons = {

                "Fermer": function() {
                    $(this).closest('.modal').modal('hide');
                },

                "Supprimer": {

                    'class' : 'btn-danger',
                    'callback' : function() {

                        $.post($deleteButton.attr('data-path'), _detach_params, function(answer) {

                            flcore.modal.load_files();

                        })

                        $(this).closest('.modal').modal('hide');

                    },

                }
            }

            new ModalDyn("Supprimer", "Voulez-vous supprimer le fichier de la bibliothèque ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

        });


        // Event // Clic sur checkbox
        $(selectors.modal.dataTable).on('click', selectors.modal.checkbox, function() {

            if ("editor" in obj.launcher || obj.launcher.config.multiple==0) {

                $(selectors.modal.checkbox).not($(this)).attr('checked', false);

            }

        });



        this.set_launcher = function(launcher)
        {
            obj.launcher = launcher;
        }




        this.valid_files = function()
        {
            if ("editor" in this.launcher)
            {
                this.launcher.editor.insert_file();
            }
            else if (obj.launcher.config.multiple==1)
            {
                this.launcher.widget.addToGallery();
            }
            else if (obj.launcher.config.multiple==0)
            {
                this.launcher.widget.attach();
            }
        }


    };


    let FL_File = function(li, launcher)
    {

        let obj = this;
        let my_id = $(li).attr('data-id');
        let my_li = $(li);

        this.get_id = function()
        {
            return my_id;
        }

        this.update_description = function()
        {

            let _li = $(selectors.image_list_selected);

            // Si la description a changé, on l'enregistre
            if ($(selectors.image_info_title).val()!=_li.attr('data-title'))
            {
                let description = $(selectors.image_info_title);

                // On sauvegarde dans la nouvelle description en propriété de la vignette
                // et on lance une sauvegarde asynchrone
                _li.attr('data-title', description.val());
                $.post(_base_url + "images/update.html", {id:_li.attr('data-id'), title:description.val()}, function() {});
            }

        };


    };



    let FL_Uploader = function()
    {

        let obj = this;
        this.element = $(selectors.modal.uploader);
        this.dataTable = $(selectors.modal.dataTable).DataTable();
        this.progress = this.element.next();
        this.category = this.element.data('category');


        this.init = function()
        {
            this.init_uploader();
        };


        this.init_uploader = function()
        {
            const button = this.element[0];
            const input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.style.display = 'none';
            document.body.appendChild(input);

            button.addEventListener('click', (e) => {
                e.preventDefault();
                const accept = this.element.data('flAccept');
                if (accept) {
                    input.accept = accept;
                }
                input.click();
            });

            input.addEventListener('change', async (e) => {
                const files = Array.from(e.target.files);
                if (!files.length) return;

                for (const file of files) {
                    await this.uploadFile(file);
                }

                input.value = '';
            });
        };

        this.uploadFile = async function(file) {
            const formData = new FormData();
            const category = $(selectors.modal.id).data('flAttachClass');
            const isPublic = flcore.modal.launcher.editor ? '1' : '0';

            formData.append('aropixel_admin_library_file[file]', file);
            formData.append('aropixel_admin_library_file[category]', category);
            formData.append('aropixel_admin_library_file[title]', file.name);
            formData.append('aropixel_admin_library_file[public]', isPublic);
            formData.append('_http_accept', 'application/javascript');

            const fileId = 'file-' + Math.random().toString(36).substring(2);
            this.progress.append(`<li id="${fileId}" class="width-200">
                <div class="info">${file.name}</div>
                <div class="progress progress-striped active"><div class="progress-bar" style="width: 0;"></div></div>
            </li>`);

            const progressBar = document.querySelector(`#${fileId} .progress-bar`);

            try {
                const response = await this.xhrUpload(this.element.data('path'), formData, (percent) => {
                    if (progressBar) progressBar.style.width = `${percent}%`;
                });

                if (response.status >= 200 && response.status < 300) {
                    $(`#${fileId}`).remove();
                    if (this.progress.children().length === 0) {
                        this.progress.html('');
                    }
                    flcore.modal.load_files();
                } else {
                    throw new Error(response.responseText || 'Upload failed');
                }
            } catch (error) {
                console.error(error);
                alert('Erreur lors de l\'upload : ' + error.message);
            }
        };

        this.xhrUpload = function(url, formData, onProgress) {
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
        };


        this.init();
        return this;
    };


    $.fn.extend({

        FileManager: function(options)
        {
            return this.each(function()
            {
                // Le launcher initie les comportements du widget
                // et transmet les bons paramètres au core
                let launcher = new FL_Launcher(this, options);
                $(this).data('launcher', launcher);


            });
        }
    });





    let FL_Core = function()
    {
        this.modal = new FL_Modal();
        this.uploader = new FL_Uploader();

        // return this;
    }


    let flcore = false;


    $(document).ready(function() {


        // Le core démarre l'uploader
        // Initie les comportements de base de la modal
        flcore = new FL_Core();

        $('.fl-manager').each(function() {


            // PARAMETRES DISPONIBLES
            // - type [gallery|image|editor] : Type de manager
            // - target : Container pour afficher le/les images/vidéos attachées
            // - entity-class : Catégorie d'image à rechercher dans la library d'images
            // - entity-id : ID de l'entité à laquelle rattacher la ou les images
            // - route : Current route pour charger les crop disponibles
            $(this).FileManager();

        });

        let gallery =  document.querySelector('.galleryContent');

        if (gallery) {
            gallery.prepend(document.querySelector('.add-gallery-image'));
        }

    });



})(jQuery);

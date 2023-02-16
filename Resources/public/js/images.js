/*
 Tagger - Converts any text input box into an easy-to-edit multiple-tag interface.
 */

import {ModalDyn} from './module/modal-dyn/modal-dyn.js';

(function($){


    // Paramètres par défaut
    let selectors = {

        modal: {
            id: '#modalLibrary',
            uploader: '#modalLibrary .image-uploader',
            dataTable: '#libraryDataTable',
            checkbox: 'input[type="checkbox"][name^="image"]',
            attach: '.attach-images',
            delete: '[data-library="delete"]',
        },

        panel: {
            open: '.image-actions .btnUpload',
            unlink: '.btnUnlink',
            edit: '.iconEdit',
            crop: '.iconCrop',
        },

        gallery: {
            container: '.gallery_pictures',
            grid: 'col-lg-3 col-sm-6',
        },

        video: {
            modal: '#modalVideo',
            textarea: '#videoIframe',
            preview: '#videoPreview',
            attach: '#videoAttach',
        },

        crop: {
            class: '.modalCrop',
            ratios: '#crop_options input',
            imageContainer: '#crop_zone',
            image: '#crop_zone > img',
            save: '.crop-file'
        },

        attributes: {
            class: '#modalAttributes',
            inputs: '#modalAttributes .modal-body input',
            save: '.save-attributes'
        },
        settings: {
            id: '#image_options',
            library: '#library_container',
            size: '#image_options .size-option',
            sizeField: '#image_options .size-option #img_size',
            filter: '#image_options .size-option .size-filter-option',
            filterField: '#image_options .size-option #img_filter',
            alt: '#image_options .seo-option',
            altField: '#image_options .seo-option #img_alt',
        }


    }



    let IM_Launcher = function(element, options)
    {
        // Paramètres par défaut
        let defaults = {
            type: 'image',
            multiple: false,
        }

        //
        this.config = $.extend(defaults, $(element).data() || {});
        this.element = $(element);
        this.selectors = selectors;


        //
        if (typeof options === 'object' && ("editor" in options))
        {
            this.element.data('imLibrary', options.category);
            this.element.data('imAttachClass', options.category);
            this.element.data('imAttachEditor', options.attach_path);
            this.config = $.extend(defaults, options || {});
            this.config.imType = 'editor';
            this.editor = new IM_Editor(this, options.editor);
        }
        else if (this.config.imType=='gallery')
        {
            this.gallery = new IM_Gallery(this);
        }
        else
        {
            this.widget = new IM_Widget(this);
        }

        return this;
    };




    let IM_Editor = function(launcher, editor)
    {

        // Ouvre la modal et charge les images
        imcore.modal.set_launcher(launcher);
        $(selectors.modal.id).modal('show');

        //
        this.insert_image = function()
        {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.modal.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Création des vignettes').attr('disabled', 'disabled');


            let _attach_params = {};
            let _attach = [];
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {

                _attach.push($(this).val());

            });


            //
            _attach_params['images'] = _attach;
            _attach_params['width'] = $(selectors.settings.sizeField).val();
            _attach_params['filter'] = $(selectors.settings.filterField).val();
            _attach_params['alt'] = $(selectors.settings.altField).val();

            if (launcher.config.attach_params!=false)
            {
                let isFunc = jQuery.isFunction( launcher.config.attach_params );
                if (isFunc)         $.extend( _attach_params, launcher.config.attach_params() );
                else                $.extend( _attach_params, launcher.config.attach_params );
            }


            $.post( launcher.element.data('imAttachEditor'),  _attach_params, function(result) {

                editor.insertHtml( result );
                editor.focusManager.focus();

                $(selectors.modal.id).modal('hide');

                _button.html("Ajouter l'image").attr('disabled', false);

            });

        }

        return this;

    }



    let IM_Widget = function(launcher)
    {

        let obj = this;
        let widget = launcher.element;

        let cropper = new IM_Cropper(launcher);
        cropper.initCropper();

        widget.on('click', selectors.panel.unlink, function() {

            obj.detach();

        });


        this.attach = function()
        {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.modal.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Création des vignettes').attr('disabled', 'disabled');

            let _thumbData = widget.find(".thumbnail").data();


            let _attach_params = {};
            let _attach = [];
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {

                _attach.push($(this).val());

            });


            //
            _attach_params['route']        = launcher.config.imRoute;
            _attach_params['multiple']     = '0';
            _attach_params['images']       = _attach;
            _attach_params['data_type']    = launcher.config.imDataType;
            _attach_params['attach_id']    = _thumbData.imAttachId;
            _attach_params['attach_class'] = _thumbData.imAttachClass;
            _attach_params['attach_value'] = _thumbData.imAttachValue;
            _attach_params['entity_class'] = launcher.config.imEntityClass;
            _attach_params['crops_slugs'] = _thumbData.imCropsSlugs;
            _attach_params['crops_labels'] = _thumbData.imCropsLabels;


            if (launcher.config.attach_params!=false)
            {
                let isFunc = jQuery.isFunction( launcher.config.attach_params );
                if (isFunc)         $.extend( _attach_params, launcher.config.attach_params() );
                else                $.extend( _attach_params, launcher.config.attach_params );
            }



            //
            $.post(_thumbData.imAttachPath,  _attach_params, function(result) {

                // Update widget image
                if (launcher.element.find(".preview > img").length) {
                    launcher.element.find(".preview > img").replaceWith($(result).find('.preview > img'));
                }
                else {
                    launcher.element.find(".no-img").replaceWith($(result).find('.preview > img'));
                }

                // Update file_name field
                launcher.element.find(".preview input[name$='[file_name]']").val($(result).find(".preview input[name$='[file_name]']").val());
                launcher.element.find(".preview input[name$='[image]']").val($(result).find(".preview input[name$='[image]']").val());

                // Update footer
                launcher.element.find(".caption").html($(result).find('.caption').html());

                // Remove all action buttons except upload (first)
                launcher.element.find(".image-actions .btnUnlink").remove();
                launcher.element.find(".image-actions .iconCrop").remove();
                launcher.element.find(".image-actions .btnUpload").after($(result).find('.image-actions .btnUnlink'));

                /*launcher.element.find(".caption-overflow a:not(:first-child)").remove();
                launcher.element.find(".caption-overflow a:first-child").after($(result).find('.caption-overflow a:not(:first-child)'));*/

                // Give correct modal target to crop button
                launcher.element.find(".caption-overflow .iconCrop").attr('data-target', '#'+launcher.element.find('.modalCrop').attr('id'));

                launcher.element.find(".thumbnail").attr("data-im-crop-path", $(result).attr('data-im-crop-path'));
                launcher.element.find(".thumbnail").attr("data-im-image-id", $(result).attr('data-im-image-id'));
                launcher.element.find(".thumbnail").attr("data-im-attach-id", $(result).attr('data-im-attach-id'));

                // params.onAttach(launcher, result);
                $(selectors.modal.id).modal('hide');

                // $.uniform.update($('.checkbox-library-thumb input').attr('checked',false));
                _button.html("Ajouter l'image").attr('disabled', false);

            });



        }


        this.detach = function() {


            let _buttons = {

                "Fermer": function() {
                    $(this).closest('.modal').modal('hide');
                },

                "Supprimer": {

                    'class' : 'btn-danger',
                    'callback' : function() {

                        let _button = $(this);
                        if (launcher.config.multiple)
                        {
                            widget.parent().remove();
                        }
                        else
                        {
                            let _preview = launcher.element.find(".preview");
                            launcher.element.find(".preview > img").replaceWith(_preview.attr('data-new'));
                            //launcher.element.find(".caption-overflow .iconUnlink").remove();
                            launcher.element.find(".image-actions .btnUnlink").remove();
                            launcher.element.find(".image-actions .iconCrop").remove();
                            launcher.element.find(".caption-overflow .iconCrop").remove();

                            launcher.element.find(".preview input[name$='[image]']").removeAttr('value');
                            launcher.element.find(".preview input[name$='[title]']").removeAttr('value');
                            launcher.element.find(".preview input[name$='[alt]']").removeAttr('value');

                            launcher.element.find(".preview input[name$='[file_name]']").removeAttr('value');

                        }
                        _button.closest('.modal').modal('hide');

                    },

                }
            }

            new ModalDyn("Supprimer", "Voulez-vous supprimer l'image de ce contenu ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
        }


        //
        this.open_crop = function() {

        }

    }





    let IM_Gallery = function(launcher)
    {
        let obj = this;
        this.launcher = launcher;

        let cropper = new IM_Cropper(launcher);
        cropper.initCropper();

        let gallery = this;
        launcher.element.find('.thumbnail').each(function() {

            new IM_Gallery_Widget(gallery, $(this));

        });


        $(selectors.video.modal).on('keyup', selectors.video.textarea, function() {


            let _content = $(selectors.video.textarea).val();
            let _modalWidth = $(selectors.video.modal).find('.modal-content').outerWidth() - 2;

            if ($(_content).prop("tagName").toLowerCase()=='iframe') {

                let _width = $(_content).attr("width");
                let _height = $(_content).attr("height");
                let _newHeight = Math.round(_height * _modalWidth / _width);


                let _iframe = $(_content).clone();
                _iframe.attr("width", _modalWidth);
                _iframe.attr("height", _newHeight);


                $(selectors.video.preview).html(_iframe);

            }

        });


        $(selectors.video.modal).on('click', selectors.video.attach, function() {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.video.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Ajouter la vidéo').attr('disabled', 'disabled');

            let _attach_params = {};
            _attach_params['iframe']   = $(selectors.video.textarea).val();
            _attach_params['category'] = launcher.config.imEntityClass;
            _attach_params['id']       = launcher.config.imEntityId;
            _attach_params['route']    = launcher.config.imRoute;
            _attach_params['position'] = $(launcher.config.imTarget+' .thumbnail').length;
            _attach_params['multiple'] = '1';


            $.post(Routing.generate('gallery_video'),  _attach_params, function(result) {

                let _element = $('<div></div>').addClass(selectors.gallery.grid).html(result);

                if ($(launcher.config.imTarget+' .row:last > div').length < 4) {
                    $(launcher.config.imTarget+' .row:last').append(_element);
                }
                else {
                    let _row = $('<div></div>').addClass('row').html(_element);
                    $(launcher.config.imTarget+' .row:last').after(_row);
                }

                $(selectors.video.modal).modal('hide');
                $(launcher.config.imTarget+' .row').sortable( "refresh" );

                _button.html("Ajouter la video").attr('disabled', false);

            });


        });


        this.sortable_list = function() {

            obj.launcher.element.find('.row').sortable({
                items: "> div",
                update: function( event, ui ) {
                    obj.launcher.element.find('.thumbnail').each(function(index) {

                        let _input = $(this).find('input:hidden');
                        if (_input.length) {
                            _input.each(function() {
                                let new_name = $(this).attr('name').replace(/\[[0-9]+\]?/, "["+(index)+"]");
                                $(this).attr('name', new_name);
                            });
                        }

                    });
                }
            });

        }


        this.reIndex = function()
        {

            launcher.element.find('.thumbnail').each(function(i) {

                $(this).find('input:hidden').each(function() {

                    let old_name = $(this).attr('name');
                    let new_name = old_name.replace(/\[[0-9]+\]?/, function (match, $1) {
                        return '[' + i + ']';
                    });

                    $(this).attr('name', new_name);

                })
            });


        }


        this.attach = function()
        {

            // On désactive le bouton, et on change le texte
            let _button = $(selectors.modal.attach);
            _button.html('<i class="icon-spinner2 spinner position-left"></i> Création des vignettes').attr('disabled', 'disabled');


            let _attach_params = {};
            let _attach = [];
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {


                let $galleryContent = launcher.element.find('> .galleryContent');
                let prototype = $galleryContent.data('prototype');
                let index = $galleryContent.children().length;

                // Turn the prototype to objects
                let $newItem = $(prototype);


                // Replace __name__ in childs which have this pattern in name and id attr
                $newItem.find('[name*="[__name__]"]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/__name__/g, index))
                })

                // Replace __name__ in childs which have this pattern in name and id attr
                $newItem.find('[id*="__name__"]').each(function() {
                    $(this).attr('id', $(this).attr('id').replace(/__name__/g, index))
                })

                // Replace __name__in crops prototype
                $newItem.find('[data-prototype*="[__name__]"]').each(function() {

                    $(this).attr('data-prototype', $(this).attr('data-prototype').replace(/\[__name__\]\[crops\]/g, '['+index+'][crops]'))
                    $(this).attr('data-prototype', $(this).attr('data-prototype').replace(/___name___crops/g, index+'_crops'))

                })


                // Get image of the library
                let $imgLibraryModal = $(this).closest('tr').find('.img-preview');
                let $img = $('<img>').attr('src', $imgLibraryModal.attr('src'));

                // Add a new image from the prototype in the container, and replace the place holder by the image
                $galleryContent.append($newItem);
                $galleryContent.find(".no-img").replaceWith($img);

                let $thumbnail = $galleryContent.find('[name$="['+index+'][title]"]').closest('.thumbnail');

                // If the image is stored as a relation
                let $attach_image = $galleryContent.find('[name$="['+index+'][image]"]');
                if ($attach_image.length) {

                    $thumbnail.attr('data-im-image-id', $(this).val());
                    $attach_image.val($(this).val());

                }
                // If image is stored as a file name
                else {
                    let filename = $img.attr('src').split('\\').pop().split('/').pop();
                    $galleryContent.find('[name$="['+index+'][file_name]"]').val(filename)
                }

                $thumbnail.attr('data-im-crop-path', $imgLibraryModal.attr('data-crop-path'));

                if (launcher.config.imCropActive) {
                    $thumbnail.find(".iconCrop").attr('data-target', $thumbnail.find(".iconCrop").attr('data-target') + launcher.config.imAttachShortClass);
                }

                new IM_Gallery_Widget(gallery, $thumbnail);

                _button.html("Ajouter l'image").attr('disabled', false);
                $(selectors.modal.id).modal('hide');

            });


        }

        this.sortable_list();

    }


    let IM_Gallery_Widget = function(gallery, widget)
    {
        let obj = this;
        let launcher = gallery.launcher;

        widget.on('click', selectors.panel.unlink, function() {

            obj.detach();

        });

        widget.on('click', selectors.panel.edit, function() {

            obj.edit();

        });


        this.edit = function() {

            let _edit_params = {};
            let _imageGalleryId = widget.find('input[name^="attach_edit"]').val();
            let _imageAdminId = widget.find('input[name^="attach_new"]').val();


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



        //
        this.detach = function() {


            let _buttons = {

                "Fermer": function() {
                    $(this).closest('.modal').modal('hide');
                },

                "Supprimer": {

                    'class' : 'btn-danger',
                    'callback' : function() {

                        widget.parent().fadeOut(400, function() { $(this).remove(); gallery.reIndex(); } );
                        $(this).closest('.modal').modal('hide');

                    },

                }
            }

            new ModalDyn("Supprimer", "Voulez-vous supprimer l'image de la galerie ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

        }


        //
        this.open_crop = function() {

        }

    }



    let IM_Modal = function()
    {

        let obj = this;
        let images = new Array();
        let config = {};

        this.launcher = false;


        // Attribue le launcher du widget (et ses réglages) à la popup
        // lors de l'ouverture de la popup
        $(selectors.modal.id).on('show.bs.modal', function (event) {

            // Remet toutes les checkboxes non cochées
            $(selectors.modal.id).css('z-index', 9996);
            $(selectors.modal.checkbox).attr('checked', false);

            let button = $(event.relatedTarget);
            if (button.length) {
                if (button.attr('data-rel')) {
                    obj.launcher = $('#' + button.attr('data-rel')).closest('[data-im-type]').data('launcher');;
                }
                else {
                    obj.launcher = button.closest('[data-im-type]').data('launcher');
                }
            }

            config = obj.launcher.config;

            if (config.imType == 'editor') {

                $(selectors.settings.library).removeClass('col-md-12').addClass('col-md-8');
                $(selectors.settings.id).show();

            }
            else {

                $(selectors.settings.library).removeClass('col-md-8').addClass('col-md-12');
                $(selectors.settings.id).hide();

            }

            obj.load_pictures();
        });


        // Attribue le launcher du widget (et ses réglages) à la popup
        // lors de l'ouverture de la popup
        $(selectors.modal.id).on('hide.bs.modal', function (event) {
            $('#alertUploadError').html('').hide();
        });



        let _modal_attributes = $(selectors.attributes.class);

        _modal_attributes.on('show.bs.modal', function (event) {

            let attributesButton = $(event.relatedTarget);
            _modal_attributes.data('related', attributesButton);


            // Read values from widget hidden fields
            // to populate the attributes modal fields
            let attributesInputs = $(selectors.attributes.inputs);
            let $widget = attributesButton.closest('.thumb');
            attributesInputs.each(function(index) {

                let _input = $(this);
                let _val = $widget.find('[name$="['+_input.attr('name')+']"]').val();

                _input.val(_val);
            });


            let $attributesContainer = $widget.closest('[data-title-enabled]');
            let isTitleEnabled = ($attributesContainer.attr('data-title-enabled') === '1');
            let isDescriptionEnabled = ($attributesContainer.attr('data-description-enabled') === '1');
            let isLinkEnabled = ($attributesContainer.attr('data-link-enabled') === '1');

            if (!isTitleEnabled && !isDescriptionEnabled && !isLinkEnabled) {
                _modal_attributes.find('.nav-tabs').hide();
                _modal_attributes.find('#gallery-image-texts').hide();
                _modal_attributes.find('#gallery-image-attributes').removeClass('show active').show();
            }
            else {
                _modal_attributes.find('.nav-tabs').show();
                _modal_attributes.find('.nav-tabs li:first a').addClass('active');
                _modal_attributes.find('.nav-tabs li:not(:first) a').removeClass('active');
                _modal_attributes.find('#gallery-image-texts').addClass('show active').removeAttr('style');
                _modal_attributes.find('#gallery-image-attributes').removeClass('show active').removeAttr('style');
            }

            if (isTitleEnabled) {
                _modal_attributes.find('#img_title').show();
                _modal_attributes.find('#img_title label').html($attributesContainer.attr('data-title-label'))
            }
            else {
                _modal_attributes.find('#img_title').hide();
            }

            if (isDescriptionEnabled) {
                _modal_attributes.find('#img_description').show();
                _modal_attributes.find('#img_description label').html($attributesContainer.attr('data-description-label'))
            }
            else {
                _modal_attributes.find('#img_description').hide();
            }

            if (isLinkEnabled) {
                _modal_attributes.find('#img_link').show();
                _modal_attributes.find('#img_link label').html($attributesContainer.attr('data-link-label'))
            }
            else {
                _modal_attributes.find('#img_link').hide();
            }


        });


        _modal_attributes.on('click', selectors.attributes.save, function() {

            // Get the data-prototype explained earlier
            let attributesButton = _modal_attributes.data('related');
            let attributesInputs = $(selectors.attributes.inputs);
            let $widget = attributesButton.closest('.thumb');

            attributesInputs.each(function(index) {

                let _input = $(this);
                $widget.find('[name$="['+_input.attr('name')+']"]').val(_input.val());

            });

            _modal_attributes.modal('hide');

        });


        this.load_pictures = function(button) {

            let _class = obj.launcher.element.data('imAttachClass');
            let _library = obj.launcher.element.data('imLibrary');
            let _src = $(selectors.modal.dataTable).attr('data-src')
            let _params = {
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": $.fn.dataTable.pipeline( {
                    url: encodeURI(_src + '/' + _library),
                    pages: 5 // number of pages to cache
                } )
            };

            //
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

            $(selectors.modal.id).data('imLibrary', _library);

        };


        // Event // Clic sur le bouton de validation
        $(selectors.modal.attach).click(function() {


            let _attach = [];
            $(selectors.modal.dataTable+' '+selectors.modal.checkbox+':checked').each(function() {

                _attach.push($(this).val());

            });

            if (_attach.length==0) {
                $('#alertNoImg').fadeIn('fast');
            }
            else {

                obj.valid_pictures();

            }

        });


        // Event // Clic sur le bouton de validation
        $(selectors.modal.dataTable).on('click', selectors.modal.delete, function() {

            let $deleteButton = $(this);

            let _detach_params = {};
            _detach_params['category'] = obj.launcher.config.imLibrary;
            _detach_params['entity_id'] = obj.launcher.config.imEntityId;
            _detach_params['image_id'] = $(this).data('id');

            let _buttons = {

                "Fermer": function() {
                    $(this).closest('.modal').modal('hide');
                },

                "Supprimer": {

                    'class' : 'btn-danger',
                    'callback' : function() {

                        let closeModal = $(this).closest('.modal');
                        $.post($deleteButton.attr('data-path'), _detach_params, function(answer) {

                            //
                            if (answer == 'OK') {

                                //
                                imcore.modal.load_pictures();

                            }
                            else if (answer == 'FOREIGN_KEY') {

                                $('#alertUploadError').html('<strong>Suppression impossible.</strong> Votre image est utilisée dans un ou plusieurs contenus.').fadeIn();

                            }
                            else {

                                $('#alertUploadError').html('Votre image n\'a pas pu être supprimée.').fadeIn();

                            }

                            $(selectors.crop.id).modal('hide');
                            closeModal.modal('hide');

                        })

                    },

                }
            }

            new ModalDyn("Supprimer", "Voulez-vous supprimer l'image de la bibliothèque ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

        });


        // Event // Clic sur checkbox
        $(selectors.modal.dataTable).on('click', selectors.modal.checkbox, function() {

            $('#alertNoImg').hide();
            if (config.imType == 'image') {
                $(selectors.modal.checkbox).not($(this)).attr('checked', false);
            }

        });


        // Event // Clic sur checkbox
        $(selectors.settings.size).on('change', '#img_size', function() {

            if ($(this).val() == 'customfilter') {
                $(selectors.settings.filter).fadeIn();
            }
            else {
                $(selectors.settings.filter).fadeOut();
            }

        });


        this.set_launcher = function(launcher)
        {
            obj.launcher = launcher;
        }


        this.valid_pictures = function()
        {
            if (this.launcher.config.imType == 'editor')
            {
                this.launcher.editor.insert_image();
            }
            else if (this.launcher.config.imType == 'gallery')
            {
                this.launcher.gallery.attach();
            }
            else
            {
                this.launcher.widget.attach();
            }
        }

    }


    let IM_Cropper = function(launcher)
    {
        let obj = this;
        let $modal = launcher.element.find(selectors.crop.class)

        this.cropper_options = function(button) {

            let $image = $modal.find(selectors.crop.image);

            let _options = {
                viewMode: 2,
                aspectRatio: button.attr("data-ratio"),
                autoCropArea: 1,
                zoomable: false,
                minContainerWidth: 200,
                minContainerHeight: 200,
                ready: function(e) {
                    // e.preventDefault();
                    // if ($modal.find(selectors.crop.ratios+':checked').attr('data-crop')) {
                    //
                    //     var _data = $modal.find(selectors.crop.ratios+':checked').attr('data-crop').split(",");
                    //     let _cropboxdata = {left: _data[0], top: _data[1], width: _data[2], height: _data[3]};
                    //     $image.cropper('setCropBoxData', _cropboxdata);
                    // }
                },
                cropend: function(e) {

                    // Prevent to start cropping, moving, etc if necessary
                    // if (e.action === 'crop') {
                    //     e.preventDefault();
                    // }
                    // else {
                    //     $modal.find(selectors.crop.ratios+':checked').attr("data-crop", data.x+","+data.y+","+data.width+","+data.height);
                    // }

                },
                cropstart: function(e) {
                },
                cropmove: function(e) {
                },
                crop: function(data) {
                    $modal.find(selectors.crop.ratios+':checked').attr("data-crop", data.x+","+data.y+","+data.width+","+data.height);
                }
            }

            if (button.attr('data-crop') && button.attr('data-crop').length)
            {
                let _data = button.attr('data-crop').split(",");
                _options['data'] = {x: Math.floor(_data[0]), y: Math.floor(_data[1]), width: Math.floor(_data[2]), height: Math.floor(_data[3])};
            }
            return _options;
        }


        this.initCropper = function (_modal) {

            if (!$modal.data('initialized')) {

                $modal.on('shown.bs.modal', function (event) {

                    let cropButton = $(event.relatedTarget);
                    if (cropButton.length) {
                        if (cropButton.closest('[data-im-type]').length) {
                            obj.launcher = cropButton.closest('[data-im-type]').data('launcher');
                        }
                        else if (cropButton.closest('[rel="im-manager"]').length) {
                            obj.launcher = cropButton.closest('[rel="im-manager"]').data('launcher');
                        }
                    }

                    let $thumbnail = cropButton.closest('.image-widget').find('.thumbnail');
                    let ImgId = $thumbnail.attr('data-im-image-id');
                    let ImgSrc = $thumbnail.attr('data-im-crop-path');


                    let changeImage = false;
                    let $button = $modal.find(selectors.crop.ratios+':first');
                    let $image = $modal.find(selectors.crop.image);
                    let $imageContainer = $modal.find(selectors.crop.imageContainer);
                    let isInitialized = $image.data('cropper');


                    // S'il n'y avait pas d'image, ou si l'image a changé
                    if (!$image.length || parseInt($image.data('id')) != parseInt(ImgId)) {

                        // S'il n'y avait pas d'image
                        if (!$image.length) {

                            let _img = $('<img>')
                                .attr("src", ImgSrc);

                            $modal.find(selectors.crop.imageContainer).html(_img);
                            $modal.find(selectors.crop.image).attr("width", 600);
                            $image = $modal.find(selectors.crop.image);

                        }
                        else {
                            changeImage = true;
                        }

                        $imageContainer.data('id', ImgId);
                        $imageContainer.data('thumb', $thumbnail.attr('id'));
                        $modal.find(selectors.crop.ratios).each(function() {

                            // On met à jour la valeur du crop (associée au radio button)
                            $(this).removeAttr('data-crop');

                            let $imageCropInfo = $thumbnail.find('[name$="[filter]"][value="'+$(this).val()+'"]').next();
                            if ($imageCropInfo.length && $imageCropInfo.val().length) {
                                $(this).attr('data-crop', $imageCropInfo.val());
                            }

                        });

                    }
                    else {
                    }

                    $modal.on('change', selectors.crop.ratios, function() {

                        let _options = obj.cropper_options($(this));
                        $modal.find(selectors.crop.image).cropper('destroy').cropper(_options);

                    });

                    $modal.on('click', selectors.crop.save, function() {

                        // Get the data-prototype explained earlier
                        let $collectionHolder = $thumbnail.find('[id$="_crops"]');
                        // $collectionHolder = _modal.next();
                        $collectionHolder.empty();
                        let prototype = $collectionHolder.data('prototype');

                        $modal.find(selectors.crop.ratios).each(function(index) {

                            let _ratio = $(this);

                            // Replace '__name__' in the prototype's HTML to
                            // instead be a number based on how many items we have
                            let newForm = prototype.replace(/__name__/g, index);
                            $collectionHolder.append(newForm);

                            $collectionHolder.find('[name$="['+index+'][filter]"]').val(_ratio.val());
                            $collectionHolder.find('[name$="['+index+'][crop]"]').val(_ratio.attr('data-crop'));
                        });

                        $modal.modal('hide');

                    });

                    let _options = obj.cropper_options($button);
                    $image.cropper('destroy').attr('src', ImgSrc).cropper(_options);

                    $modal.data('initialized', true);

                });

            }

        }

    };


    let IM_Uploader = function()
    {

        let obj = this;
        this.element = $(selectors.modal.uploader);
        this.dataTable = $(selectors.modal.dataTable).DataTable();
        this.progress = this.element.next();
        this.category = this.element.data('category');


        let params = {

            runtimes : 'gears,html5,flash,silverlight,browserplus',
            max_file_size : '20mb',
            flash_swf_url : '../plugins/uploader/plupload.flash.swf',
            silverlight_xap_url : '../plugins/uploader/plupload.silverlight.xap',
            multi_selection : true,
            filters : [
                {title : "Fichiers images", extensions : "jpg,gif,png,jpeg"}
            ]

        }


        this.init = function()
        {
            if (!obj.element.data('plupload') || obj.element.data('plupload')=='undefined')
            {
                obj.init_plupload();
            }
        };


        this.init_plupload = function()
        {
            // Évènement de démarrage de l'upload (envoi des fichiers)
            let button_upload_id = (Math.random() + '').replace('0.', '');
            obj.element.attr("id", button_upload_id);


            // Initialisation de l'uploader
            params = {

                file_data_name : 'plupload_image[file]',
                multipart: true,
                multipart_params: {
                    '_http_accept': 'application/javascript'
                },
                browse_button : button_upload_id,
                url: obj.element.data('path'),
                runtimes : params.runtimes,
                max_file_size : params.max_file_size,
                flash_swf_url : params.flash_swf_url,
                silverlight_xap_url : params.silverlight_xap_url,
                filters : [
                    {title : "Fichiers images", extensions : "jpg,gif,png,jpeg"}
                ],

                init: {
                    FilesAdded: function(up, files) {

                        // On démarre l'upload
                        $('#alertUploadError').html('').hide();
                        obj.uploader.start();
                        up.refresh();

                    },

                    BeforeUpload: function(up, file) {

                        up.settings.multipart_params = { 'plupload_image[category]': $(selectors.modal.id).data('imLibrary'), 'plupload_image[title]': file.name };

                    },

                    UploadFile: function(up, file) {

                        // On ajoute un élément à la liste des images, avec une barre de progression
                        let new_item = '<li id="' + file.id + '" class="width-200">';
                        new_item += '<div class="info">'+file.name+'</div>';
                        new_item += '<div class="progress"><div class="progress-bar" style="width: 0;"></div></div>';
                        new_item += '</li>';

                        obj.progress.html(new_item);

                    },

                    UploadProgress: function(up, file) {

                        $('#' + file.id + " .progress").addClass('progress-striped').addClass('active');
                        $('#' + file.id + " .progress-bar").css('width', file.percent+"%");

                    },

                    UploadComplete: function(up, file) {

                        obj.progress.html('');

                    },

                    Error: function(upload, error) {

                        $('#alertUploadError').html(error.response).fadeIn();

                    },

                    FileUploaded: function(upload, file, response) {

                        imcore.modal.load_pictures();

                    }

                }

            };

            obj.uploader = new plupload.Uploader(params);
            obj.uploader.init();
            obj.element.data('plupload', obj.uploader);

        };

        this.init();
        return this.uploader;
    };


    $.fn.extend({

        ImageManager: function(options)
        {
            return this.each(function()
            {
                // Le launcher initie les comportements du widget
                // et transmet les bons paramètres au core
                let launcher = new IM_Launcher(this, options);
                $(this).data('launcher', launcher);

                if (launcher.config.imType=="gallery") {

                    $(launcher.config.imTarget).data('launcher', launcher);
                    $(launcher.config.imTarget).attr('rel', "im-manager");

                }

            });
        }
    });



    let IM_Core = function()
    {
        this.modal = new IM_Modal();
        this.uploader = new IM_Uploader();
    }


    let imcore = false;

    $(document).ready(function() {


        // Le core démarre l'uploader
        // Initie les comportements de base de la modal
        imcore = new IM_Core();

        $('.im-manager').each(function() {


            // PARAMETRES DISPONIBLES
            // - type [gallery|image|editor] : Type de manager
            // - target : Container pour afficher le/les images/vidéos attachées
            // - entity-class : Catégorie d'image à rechercher dans la library d'images
            // - entity-id : ID de l'entité à laquelle rattacher la ou les images
            // - route : Current route pour charger les crop disponibles
            $(this).ImageManager();

        });

    });



})(jQuery);

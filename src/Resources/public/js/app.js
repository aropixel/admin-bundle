/* ------------------------------------------------------------------------------
*
*  # Template JS core
*
*  Core JS file with default functionality configuration
*
*  Version: 1.1
*  Latest update: Oct 20, 2015
*
* ---------------------------------------------------------------------------- */
import {SwitchStatus} from '/bundles/aropixeladmin/js/module/switch-status/switch-status.js';
import {ModalDyn} from '/bundles/aropixeladmin/js/module/modal-dyn/modal-dyn.js';
import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';

onDomReady(() => {

    let $quillEditor = $('.quill-editor');
    if ($quillEditor.length) {
        $quillEditor.each(function() {
            activateQuillEditor($(this));
        });
    }

    // Make CKEDITO works with webpack AND asset mapper

    let $pickadate = $('.pickadate');
    if ($pickadate.length) {
        $pickadate.each(function() {
            activateDatePicker($(this));
        })
    }

    let $pickatime = $('.pickatime');
    if ($pickatime.length) {
        $pickatime.each(function() {
            activateTimePicker($(this));
        });
    }


    let switchStatus = document.querySelector('.form-status-switch .form-check-input');
    if (switchStatus) {

        let status = switchStatus.getAttribute('data-status-field') ?
            document.getElementById(switchStatus.getAttribute('data-status-field')) :
            document.querySelector(".form-status-switch input[type='hidden'][name$='[status]']")
        ;

        let activeValue = switchStatus.getAttribute('data-status-active-value') && switchStatus.getAttribute('data-status-active-value').length ?
            switchStatus.getAttribute('data-status-active-value') :
            'online'
        ;

        let unactiveValue = switchStatus.getAttribute('data-status-unactive-value') && switchStatus.getAttribute('data-status-unactive-value').length ?
            switchStatus.getAttribute('data-status-unactive-value') :
            'offline'
        ;

        let activeLabel = switchStatus.getAttribute('data-status-active-label') && switchStatus.getAttribute('data-status-active-label').length ?
            switchStatus.getAttribute('data-status-active-label') :
            'Publié'
        ;

        let unactiveLabel = switchStatus.getAttribute('data-status-unactive-label') && switchStatus.getAttribute('data-status-unactive-label').length ?
            switchStatus.getAttribute('data-status-unactive-label') :
            'Non publié'
        ;

        if (status && switchStatus) {

            let publicationFields = document.getElementById('publicationFields');
            let publishAtDate = publicationFields ? publicationFields.querySelector("input[name$='[publishAt][date]']") : null;
            let publishAtTime = publicationFields ? publicationFields.querySelector("input[name$='[publishAt][time]']") : null;
            let publishUntilDate = publicationFields ? publicationFields.querySelector("input[name$='[publishUntil][date]']") : null;
            let publishUntilTime = publicationFields ? publicationFields.querySelector("input[name$='[publishUntil][time]']") : null;

            new SwitchStatus(status, switchStatus, {
                'publishAtDate' : publishAtDate,
                'publishAtTime' : publishAtTime,
                'publishUntilDate' : publishUntilDate,
                'publishUntilTime' : publishUntilTime,
                stateLabels: {
                    'outdated' : 'Passé',
                    'published' : activeLabel,
                    'scheduled' : 'Programmé',
                    'offline' : unactiveLabel,
                },
                'stateValues': {
                    'offline' : unactiveValue,
                    'online' : activeValue,
                }
            })
        }

    }


    let passWordUser = document.querySelector('[data-gdpr]');
    if (passWordUser) {

        let gdprform = passWordUser.closest('form');
        new GdprPassword(passWordUser, {form: gdprform});

    }



    let $bsSelect = $('.bootstrap-select');
    if ($bsSelect.length) {

        $bsSelect.select2({
            width: '100%',
            minimumResultsForSearch: -1
        });

    }


    let $colorPicker = $('.color-picker');
    if ($colorPicker.length) {

        $colorPicker.spectrum({
            showInput: true
        });

    }



    // HTML5 editor
    $('.html5-editor').each(function() {
        $(this).wysihtml5({
            parserRules:  wysihtml5ParserRules,
            "font-styles": false, //Font styling, e.g. h1, h2, etc. Default true
            "emphasis": true, //Italics, bold, etc. Default true
            "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
            "html": true, //Button which allows you to edit the generated HTML. Default false
            "link": true, //Button to insert a link. Default true
            "image": false, //Button to insert an image. Default true,
            "color": false //Button to change color of font
        });
    });



    // Basic functionality
    $(".sortable tbody").each(function() {

        let _sortable_element = $(this);
        _sortable_element.sortable({
            opacity: 0.7,
            // 'helper': fixHelper,
            update : function ()
            {
                let _path = _sortable_element.parent().attr('data-path') ? _sortable_element.parent().attr('data-path') : window.location.href;
                //
                $.post(_path, $(this).sortable('serialize'),
                    function(answer)
                    {
                        _sortable_element.find("tr").each(function(i) {
                            $(this).find('td.numero').html((i+1)+".");
                        });
                    }
                );

            }

        }).disableSelection()
    });




    $(".select-multiple").not('.select2-container').each(function() {

        if ($(this).parent('.duallistbox').length) {

        }
        else {
            $(this).select2({
                'closeOnSelect': false
            });
        }

    });



    function initializeSelect2(_select) {

        let params = {};
        let width = _select.prop('style')['width'];
        if (width.length) {
            params.width = width;
        }

        if (_select[0].hasAttribute("data-placeholder")) {
            params.allowClear = true;
        }

        // Bugfix select2 in modal
        let modal = _select.closest('.modal');
        if (modal.length > 0) {
            params.dropdownParent = modal;
        }

        _select.select2(params);
    }


    function initializeSelect2Ajax(_select) {

        let _url = _select.attr('data-url');
        let _placeholder = _select.attr('data-placeholder');
        let _multiple = _select.attr('data-multiple') ? true : false;

        let params = {
            multiple: _multiple,
            ajax: {
                url: _url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            //   minimumInputLength: 1,
            templateResult: formatRepo, // omitted for brevity, see the source of this page
            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
        };


        if (_select[0].hasAttribute("data-placeholder")) {
            params.placeholder = _placeholder;
            params.allowClear = true;
        } else {
            params.placeholder = "";
        }

        // Bugfix select2 in modal
        let modal = _select.closest('.modal');
        if (modal.length > 0) {
            params.dropdownParent = modal;
        }

        _select.select2(params);
    }


    $(".select2-ajax").each(function() {

        initializeSelect2Ajax($(this));

    });


    $(".select2").not('.select2-container').each(function() {

        initializeSelect2($(this));

    });


    function resizePreview(spanPreview) {

        let width = spanPreview.outerWidth();
        let height = width * 9 / 16;
        spanPreview.height(height);

    }

    let $videos = $('.video-type');
    if ($videos.length) {

        // Enable Select2 select for the length option
        $videos.each(function() {

            $(this).find('.preview').fitVids();
            $(this).on('keyup', 'textarea', function() {

                let srcArea = $(this).val().match(/<iframe.*? src="([^"]+)".*?>.*?<\/iframe>/g);
                let $preview = $(this).closest('.video-type').find('.preview');
                let $iframe = $preview.find('iframe');

                if (srcArea) {
                    if ($iframe) {
                        let srcPreview = $preview.html().match(/<iframe.*? src="([^"]+)".*?>.*?<\/iframe>/g);
                        if (!srcPreview || srcPreview[1] != srcArea[1]) {
                            $preview.html($(this).val());
                            $preview.fitVids();
                        }
                    }
                }
                else {
                    $preview.empty().append('<span></span>');
                    resizePreview($preview.find('span'));
                }

            });
            $(this).find('textarea').trigger('keyup');

        });


        $( window ).resize(function() {
            $('.video-type .preview span').each(function() {
                resizePreview($(this));
            });
        });

    }




    let $form = $('form[data-form="form"]');
    let $submitForm = $('button[data-form="submit"]');

    if ($form.length) {

        $form.attr('novalidate', 'novalidate');
        if ($submitForm.length) {

            $form.submit(function(e) {

                let $requiredFields = $form.find('[required="required"]');

                let hasError = false;
                let messageField = '<div class="alert alert-form show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Ce champ est obligatoire.</div></div>';
                let messageBox = '<div class="alert alert-danger show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Un ou plusieurs champs obligatoires sont manquants.</div></div>';

                let tabLabels = [];
                $requiredFields.each(function() {

                    let $formGroup = $(this).closest('.form-group');

                    if (!$(this).val().length) {

                        hasError = true;
                        if ($formGroup.find('.alert').length === 0) {

                            let panelId = $formGroup.closest('.tab-pane') ? $formGroup.closest('.tab-pane').attr('id') : null;
                            if (panelId) {
                                let panelLabel = $("[href$='#" + panelId.replace(/(:)/g, "\\$1") + "']").text();
                                panelLabel = '<a href="#" data-error-panel="#' + panelId +'">' + panelLabel + '</a>';

                                if (panelLabel) {
                                    if (!tabLabels.includes(panelLabel)) {
                                        tabLabels.push(panelLabel);
                                    }
                                }
                            }

                            $formGroup.append(messageField);
                        }

                    }

                });

                let navItem = $('.header-bottom .tabbable-header .nav-item');

                if (navItem.length > 1 && tabLabels.length) {
                    if (tabLabels.length === 1) {
                        messageBox = '<div class="alert alert-danger show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Un ou plusieurs champs obligatoires sont manquants dans l\'onglet ' + tabLabels[0] + '.</div></div>';
                    } else {
                        messageBox = '<div class="alert alert-danger show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Un ou plusieurs champs obligatoires sont manquants dans les onglets ' + tabLabels.join(", ") + '.</div></div>';
                    }
                }


                if (hasError) {

                    if ($form.prev('.alert').length === 0) {
                        $form.before(messageBox);
                    }

                    e.preventDefault();

                }

            });



        }

    }



    $('#delete_button').click(function() {

        let _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : 'btn-danger',
                'callback' : function() {
                    $('#delete_button').closest('form').submit();
                    $(this).closest('.modal').fadeOut('300', function() { $(this).remove(); });
                },
            }
        }


        let message = 'Voulez-vous vraiment <strong>supprimer ce contenu</strong> ?<br /> \
                        Le contenu sera définitivement supprimé, il ne sera plus possible de le récupérer.';

        new ModalDyn("Supprimer un contenu", message, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});


    });



    // Override defaults
    // ------------------------------


    $(".main-content").on('click', 'a.confirm[data-confirm]', function() {

        let _button = $(this);

        let _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : 'btn-danger',
                'callback' : function() {
                    window.location.href = _button.attr("data-path");
                },
            }


        }

        let me_data = _button.data('confirm');

        let me_title = "Confirmation";
        let me_description = me_data;

        me_data = me_data.split("|");
        if (me_data.length > 1) {
            me_title = me_data[0];
            me_description = me_data[1];
        }

        new ModalDyn(me_title, me_description, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

    });



    $(".main-content").on('click', 'a.delete[data-confirm]', function() {

        let _button = $(this);

        let _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : 'btn-danger',
                'callback' : function() {
                    let $btnGroup = _button.closest('.btn-group');
                    let $form = $btnGroup.find('form');
                    $form.submit();

                },
            }


        }

        let me_data = _button.data('confirm');

        let me_title = "Confirmation";
        let me_description = me_data;

        me_data = me_data.split("|");
        if (me_data.length > 1) {
            me_title = me_data[0];
            me_description = me_data[1];
        }

        new ModalDyn(me_title, me_description, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});




    });




    $(".main-content").on('click', 'a.status[data-confirm]', function() {

        let _button = $(this);
        let _btn_group = _button.closest('.dropdown-menu');
        let _state_icon = $(this).closest('tr').find('.state-icon');
        let state = _state_icon.hasClass('state-icon--offline') ? 'offline' : 'online';
        let _modalBgClass = (state === 'online' ? 'bg-default' : 'bg-primary');
        let _buttonValidClass = (state === 'online' ? 'btn-default' : 'btn-primary');
        let _message = _button.data('confirm').replace('%s', state === 'online' ? 'hors ligne' : 'en ligne');

        let _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : _buttonValidClass,
                'callback' : function() {

                    let suffix = document.URL.slice(-1) === '/' ? '' : '/';
                    let url = _button.attr("data-path") ? _button.attr("data-path") : document.URL+suffix+"state";
                    let button = $(this);

                    button.attr('disabled', 'disabled');

                    $.get(url, function(answer) {
                        if (answer === 'OK') {

                            _state_icon
                                .removeClass(state === 'online' ? 'state-icon--online' : 'state-icon--offline')
                                .addClass(state !== 'online' ? 'state-icon--online' : 'state-icon--offline')
                                .attr('title', state === 'online' ? 'hors ligne' : 'en ligne')
                                .attr('data-bs-original-title', state === 'online' ? 'hors ligne' : 'en ligne');

                            _btn_group.find('.status').html('<i class="fas fa-toggle-on"></i> ' + (state === 'online' ? 'Mettre en ligne' : 'Mettre hors ligne'));

                            button.removeAttr('disabled');
                            button.closest('.modal').modal('hide');
                            button.closest('.modal').on('hidden.bs.modal', function (e) {
                                $(this).remove();
                            });

                        }
                    });

                }

            },
        }

        new ModalDyn("Confirmation", _message, _buttons, {modalClass: 'modal_mini', headerClass: _modalBgClass});

    });



    $(".main-content").on('click', 'a.delete-out[data-confirm]', function() {

        let _button = $(this);

        let _buttons = {

            "Annuler": function () {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class': 'btn-danger',
                'callback': function () {

                    if (_button.data('rel')) {
                        $('.forms-out[rel="' + _button.data('rel') + '"] form[action$="' + _button.data('id') + '"]').submit();
                    } else {
                        $('.forms-out form[action$="' + _button.data('id') + '"]').submit();
                    }


                },
            }


        }

        new ModalDyn("Confirmation", _button.attr('data-confirm'), _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});


    });


    $('.main-content').on('click', '[data-form-collection-add]', function(e) {

        e.preventDefault();

        const $addButton = $(this);
        const collectionId = $addButton.attr('data-form-collection-add');
        const $collection = $('#' + collectionId.replace(/(:)/g, "\\$1"));
        const $list = $collection.find('> [data-form-collection="list"]');
        const prototype = $collection.attr('data-prototype');

        // Récupérer l'index le plus élevé pour éviter les doublons même si on supprime des éléments
        let index = $collection.data('index') || $list.find('[data-form-collection="item"]').length;

        // Remplacement du prototype
        const newForm = prototype.replace(/__name__/g, index);

        // Mise à jour de l'index pour le prochain ajout
        $collection.data('index', index + 1);

        // Ajout au DOM
        const $newElement = $(newForm);
        $list.append($newElement);

        // Initialisation des plugins sur le nouvel élément
        activateDatePicker($newElement.find('.pickadate'));
        activateTimePicker($newElement.find('.pickatime'));
        activateQuillEditor($newElement.find('.quill-editor'));
        activateImManager($newElement.find('.im-manager'));
        activateSortable($list);

        // Initialize Select2 using global helper functions
        $newElement.find(".select2-ajax, .select2, .select-multiple").each(function() {
            const $select = $(this);
            if ($select.hasClass('select2-ajax')) {
                if (!$select.next().hasClass('select2-container')) {
                    initializeSelect2Ajax($select);
                }
            } else if ($select.hasClass('select-multiple')) {
                if (!$select.parent('.duallistbox').length && !$select.next().hasClass('select2-container')) {
                    initializeSelect2($select);
                }
            } else {
                if (!$select.next().hasClass('select2-container')) {
                    initializeSelect2($select);
                }
            }
        });

        // Initialize translatable fields
        const currentLocale = $('#btnCurrentLocale').text().trim().toLowerCase();
        if (currentLocale) {
            $newElement.find('.translatable-field').each(function() {
                const $field = $(this);
                if ($field.attr('data-locale') === currentLocale) {
                    $field.addClass('active');
                } else {
                    $field.removeClass('active');
                }
            });
        }

        // Mise à jour de la position si nécessaire
        $newElement.find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            if (name && name.endsWith('[position]')) {
                const count = $list.find('[data-form-collection="item"]').length;
                $(this).val(count);
            }
        });

        // Ouverture automatique de l'offcanvas pour le nouvel élément
        if ($collection.data('use-offcanvas')) {
            $newElement.trigger('click');
        }

    });

    $('[data-form-collection="list"]').each(function() {
        $(this).on('click', '[data-form-collection="delete"]', function() {
            $(this).closest('[data-form-collection="item"]').remove();
        });
        activateSortable($(this));
    });

    const offcanvasElement = document.getElementById('offcanvasCollection');
    let bsOffcanvas = null;
    if (offcanvasElement) {
        bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
    }

    $(document).on('click', '[data-form-collection-edit]', function(e) {

        // Si on a cliqué sur un bouton d'action spécifique (supprimer, déplacer, etc.), on ne fait rien ici
        if ($(e.target).closest('[data-form-collection="delete"], [data-form-collection="move"], .dropdown-toggle').length) {
            return;
        }

        e.preventDefault();
        const $item = $(this).closest('[data-form-collection="item"]');
        const $collection = $item.closest('[data-form-type="collection"]');
        const $container = $item.find('.collection-form-container');
        const formTitle = $collection.attr('data-form-title') || 'Edition';

        if (bsOffcanvas) {
            $('#offcanvasCollectionLabel').text(formTitle);
            const $offcanvasBody = $('#offcanvasCollection .offcanvas-body');

            // Move form to offcanvas
            $offcanvasBody.empty().append($container.contents());
            $container.data('active-item', $item);

            bsOffcanvas.show();

            // Initialize plugins in offcanvas
            activateDatePicker($offcanvasBody.find('.pickadate'));
            activateTimePicker($offcanvasBody.find('.pickatime'));
            activateQuillEditor($offcanvasBody.find('.quill-editor'));
            activateImManager($offcanvasBody.find('.im-manager'));

            $offcanvasBody.find(".select2-ajax, .select2, .select-multiple").each(function() {
                const $select = $(this);
                if ($select.hasClass('select2-ajax')) {
                    initializeSelect2Ajax($select);
                } else if ($select.hasClass('select-multiple')) {
                    if (!$select.parent('.duallistbox').length) {
                        initializeSelect2($select);
                    }
                } else {
                    initializeSelect2($select);
                }
            });
        }
    });

    // Gestion de la suppression depuis l'offcanvas
    $(document).on('click', '#offcanvasCollection [data-form-collection="delete"]', function(e) {
        e.preventDefault();

        const _button = $(this);
        const _buttons = {
            confirm: {
                label: 'Supprimer',
                className: 'btn-danger',
                callback: function(){

                    const $item = $('.collection-form-container').filter(function() {
                        return $(this).contents().length === 0;
                    }).closest('[data-form-collection="item"]');

                    if ($item.length) {
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                        $item.remove();
                    }
                }
            },
            cancel: {
                label: 'Annuler',
                className: 'btn-default',
                callback: function(){
                }
            }
        };

        new ModalDyn("Confirmation", "Êtes-vous sûr de vouloir supprimer cet élément ?", _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});

    });

    if (offcanvasElement) {
        offcanvasElement.addEventListener('hidden.bs.offcanvas', function () {
            const $offcanvasBody = $(this).find('.offcanvas-body');
            const $item = $('.collection-form-container').filter(function() {
                return $(this).contents().length === 0;
            }).first();

            if ($item.length) {
                $item.append($offcanvasBody.contents());
            }
        });
    }

    $(document).on('input change', '[data-form-collection="item"] :input, #offcanvasCollection :input', function (e) {
        const inputName = $(this).attr('name');
        let newValue;

        if ($(this).is('select')) {
            newValue = $(this).find('option:selected').text();
        } else {
            newValue = $(this).val();
        }

        let $item;

        if ($(this).closest('#offcanvasCollection').length) {
            // Find the item that is currently being edited
            $item = $('.collection-form-container').filter(function() {
                return $(this).contents().length === 0;
            }).closest('[data-form-collection="item"]');
        } else {
            $item = $(this).closest('[data-form-collection="item"]');
        }

        if ($item && $item.length) {
            const $label = $item.find(`.modal-collection-display-label[data-display-field-name="${inputName}"]`);
            if ($label.length) {
                if (!newValue) {
                    const defaultLabel = $label.attr('data-display-field-default') || 'Nouveau';
                    $label.html(`<em class="text-muted">${defaultLabel}</em>`);
                } else {
                    $label.text(newValue);
                }
            }
        }
    });

});


function initializeSelect2($select) {
    let params = {};
    let width = $select.prop('style')['width'];
    if (width && width.length) {
        params.width = width;
    }
    if ($select[0].hasAttribute("data-placeholder")) {
        params.allowClear = true;
    }
    $select.select2(params);
}

function initializeSelect2Ajax($select) {
    let _url = $select.attr('data-url');
    let _placeholder = $select.attr('data-placeholder');
    let _multiple = $select.attr('data-multiple') ? true : false;

    let params = {
        multiple: _multiple,
        ajax: {
            url: _url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    };

    if ($select[0].hasAttribute("data-placeholder")) {
        params.placeholder = _placeholder;
        params.allowClear = true;
    } else {
        params.placeholder = "";
    }

    $select.select2(params);
}

function formatRepo (repo) {

    if (repo.loading) return repo.text;

    return repo.full_name || repo.text;
}

function formatRepoSelection (repo) {
    return repo.full_name || repo.text;
}


function activateDatePicker($element) {

    if (!$element.length) {
        return false;
    }

    $element.pickadate({
        monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        today: 'aujourd\'hui',
        firstDay : 1,
        clear: 'effacer',
        close: 'fermer',
        selectMonths: true,
        selectYears: 20,
        labelMonthNext: 'Mois suivant',
        labelMonthPrev: 'Mois précédent',
        format: 'dd/mm/yyyy',
        formatSubmit: 'yyyy-mm-dd',
        onOpen: function() {

            moment.locale('fr');
            let selected = $(this)[0].get('select');
            let dateMoment = selected ? moment(selected.obj) : moment();
            let dayName = dateMoment.format('dddd')[0].toUpperCase() + dateMoment.format('dddd').slice(1);

            let displayDate = '<div class="picker__date-display"><div class="picker__weekday-display">'+dayName+'</div><div class="picker__month-display"><div>'+dateMoment.format('MMM')+'</div></div><div class="picker__day-display"><div>'+dateMoment.format('D')+'</div></div><div class="picker__year-display"><div>'+dateMoment.format('YYYY')+'</div></div></div>';
            $(this)[0].$root.find( '.picker__wrap .picker__date-display' ).remove();
            $(this)[0].$root.find( '.picker__wrap' ).prepend(displayDate);
            $(this)[0].render();

        }
    });
}

function activateTimePicker($element) {

    if (!$element.length) {
        return false;
    }

    $element.clockpicker({
        amOrPm: false,
        cleartext: 'Effacer',
        donetext: 'Valider'
    });
}


function activateQuillEditor($elements) {

    // On peut enregistrer des barres d'outils personnalisées via window.aropixelQuillToolbars
    const defaultToolbars = {
        'full': [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean'],
            ['link', 'image', 'video']
        ],
        'simple': [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'clean']
        ]
    };

    $elements.each(function() {
        let $this = $(this);
        let targetSelector = $this.data('target');
        let toolbarType = $this.data('toolbar');
        let $target = $(targetSelector.replace(/(:)/g, "\\$1"));

        // Récupérer les barres d'outils personnalisées de l'utilisateur
        const customToolbars = window.aropixelQuillToolbars || {};
        const allToolbars = { ...defaultToolbars, ...customToolbars };

        // Déterminer les options de la barre d'outils
        let toolbarOptions = allToolbars[toolbarType] || allToolbars['full'];

        // Si toolbarType ressemble à du JSON, on essaie de le parser
        if (typeof toolbarType === 'string' && toolbarType.startsWith('[') && toolbarType.endsWith(']')) {
            try {
                toolbarOptions = JSON.parse(toolbarType);
            } catch (e) {
                console.error('Erreur lors du parsing de la barre d\'outils Quill custom :', e);
            }
        }

        // Éviter la double initialisation
        if ($this.data('quill-initialized')) {
            return;
        }

        let quill = new Quill($this[0], {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });

        $this.data('quill-initialized', true);

        // Surcharge du handler image pour utiliser l'ImageManager si possible
        if (quill.getModule('toolbar')) {
            quill.getModule('toolbar').addHandler('image', function() {
                if (window.initImageManager) {
                    $target.attr('data-im-type', 'editor');
                    window.initImageManager($target[0], {
                        editor: quill,
                        category: $target.attr('data-class'),
                        attach_path: $target.attr('data-attach-path')
                    });
                } else {
                    // Fallback to default image handler if initImageManager is not available
                    const range = this.quill.getSelection();
                    const value = prompt('Veuillez entrer l\'URL de l\'image :');
                    if (value) {
                        this.quill.insertEmbed(range.index, 'image', value, Quill.Sources.USER);
                    }
                }
            });
        }

        // Initialize content
        quill.root.innerHTML = $target.val();

        quill.on('text-change', function() {
            $target.val(quill.root.innerHTML);
        });
    });
}


function activateImManager($elements) {

    $elements.each(function() {
        $(this).ImageManager();
    })

}


function activateSortable($container) {
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $container.sortable({
        helper: fixHelper,
        items: '[data-form-collection="item"]',
        handle: '[data-form-collection="move"]',
        start: function (event, ui)
        {
            $container.find('[data-form-collection="item"] textarea').each(function(iItem) {

                if ($(this).hasClass('d-none') && $(this).next().hasClass('quill-editor')) {
                    $(this).next().empty();
                    $(this).next().data('quill-initialized', false);
                }

            });
        },
        stop: function (event, ui)
        {
            $container.find('[data-form-collection="item"] .quill-editor').each(function(iItem) {

                activateQuillEditor($(this));

            });
        },
        update : function ()
        {
            let level = $container.parents('[data-form-collection="item"]').length * 2;
            $container.find('[data-form-collection="item"]').each(function(iItem) {

                let _inputs = $(this).find('input, select, textarea');
                _inputs.each(function() {
                    if (!$(this).attr('name')) {
                        return;
                    }

                    let re = /(\[[0-9]+\])/g;
                    let old_name = $(this).attr('name');

                    let splitted = old_name.split(re);
                    splitted[level+1] = '[' + iItem + ']';
                    let new_name = splitted.join('');


                    $(this).attr('name', new_name);


                    if (new_name.endsWith('[position]')) {
                        $(this).val(iItem + 1);
                    }


                    // let new_id = new_name.replace(/[\[\]]+/g,'_');
                    // new_id = new_id.substring(0, new_id.length - 1);
                    // $(this).attr('id', new_id);


                });

                $(this).attr('data-form-collection-index', iItem);

            })


        }
    });

}


document.addEventListener("click", function(e){

    let panelId = e.target.getAttribute('data-error-panel');
    if (panelId) {
        document.querySelector('[href="' + panelId + '"]').click();
    }

});

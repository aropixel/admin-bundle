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
import {SwitchStatus} from './module/switch-status/switch-status.js';
import {ModalDyn} from './module/modal-dyn/modal-dyn.js';

$(function() {


    moment.locale('fr', {
        months : 'janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'.split('_'),
        monthsShort : 'janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'.split('_'),
        monthsParseExact : true,
        weekdays : 'dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'.split('_'),
        weekdaysShort : 'dim._lun._mar._mer._jeu._ven._sam.'.split('_'),
        weekdaysMin : 'Di_Lu_Ma_Me_Je_Ve_Sa'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay : '[Aujourd’hui à] LT',
            nextDay : '[Demain à] LT',
            nextWeek : 'dddd [à] LT',
            lastDay : '[Hier à] LT',
            lastWeek : 'dddd [dernier à] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'dans %s',
            past : 'il y a %s',
            s : 'quelques secondes',
            m : 'une minute',
            mm : '%d minutes',
            h : 'une heure',
            hh : '%d heures',
            d : 'un jour',
            dd : '%d jours',
            M : 'un mois',
            MM : '%d mois',
            y : 'un an',
            yy : '%d ans'
        },
        dayOfMonthOrdinalParse : /\d{1,2}(er|e)/,
        ordinal : function (number) {
            return number + (number === 1 ? 'er' : 'e');
        },
        meridiemParse : /PD|MD/,
        isPM : function (input) {
            return input.charAt(0) === 'M';
        },
        // In case the meridiem units are not separated around 12, then implement
        // this function (look at locale/id.js for an example).
        // meridiemHour : function (hour, meridiem) {
        //     return /* 0-23 hour, given meridiem token and hour 1-12 */ ;
        // },
        meridiem : function (hours, minutes, isLower) {
            return hours < 12 ? 'PD' : 'MD';
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // Used to determine first week of the year.
        }
    });


    // ========================================
    //
    // Other code
    //
    // ========================================


    let $formSlug = $('#setslug');
    if ($formSlug.length) {

        // $formSlug.editable({
        //     name: 'slug',
        //     emptytext: "Aucun",
        //     success: function(response, newValue) {
        //         $("#setslug").parent().find('input:hidden').val(newValue);
        //     }
        // });

    }

    let $pickadate = $('.pickadate');
    if ($pickadate.length) {

        $pickadate.each(function() {
            activateDatePicker($(this));
        })

    }



    let $pickatime = $('.pickatime');

    if ($pickatime.length) {

        // $pickatime.pickatime({
        //     clear: 'Effacer',
        //     format: 'HH:i',
        //     formatLabel: 'HH:i',
        //     formatSubmit: 'H:i',
        //     interval: 15,
        //     hiddenName: true
        // });
        $pickatime.each(function() {
            activateTimePicker($(this));
        });
        // $pickatime.clockpicker({
        //     amOrPm: false,
        //     cleartext: 'Effacer',
        //     donetext: 'Valider'
        // });

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

            let offcanvas = document.querySelector('.offcanvas-body');
            let publishAtDate = offcanvas ? offcanvas.querySelector("input[name$='[publishAt][date]']") : null;
            let publishAtTime = offcanvas ? offcanvas.querySelector("input[name$='[publishAt][time]']") : null;
            let publishUntilDate = offcanvas ? offcanvas.querySelector("input[name$='[publishUntil][date]']") : null;
            let publishUntilTime = offcanvas ? offcanvas.querySelector("input[name$='[publishUntil][time]']") : null;

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

    //$(".sortable").sortable();






    // Table setup
    // ------------------------------

    // Setting datatable defaults
    $.extend( $.fn.dataTable.defaults, {
        autoWidth: false,
        columnDefs: [
            {
                orderable: false,
                targets: [ 'no-sort' ]
            },
            {
                type: 'date-euro',
                targets: [ 'date-euro' ]
            },
            {
                className: 'position',
                targets: [ 'position' ]
            }
        ],
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        language: {
            search: '<span>Filtrer :</span> _INPUT_',
            lengthMenu: '<span>Nombre par page:</span> _MENU_',
            emptyTable: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            info: "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            infoEmpty: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            infoFiltered: "(sur _MAX_ &eacute;l&eacute;ments disponibles)",
            zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            paginate: { 'first': 'Premier', 'last': 'Dernier', 'next': '&rarr;', 'previous': '&larr;' }
        },
        drawCallback: function () {
            //$(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            $(this).closest('.dataTables_wrapper').unblock();
            // $("[data-modal-xeditable]").editable();
        },
        preDrawCallback: function() {
            //$(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        }
    });

    //
    // Pipelining function for DataTables. To be used to the `ajax` option of DataTables
    //
    $.fn.dataTable.pipeline = function ( opts ) {
        // Configuration options
        let conf = $.extend( {
            pages: 5,     // number of pages to cache
            url: '',      // script url
            data: null,   // function or object with parameters to send to the server
                          // matching how `ajax.data` works in DataTables
            method: 'GET' // Ajax HTTP method
        }, opts );

        // Private variables for storing the cache
        let cacheLower = -1;
        let cacheUpper = null;
        let cacheLastRequest = null;
        let cacheLastJson = null;

        return function ( request, drawCallback, settings ) {
            let ajax          = false;
            let requestStart  = request.start;
            let drawStart     = request.start;
            let requestLength = request.length;
            let requestEnd    = requestStart + requestLength;

            if ( settings.clearCache ) {
                // API requested that the cache be cleared
                ajax = true;
                settings.clearCache = false;
            }
            else if ( cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper ) {
                // outside cached data - need to make a request
                ajax = true;
            }
            else if ( JSON.stringify( request.order )   !== JSON.stringify( cacheLastRequest.order ) ||
                JSON.stringify( request.columns ) !== JSON.stringify( cacheLastRequest.columns ) ||
                JSON.stringify( request.search )  !== JSON.stringify( cacheLastRequest.search )
            ) {
                // properties changed (ordering, columns, searching)
                ajax = true;
            }

            // Store the request for checking next time around
            cacheLastRequest = $.extend( true, {}, request );

            if ( ajax ) {
                // Need data from the server
                if ( requestStart < cacheLower ) {
                    requestStart = requestStart - (requestLength*(conf.pages-1));

                    if ( requestStart < 0 ) {
                        requestStart = 0;
                    }
                }

                cacheLower = requestStart;
                cacheUpper = requestStart + (requestLength * conf.pages);

                request.start = requestStart;
                request.length = requestLength*conf.pages;

                // Provide the same `data` options as DataTables.
                if ( $.isFunction ( conf.data ) ) {
                    // As a function it is executed with the data object as an arg
                    // for manipulation. If an object is returned, it is used as the
                    // data object to submit
                    let d = conf.data( request );
                    if ( d ) {
                        $.extend( request, d );
                    }
                }
                else if ( $.isPlainObject( conf.data ) ) {
                    // As an object, the data given extends the default
                    $.extend( request, conf.data );
                }

                $(this).closest('.dataTables_wrapper').block({
                    message: '<i class="fas fa-spinner fa-spin"></i>',
                    overlayCSS: {
                        backgroundColor: '#fff',
                        opacity: 0.9,
                        cursor: 'wait'
                    },
                    css: {
                        border: 0,
                        padding: 0,
                        backgroundColor: 'none'
                    }
                });

                settings.jqXHR = $.ajax( {
                    "type":     conf.method,
                    "url":      conf.url,
                    "data":     request,
                    "dataType": "json",
                    "cache":    false,
                    "success":  function ( json ) {
                        cacheLastJson = $.extend(true, {}, json);

                        if ( cacheLower != drawStart ) {
                            json.data.splice( 0, drawStart-cacheLower );
                        }
                        json.data.splice( requestLength, json.data.length );

                        drawCallback( json );
                    }
                } );
            }
            else {
                let json = $.extend( true, {}, cacheLastJson );
                json.draw = request.draw; // Update the echo for each response
                json.data.splice( 0, requestStart-cacheLower );
                json.data.splice( requestLength, json.data.length );

                drawCallback(json);
            }
        }
    };

    // Register an API method that will empty the pipelined data, forcing an Ajax
    // fetch on the next draw (i.e. `table.clearPipeline().draw()`)
    $.fn.dataTable.Api.register( 'clearPipeline()', function () {
        return this.iterator( 'table', function ( settings ) {
            settings.clearCache = true;
        } );
    } );


    // AJAX sourced data
    $('.datatable-ajax').each(function() {

        let _suffix = document.URL.slice(-1)=='/' ? '' : '/';
        let _src = $(this).data('src') ? $(this).data('src') : document.URL+_suffix+"dataTable.json";
        let _params = {
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": $.fn.dataTable.pipeline( {
                url: _src,
                pages: 5 // number of pages to cache
            } )
        };

        if ($(this).data("order-column"))
        {
            let _column = $(this).data("order-column") - 1;
            let _direction = 'asc';
            if ($(this).data("order-direction")) 	_direction = $(this).data("order-direction");

            _params["order"] = [[ _column, _direction ]];
        }

        $(this).dataTable(_params);

    })



    // Basic datatable
    $('.datatable').each(function() {

        let _params = {}
        if ($(this).data("order-column"))
        {
            let _column = $(this).data("order-column") - 1;
            let _direction = 'asc';
            if ($(this).data("order-direction")) 	_direction = $(this).data("order-direction");

            _params["order"] = [[ _column, _direction ]];
        }

        $(this).DataTable(_params);

    });


    // External table additions
    // ------------------------------

    // Add placeholder to the datatable filter option
    $('.dataTables_filter input[type=search]').attr('placeholder','Taper pour filtrer...');


    // Enable Select2 select for the length option
    // $('.dataTables_length select').select2({
    //     minimumResultsForSearch: Infinity,
    //     width: 'auto'
    // });



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
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count
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
                let messageField = '<div class="alert alert-form show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Ce champs est obligatoire.</div></div>';
                let messageBox = '<div class="alert alert-danger show fade"><div class="alert-body"><span class="text-semibold">Attention !</span> Un ou plusieurs champs obligatoires sont manquants.</div></div>';

                $requiredFields.each(function() {

                    let $formGroup = $(this).closest('.form-group');

                    // if (!$(this).hasClass('picker__input') && !$(this).val().length) {
                    if (!$(this).val().length) {

                        hasError = true;
                        if ($formGroup.find('.alert').length == 0) {
                            $formGroup.append(messageField);
                        }

                    }

                });

                if (hasError) {

                    if ($form.prev('.alert').length == 0) {
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
        let _state_icon = $(this).closest('tr').find('.img-state-icon');
        let state = _state_icon.hasClass('img-state-icon--offline') ? 'offline' : 'online';
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
                                .removeClass(state === 'online' ? 'img-state-icon--online' : 'img-state-icon--offline')
                                .addClass(state !== 'online' ? 'img-state-icon--online' : 'img-state-icon--offline');

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


    $('.main-content').on('click', '[data-form-collection-add]', function() {

        let $collection = $('#'+$(this).attr('data-form-collection-add'));
        let pattern_id_replace = $(this).attr('data-form-prototype-id-replace');
        let pattern_name_replace = $(this).attr('data-form-prototype-name-replace');

        let $list = $collection.find('> [data-form-collection="list"]');
        let $items = $list.find('> [data-form-collection="item"]');

        let count = $items.length + 1;
        // $items.attr('data-form-collection-index', count);
        let prototype = $collection.attr('data-prototype');

        if (pattern_id_replace) {

            let replace_value = pattern_id_replace.replace(/__name__/g, count);

            let re = new RegExp(pattern_id_replace,"g");
            prototype = prototype.replace(re, replace_value);

        }
        else {
            prototype = prototype.replace(/__name__/g, count);
        }

        if (pattern_name_replace) {

            pattern_name_replace = pattern_name_replace.replace(/\[/g, '\\[');
            pattern_name_replace = pattern_name_replace.replace(/]/g, '\\]');

            let replace_value = pattern_name_replace.replace(/__name__/g, count);
            replace_value = replace_value.replace(/\\\[/g, '[')
            replace_value = replace_value.replace(/\\]/g, ']')

            let re = new RegExp(pattern_name_replace,"g");
            prototype = prototype.replace(re, replace_value);

        }
        else {
            prototype = prototype.replace(/__name__/g, count);
        }


        $list.append(prototype);
        $list.find('> [data-form-collection="item"]:nth-child('+count+')').attr('data-form-collection-index', count);


        activateDatePicker($list.find('> [data-form-collection="item"]:nth-child('+count+') .pickadate'));
        activateTimePicker($list.find('> [data-form-collection="item"]:nth-child('+count+') .pickatime'));
        activateCkeditor($list.find('> [data-form-collection="item"]:nth-child('+count+') .ckeditor'));
        activateImManager($list.find('> [data-form-collection="item"]:nth-child('+count+') .im-manager'));
        activateSortable($list.find('> [data-form-collection="list"]'));
        // $list.find('> [data-form-collection="item"]:nth-child('+count+') .bootstrap-select').selectpicker({
        //     autoWidth: false
        // });


        // re-init select2 for new select 2 item in collection
        $(".select2-ajax").each(function() {

            if (!$(this).next().length || !$(this).next().hasClass('select2-container')) {
                initializeSelect2Ajax($(this));
            }

        });

        $(".select2").not('.select2-container').each(function() {

            initializeSelect2($(this));

        });

        $(".select-multiple").not('.select2-container').each(function() {

            if ($(this).parent('.duallistbox').length) {

            }
            else {
                initializeSelect2($(this));
            }
        });


    });

    $('[data-form-collection="list"]').each(function() {
        $(this).on('click', '[data-form-collection="delete"]', function() {
            $(this).closest('[data-form-collection="item"]').remove();
        });
        activateSortable($(this));

    });

});


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


function activateCkeditor($elements) {

    $elements.each(function() {
        console.log($(this));
        CKEDITOR.replace( this.id );
    })

}


function activateImManager($elements) {

    $elements.each(function() {
        $(this).ImageManager();
    })

}


function activateSortable($container) {

    $container.sortable({
        items: '> [data-form-collection="item"]',
        handle: '[data-form-collection="move"]',
        start: function (event, ui)
        {
            $container.find('> [data-form-collection="item"] textarea').each(function(iItem) {

                for(name in CKEDITOR.instances)
                {
                    if (this.id == name) {
                        CKEDITOR.instances[name].destroy();
                    }
                }

            });
        },
        stop: function (event, ui)
        {
            $container.find('> [data-form-collection="item"] textarea.ckeditor').each(function(iItem) {

                let id_textarea = $(this).attr("id");
                CKEDITOR.replace(id_textarea);

            });
        },
        update : function ()
        {
            let level = $container.parents('[data-form-collection="item"]').length * 2;
            $container.find('> [data-form-collection="item"]').each(function(iItem) {

                let _inputs = $(this).find('input, select, textarea');
                _inputs.each(function() {


                    let re = /(\[[0-9]+\])/g;
                    let old_name = $(this).attr('name');

                    let splitted = old_name.split(re);
                    splitted[level+1] = '[' + iItem + ']';
                    let new_name = splitted.join('');


                    $(this).attr('name', new_name);


                    // let new_id = new_name.replace(/[\[\]]+/g,'_');
                    // new_id = new_id.substring(0, new_id.length - 1);
                    // $(this).attr('id', new_id);


                });

                $(this).attr('data-form-collection-index', iItem);

            })


        }
    });

}

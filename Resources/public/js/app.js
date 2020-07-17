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



    //
    var $formSlug = $('#setslug');
    if ($formSlug.length) {

        $formSlug.editable({
            name: 'slug',
            emptytext: "Aucun",
            success: function(response, newValue) {
                $("#setslug").parent().find('input:hidden').val(newValue);
            }
        });

    }



    var $pickadate = $('.pickadate');
    if ($pickadate.length) {

        $pickadate.each(function() {

            $(this).pickadate({
                monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                today: 'aujourd\'hui',
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
                    var selected = $(this)[0].get('select');
                    var dateMoment = selected ? moment(selected.obj) : moment();
                    var dayName = dateMoment.format('dddd')[0].toUpperCase() + dateMoment.format('dddd').slice(1);

                    var displayDate = '<div class="picker__date-display"><div class="picker__weekday-display">'+dayName+'</div><div class="picker__month-display"><div>'+dateMoment.format('MMM')+'</div></div><div class="picker__day-display"><div>'+dateMoment.format('D')+'</div></div><div class="picker__year-display"><div>'+dateMoment.format('YYYY')+'</div></div></div>';
                    $(this)[0].$root.find( '.picker__wrap .picker__date-display' ).remove();
                    $(this)[0].$root.find( '.picker__wrap' ).prepend(displayDate);
                    $(this)[0].render();

                }
            });

        })

    }



    var $pickatime = $('.pickatime');
    if ($pickatime.length) {

        // $pickatime.pickatime({
        //     clear: 'Effacer',
        //     format: 'HH:i',
        //     formatLabel: 'HH:i',
        //     formatSubmit: 'H:i',
        //     interval: 15,
        //     hiddenName: true
        // });
        $pickatime.clockpicker({
            amOrPm: false,
            cleartext: 'Effacer',
            donetext: 'Valider'
        });

    }


    var passWordUser = document.querySelector('[data-gdpr]');
    if (passWordUser) {

        new GdprPassword(passWordUser);

    }



    var $bsSelect = $('.bootstrap-select');
    if ($bsSelect.length) {

        $bsSelect.selectpicker({
            autoWidth: false
        });

    }


    var $colorPicker = $('.color-picker');
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

        var _sortable_element = $(this);
        _sortable_element.sortable({
            opacity: 0.7,
            // 'helper': fixHelper,
            update : function ()
            {
                var _path = _sortable_element.parent().attr('data-path') ? _sortable_element.parent().attr('data-path') : window.location.href;
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
            search: '<span>Filtrer:</span> _INPUT_',
            lengthMenu: '<span>Nombre par page:</span> _MENU_',
            sInfo: "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            sInfoEmpty: "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            sEmptyTable: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            paginate: { 'first': 'Premier', 'last': 'Dernier', 'next': '&rarr;', 'previous': '&larr;' }
        },
        drawCallback: function () {
            //$(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            $(this).closest('.dataTables_wrapper').unblock();
            $("[data-modal-xeditable]").editable();
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
        var conf = $.extend( {
            pages: 5,     // number of pages to cache
            url: '',      // script url
            data: null,   // function or object with parameters to send to the server
                          // matching how `ajax.data` works in DataTables
            method: 'GET' // Ajax HTTP method
        }, opts );

        // Private variables for storing the cache
        var cacheLower = -1;
        var cacheUpper = null;
        var cacheLastRequest = null;
        var cacheLastJson = null;

        return function ( request, drawCallback, settings ) {
            var ajax          = false;
            var requestStart  = request.start;
            var drawStart     = request.start;
            var requestLength = request.length;
            var requestEnd    = requestStart + requestLength;

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
                    var d = conf.data( request );
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
                json = $.extend( true, {}, cacheLastJson );
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

        //
        var _suffix = document.URL.slice(-1)=='/' ? '' : '/';
        var _src = $(this).data('src') ? $(this).data('src') : document.URL+_suffix+"dataTable.json";
// console.log(_src);
        var _params = {
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": $.fn.dataTable.pipeline( {
                url: _src,
                pages: 5 // number of pages to cache
            } )
        };

        //
        if ($(this).data("order-column"))
        {
            //
            var _column = $(this).data("order-column") - 1;
            var _direction = 'asc';
            if ($(this).data("order-direction")) 	_direction = $(this).data("order-direction");

            //
            _params["order"] = [[ _column, _direction ]];
        }

        //
        $(this).dataTable(_params);

    })



    // Basic datatable
    $('.datatable').each(function() {

        //
        var _params = {}
        if ($(this).data("order-column"))
        {
            //
            var _column = $(this).data("order-column") - 1;
            var _direction = 'asc';
            if ($(this).data("order-direction")) 	_direction = $(this).data("order-direction");

            //
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





    //
    $(".select-multiple").not('.select2-container').each(function() {

        if ($(this).parent('.duallistbox').length) {

        }
        else {
            $(this).select2();
        }

    });



    //
    $(".select2").not('.select2-container').each(function() {

        var params = {};
        var width = $(this).prop('style')['width'];
        if (width.length) {
            params.width = width;
        }

        if ($(this)[0].hasAttribute("data-placeholder")) {
            params.allowClear = true;
        }

        $(this).select2(params);

    });



    //
    $(".select2-ajax").each(function() {

        var _url = $(this).attr('data-url');
        var _placeholder = $(this).attr('data-placeholder');
        var _multiple = $(this).attr('data-multiple') ? true : false;

        var params = {
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


        if ($(this)[0].hasAttribute("data-placeholder")) {
            params.placeholder = _placeholder;
            params.allowClear = true;
        }


        $(this).select2(params);

    });




    function resizePreview(spanPreview) {

        var width = spanPreview.outerWidth();
        var height = width * 9 / 16;
        spanPreview.height(height);

    }

    var $videos = $('.video-type');
    if ($videos.length) {

        // Enable Select2 select for the length option
        $videos.each(function() {

            $(this).find('.preview').fitVids();
            $(this).on('keyup', 'textarea', function() {

                var srcArea = $(this).val().match(/<iframe.*? src="([^"]+)".*?>.*?<\/iframe>/g);
                var $preview = $(this).closest('.video-type').find('.preview');
                var $iframe = $preview.find('iframe');

                if (srcArea) {
                    if ($iframe) {
                        var srcPreview = $preview.html().match(/<iframe.*? src="([^"]+)".*?>.*?<\/iframe>/g);
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




    var $form = $('form[data-form="form"]');
    var $tabs = $form.find('.tabbable:not([data-form="publish-tabs"]) [data-toggle="tab"]');
    var $submitForm = $form.find('[data-form="submit"]');

    if ($form.length) {

        $form.attr('novalidate', 'novalidate');
        if ($submitForm.length) {

            $form.submit(function(e) {

                var $requiredFields = $form.find('[required="required"]');

                var hasError = false;
                var messageField = '<div class="alert alert-form show fade"><div class="alert-body"><span class="text-semibold">Attention!</span> Ce champs est obligatoire.</div></div>';
                var messageBox = '<div class="alert alert-danger show fade"><div class="alert-body"><span class="text-semibold">Attention!</span> Un ou plusieurs champs obligatoires sont manquants.</div></div>';

                $requiredFields.each(function() {

                    var $formGroup = $(this).closest('.form-group');

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

        var _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : 'btn-danger',
                'callback' : function() {
                    //
                    $('#delete_button').closest('form').submit();
                    $(this).closest('.modal').fadeOut('300', function() { $(this).remove(); });
                },
            }
        }


        //
        var message = 'Voulez-vous vraiment <strong>supprimer ce contenu</strong> ?<br /> \
                        Le contenu sera définitivement supprimé, il ne sera plus possible de le récupérer.';

        //
        modalDyn("Supprimer un contenu", message, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});


    });



    // Override defaults
    // ------------------------------

    // Disable highlight
    $.fn.editable.defaults.highlight = false;

    // Output template
    // $.fn.editableform.template = '<form class="editableform">' +
    //     '<div class="control-group">' +
    //     '<div class="editable-input"></div> <div class="editable-buttons"></div>' +
    //     '<div class="editable-error-block"></div>' +
    //     '</div> ' +
    //     '</form>'

    // Set popup mode as default
    $.fn.editable.defaults.mode = 'popup';

    // Buttons
    $.fn.editableform.buttons =
        '<button type="submit" class="btn btn-primary btn-icon editable-submit"><i class="fas fa-check"></i></button>' +
        '<button type="button" class="btn btn-default btn-icon editable-cancel"><i class="fas fa-ban"></i></button>';





    $(".main-content").on('click', 'a.delete[data-confirm]', function() {

        //
        var _button = $(this);

        //
        var _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : 'btn-danger',
                'callback' : function() {
                    //
                    var $form = _button.closest('.btn-group').children('form');
                    $form.submit();

                },
            }


        }

        var me_data = _button.data('confirm');

        var me_title = "Confirmation";
        var me_description = me_data;

        me_data = me_data.split("|");
        if (me_data.length > 1) {
            me_title = me_data[0];
            me_description = me_data[1];
        }

        modalDyn(me_title, me_description, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});




    });




    $(".main-content").on('click', 'a.status[data-confirm]', function() {

        //
        var _button = $(this);
        var _btn_group = _button.closest('.btn-group');
        var _etat = _btn_group.find('button').hasClass('btn-default') ? 'offline' : 'online';
        var _modalBgClass = (_etat == 'online' ? 'bg-default' : 'bg-primary');
        var _buttonValidClass = (_etat == 'online' ? 'btn-default' : 'btn-primary');
        var _message = _button.data('confirm').replace('%s', _etat=='online' ? 'hors ligne' : 'en ligne');

        //
        var _buttons = {

            "Annuler": function() {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class' : _buttonValidClass,
                'callback' : function() {

                    //
                    var suffix = document.URL.slice(-1)=='/' ? '' : '/';
                    var url = _button.attr("data-path") ? _button.attr("data-path") : document.URL+suffix+"state";
                    var button = $(this);

                    //
                    button.attr('disabled', 'disabled');

                    //
                    $.get(url, function(answer) {
                        if (answer=='OK') {

                            //
                            _btn_group.children('a, button')
                                .removeClass(_etat=='online' ? 'btn-primary' : 'btn-default')
                                .addClass(_etat!='online' ? 'btn-primary' : 'btn-default');

                            _btn_group.find('.status').html('<i class="fas fa-toggle-on"></i> ' + (_etat=='online' ? 'Mettre en ligne' : 'Mettre hors ligne'));

                            //
                            button.removeAttr('disabled');
                            button.closest('.modal').modal('hide');
                            button.closest('.modal').on('hidden.bs.modal', function (e) {
                                $(this).remove();
                            });
                            // button.closest('.modal').fadeOut('300', function() { $(this).remove(); });

                        }
                    });

                }

            },
        }



        modalDyn("Confirmation", _message, _buttons, {modalClass: 'modal_mini', headerClass: _modalBgClass});




    });



    $(".main-content").on('click', 'a.delete-out[data-confirm]', function() {

        //
        var _button = $(this);

        //
        var _buttons = {

            "Annuler": function () {

                $(this).closest('.modal').modal('hide');

            },

            "Confirmer": {

                'class': 'btn-danger',
                'callback': function () {

                    //
                    if (_button.data('rel')) {
                        $('.forms-out[rel="' + _button.data('rel') + '"] form[action$="' + _button.data('id') + '"]').submit();
                    } else {
                        $('.forms-out form[action$="' + _button.data('id') + '"]').submit();
                    }


                },
            }


        }

        modalDyn("Confirmation", _button.attr('data-confirm'), _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});


    });



    var $addToSlideshow = $('[data-slideshow="add"]');
    var $ulSlideshow = $('[data-slideshow="container"]');
    $addToSlideshow.click(function() {

        // add a new tag form (see next code block)
        addImageToSlideshow($ulSlideshow);

    });


    function addImageToSlideshow($collectionHolder) {

        // Get the data-prototype explained earlier
        var prototype = $ulSlideshow.data('prototype');
        console.log($ulSlideshow);

        // get the new index
        var index = $ulSlideshow.find('> div').length;

        var newForm = prototype;
        newForm = newForm.replace(/__name__/g, index);
        newForm = newForm.replace(/__name1__/g, (index + 1));

        var $newForm = $(newForm);

        $newForm.appendTo($ulSlideshow);
        $newForm.find('.im-manager').ImageManager();


    }

});



function modalDyn(title, message, buttons, options)
{
    //
    var defaults = {
        modalClass: '',
        modalId: 'modalDyn',
        headerClass: '',
        zIndex: 5000
    };

    //
    var defaultButton = {
        class: 'btn-default',
        text: '',
        icon: '',
        callback: function() {},
    };

    var params = $.extend({}, defaults, options);

    var _modal = $( '<div class="modal fade" id="' + params.modalId + '"><div class="modal-dialog ' + params.modalClass + '"><div class="modal-content"></div></div></div>' );
    var _header = $( '<div class="modal-header ' + params.headerClass + '"> \
					<h5 class="modal-title">' + title + '</h5> \
					<button type="button" class="close" data-dismiss="modal">×</button> \
					</div>' );
    var _body = '<div class="modal-body">'+message+'</div>';
    var _buttonset = '';


    //
    var hasButton = false;
    var classBtn = "btn-default";

    //
    if (buttons && (typeof buttons === 'object') && buttons !== null)
    {
        //
        _buttonset = $( "<div></div>" ).addClass( "modal-footer" );

        //
        $.each(buttons, function(name, buttonParams) {

            buttonParams = $.isPlainObject( buttonParams ) ?
                buttonParams :
                { class: classBtn, icon: '', callback: buttonParams };

            buttonParams.text = name;
            buttonParams = $.extend({}, defaultButton, buttonParams);

            var button = $('<button type="button" class="btn '+buttonParams.class+'" aria-hidden="true"></button>')
                .click(function() {
                    buttonParams.callback.apply(button);
                })
                .prepend(buttonParams.icon ? '<i class="'+buttonParams.icon+'"></i> ' : '')
                .prepend(buttonParams.text)
                .appendTo(_buttonset);

            //
            classBtn = "btn-success";
            hasButton = true;
        });

    }

    //
    _modal.find('.modal-content').append(_header);
    _modal.find('.modal-content').append(_body);
    _modal.find('.modal-content').append(_buttonset);
    _modal.css('z-index', 9997);
    _modal.hide();
    $("body").append(_modal);

    _modal.modal('show');
    _modal.on('hidden.bs.modal', function (e) {
        _modal.remove();
    });

    return _modal;

}


function formatRepo (repo) {

    if (repo.loading) return repo.text;

    return repo.full_name || repo.text;
}

function formatRepoSelection (repo) {
    return repo.full_name || repo.text;
}




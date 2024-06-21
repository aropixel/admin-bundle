$(function() {


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
        drawCallback: function () {
            $(this).closest('.dataTables_wrapper').unblock();
        },
        preDrawCallback: function() {
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

        let table = $(this).DataTable(_params);

        let input = $(this).parent().parent().find('.dataTables_filter input');

        if (input) {
            input.on( 'keyup', function (e) {
                let regExSearch = '\\b' + this.value;
                table.search(regExSearch, true, false).draw();
            });
        }

    });


    // External table additions
    // ------------------------------

    // Add placeholder to the datatable filter option
    $('.dataTables_filter input[type=search]').attr('placeholder', $.fn.dataTable.defaults.language.filterPlaceholder);


});

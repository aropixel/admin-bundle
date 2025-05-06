// datatable-core.js (à charger en <script> classique, pas type="module")
(function($) {

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

    // Définition pipeline
    $.fn.dataTable.pipeline = function(opts) {
        let conf = $.extend({
            pages: 5,
            url: '',
            data: null,
            method: 'GET'
        }, opts);

        let cacheLower = -1, cacheUpper = null;
        let cacheLastRequest = null, cacheLastJson = null;

        return function(request, drawCallback, settings) {
            let ajax = false;
            let requestStart = request.start;
            let drawStart = request.start;
            let requestLength = request.length;
            let requestEnd = requestStart + requestLength;

            if (settings.clearCache) {
                ajax = true;
                settings.clearCache = false;
            } else if (
                cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper
            ) {
                ajax = true;
            } else if (
                JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
                JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
            ) {
                ajax = true;
            }

            cacheLastRequest = $.extend(true, {}, request);

            if (ajax) {
                if (requestStart < cacheLower) {
                    requestStart = requestStart - requestLength * (conf.pages - 1);
                    if (requestStart < 0) requestStart = 0;
                }

                cacheLower = requestStart;
                cacheUpper = requestStart + requestLength * conf.pages;

                request.start = requestStart;
                request.length = requestLength * conf.pages;

                if ($.isFunction(conf.data)) {
                    let d = conf.data(request);
                    if (d) $.extend(request, d);
                } else if ($.isPlainObject(conf.data)) {
                    $.extend(request, conf.data);
                }

                $(this).closest('.dataTables_wrapper').block({
                    message: '<i class="fas fa-spinner fa-spin"></i>',
                    overlayCSS: { backgroundColor: '#fff', opacity: 0.9, cursor: 'wait' },
                    css: { border: 0, padding: 0, backgroundColor: 'none' }
                });

                settings.jqXHR = $.ajax({
                    type: conf.method,
                    url: conf.url,
                    data: request,
                    dataType: 'json',
                    cache: false,
                    success: function(json) {
                        cacheLastJson = $.extend(true, {}, json);
                        if (cacheLower !== drawStart) json.data.splice(0, drawStart - cacheLower);
                        json.data.splice(requestLength, json.data.length);
                        drawCallback(json);
                    }
                });
            } else {
                let json = $.extend(true, {}, cacheLastJson);
                json.draw = request.draw;
                json.data.splice(0, requestStart - cacheLower);
                json.data.splice(requestLength, json.data.length);
                drawCallback(json);
            }
        };
    };

    // Méthode pour forcer le vidage du cache
    $.fn.dataTable.Api.register('clearPipeline()', function() {
        return this.iterator('table', function(settings) {
            settings.clearCache = true;
        });
    });

})(jQuery);

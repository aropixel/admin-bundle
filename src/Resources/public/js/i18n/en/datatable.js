
$(function() {


    $.extend($.fn.dataTable.defaults, {
        language: {
            paginate: {'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;'},
            filterPlaceholder: "Taper pour filtrer..."
        }
    });

    // Add placeholder to the datatable filter option
    $('.dataTables_filter input[type=search]').attr('placeholder', 'Search...');


});

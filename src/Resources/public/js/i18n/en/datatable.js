
$(function() {


    $.extend($.fn.dataTable.defaults, {
        language: {
            paginate: {'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;'},
            filterPlaceholder: "Type to filter..."
        }
    });

});

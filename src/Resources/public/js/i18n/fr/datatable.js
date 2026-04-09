
$(function() {


    $.extend($.fn.dataTable.defaults, {
        language: {
            search: '<span>Filtrer :</span> _INPUT_',
            lengthMenu: '<span>Nombre par page:</span> _MENU_',
            emptyTable: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            info: "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            infoEmpty: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            infoFiltered: "(sur _MAX_ &eacute;l&eacute;ments disponibles)",
            zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            paginate: {'first': 'Premier', 'last': 'Dernier', 'next': '&rarr;', 'previous': '&larr;'},
            filterPlaceholder: "Taper pour filtrer..."
        }
    });

});

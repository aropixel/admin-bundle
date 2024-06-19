
/* Editeur simple */

document.querySelectorAll('.simple-ckeditor').forEach(field => {
    let id = field.getAttribute('id');
    let options = field.getAttribute('data-ckeditor');
    let optionsToolbar = [];
    if (options) {
        optionsToolbar[id] = options.split(',');
    }

    CKEDITOR.remove(id);
    CKEDITOR.replace( id, {
        toolbar:  [
            ['Bold', 'Italic', 'Underline', 'Strike'],
            ['JustifyLeft', 'JustifyRight', 'JustifyCenter', 'JustifyBlock'],
            ['BulletedList', 'NumberedList', 'Outdent', 'Indent'],
            ['Link'],
            ['Undo', 'Redo'],
            optionsToolbar[id] ? optionsToolbar[id] : ''
        ]
    });
});
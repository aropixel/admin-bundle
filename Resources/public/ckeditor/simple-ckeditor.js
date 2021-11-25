let textarea = document.querySelectorAll('.simple-ckeditor');

textarea.forEach(field => {
    let id = field.getAttribute('id');
    CKEDITOR.remove(id);
    CKEDITOR.replace( id, {
        toolbar:  [
            ['Bold', 'Italic', 'Underline', 'Strike'],
            ['JustifyLeft', 'JustifyRight', 'JustifyCenter', 'JustifyBlock'],
            ['BulletedList', 'NumberedList', 'Outdent', 'Indent'],
            ['Link'],
            ['Undo', 'Redo'],
        ]
    });
});
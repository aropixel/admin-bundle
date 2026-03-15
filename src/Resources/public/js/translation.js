import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';

onDomReady(() => {
    switchFormLanguage();
});


const switchFormLanguage = () => {

    let btnLocales = document.querySelectorAll('.btn-locale');

    btnLocales.forEach((btnLocale) => {

        btnLocale.addEventListener('click', (event) => {

            let btnCurrentLocale = document.getElementById('btnCurrentLocale');
            let locale = btnLocale.getAttribute('data-locale');

            btnCurrentLocale.innerHTML = locale.toUpperCase();

            let fieldsToHide = document.querySelectorAll('.translatable-field.active');
            let fieldsToShow = document.querySelectorAll('.translatable-field-' + locale + ', .translatable-field[data-locale="' + locale + '"]');

            fieldsToHide.forEach((field) => {
                field.classList.remove('active');
            });

            fieldsToShow.forEach((field) => {
                field.classList.add('active');
            });

        });

    });

}


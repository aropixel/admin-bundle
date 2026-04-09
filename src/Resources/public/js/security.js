import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';

onDomReady(() => {
    togglePassword();
});

const togglePassword = () => {

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('[type=password]');

    if (null !== togglePassword) {

        togglePassword.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            togglePassword.classList.contains('fa-eye-slash') ? togglePassword.classList.replace('fa-eye-slash', 'fa-eye') : togglePassword.classList.replace('fa-eye', 'fa-eye-slash');
        });
    }

}

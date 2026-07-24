import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';

onDomReady(() => {
    togglePassword();
});

const togglePassword = () => {

    const toggle = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    if (null === toggle || null === password) {
        return;
    }

    toggle.addEventListener('click', () => {
        const revealed = password.getAttribute('type') === 'text';

        password.setAttribute('type', revealed ? 'password' : 'text');
        toggle.setAttribute('aria-pressed', String(!revealed));

        // The icons are inlined SVGs, so the state is carried by which one is hidden —
        // the old code swapped FontAwesome classes that no longer exist on the element.
        //
        // `toggleAttribute`, not the `hidden` property: `hidden` is defined on HTMLElement,
        // not on SVGElement. `svg.hidden = true` silently creates an expando that reads
        // back as true and changes nothing on screen.
        toggle.querySelectorAll('[data-state]').forEach((icon) => {
            icon.toggleAttribute('hidden', icon.dataset.state === (revealed ? 'shown' : 'hidden'));
        });
    });

}

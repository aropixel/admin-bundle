export function onDomReady(callback) {
    const run = () => callback();

    const handler = (event) => {
        if (event.type === 'turbo:load') {
            document.removeEventListener('DOMContentLoaded', handler);
            run();
        } else if (event.type === 'DOMContentLoaded') {
            // Si Turbo ne s'est pas manifesté d'ici là, on y va.
            setTimeout(() => {
                if (!document.documentElement.hasAttribute('data-turbo-preview')) {
                    run();
                }
            }, 0);
        }
    };

    document.addEventListener('turbo:load', handler);
    document.addEventListener('DOMContentLoaded', handler);

}

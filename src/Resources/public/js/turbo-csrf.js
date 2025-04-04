try {
    import('@hotwired/stimulus').then(({ Application }) => {
        import('@symfony/ux-csrf/dist/controllers/csrf_protection_controller').then((controller) => {
            const app = Application.start();
            app.register('csrf-protection', controller.default);
        });
    });
} catch (e) {
    console.warn('Stimulus or UX-CSRF not available', e);
}
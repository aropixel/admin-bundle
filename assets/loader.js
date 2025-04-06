try {
    import('@symfony/stimulus-bundle').then((app) => {
        app.startStimulusApp();
    });
} catch (e) {
    console.warn('Stimulus or UX-CSRF not available', e);
}
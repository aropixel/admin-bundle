class SwitchStatus {

    constructor(target, config) {

        this.options = {
            componentClasses: {
                'checkbox' : '.form-check-input',
                'label' : '.form-check-label',
            },
            stateClasses: {
                'outdated' : 'outdated',
                'published' : 'checked',
                'scheduled' : 'scheduled',
                'offline' : '',
            },
            stateLabels: {
                'outdated' : 'Passé',
                'published' : 'Publié',
                'scheduled' : 'Programmé',
                'offline' : 'Non publié',
            }
        }

        if (typeof config === 'object') {
            this.options = {...this.options, ...config}
        }

        target.addEventListener('change', (event) => { this.onChangeToggle(event) });

        if (config.publishAtDate && config.publishAtTime) {
            config.publishAtDate.addEventListener('change', (event) => { this.onChangePublishAt(event) });
            config.publishAtTime.addEventListener('change', (event) => { this.onChangePublishAt(event) });
        }

        if (config.publishUntilDate && config.publishUntilTime) {
            config.publishUntilDate.addEventListener('change', (event) => { this.onChangePublishUntil(event) });
            config.publishUntilTime.addEventListener('change', (event) => { this.onChangePublishUntil(event) });
        }

    }


    onChangeToggle(event) {
        this.updateStatus(event);
        this.updateSwitchToggle(event);
        this.updateSwitchText(event);
    }

    onChangePublishAt(event) {
        this.highlightPublishAt(event);
        this.updateSwitchToggle(event);
        this.updateSwitchText(event);
    }

    onChangePublishUntil(event) {
        this.highlightPublishUntil(event);
        this.updateSwitchToggle(event);
        this.updateSwitchText(event);
    }


    updateSwitchToggle(event) {

    }

    updateSwitchText() {

    }

    updateStatus() {

    }

    highlightPublishAt() {

    }

    highlightPublishUntil() {

    }


}
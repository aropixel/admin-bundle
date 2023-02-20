export class SwitchStatus {

    constructor(statusField, config) {

        this.statusField = statusField;
        this.labelField = null;
        this.checkboxField = null;

        this.options = {
            publishAtDate : null,
            publishAtTime : null,
            publishUntilDate : null,
            publishUntilTime : null,
            componentClasses: {
                'checkbox' : 'form-check-input',
                'label' : 'form-check-label',
            },
            stateClasses: {
                'outdated' : 'outdated',
                'published' : 'checked',
                'scheduled' : 'scheduled',
                'offline' : '',
            },
            publishDateClasses: {
                'outdated' : 'outdated',
                'scheduled' : 'scheduled',
                'published' : 'published',
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


        this.checkboxField = [...this.statusField.parentNode.children].filter((child) => child.classList.contains(this.options.componentClasses.checkbox)).shift();
        this.labelField = [...this.statusField.parentNode.children].filter((child) => child.classList.contains(this.options.componentClasses.label)).shift();


        this.checkboxField.addEventListener('change', (event) => { this.onChangeToggle(event) });

        var that = this;
        if (this.options.publishAtDate && this.options.publishAtTime) {

            let publishAtDatePicker = $(this.options.publishAtDate).pickadate('picker');
            publishAtDatePicker.on('set', function () {
                that.onChangePublishAt();
            });

            $(this.options.publishAtTime).change(function() { that.onChangePublishAt() });
            $(this.options.publishAtTime).trigger('change');
        }

        if (this.options.publishUntilDate && this.options.publishUntilTime) {

            let publishUntilDatePicker = $(this.options.publishUntilDate).pickadate('picker');
            publishUntilDatePicker.on('set', function () {
                that.onChangePublishUntil();
            });

            $(this.options.publishUntilTime).change(function() { that.onChangePublishUntil() });
            $(this.options.publishUntilTime).trigger('change');

        }

    }


    onChangeToggle() {
        this.updateStatus();
        this.updateSwitchToggle();
        this.updateSwitchText();
    }

    onChangePublishAt() {
        this.highlightPublishAt();
        this.updateSwitchToggle();
        this.updateSwitchText();
    }

    onChangePublishUntil() {
        this.highlightPublishUntil();
        this.updateSwitchToggle();
        this.updateSwitchText();
    }


    isScheduled() {

        if (this.statusField.value === 'offline') {
            return false;
        }

        return (
            typeof this.options.publishAtDate !== "undefined" ||
            typeof this.options.publishUntilDate !== "undefined"
        );

    }

    isPublished() {

        if (this.statusField.value === 'offline') {
            return false;
        }
        return !this.isScheduled() || (!this.isOutdated() && this.isIncoming());
    }

    isIncoming() {

        if (this.options.publishAtDate === null || typeof this.options.publishAtDate === "undefined") {
            return false;
        }

        let dateInfo = this.options.publishAtDate.value.split('/');
        let dateStr = dateInfo[2]+'-'+dateInfo[1]+'-'+dateInfo[0];
        let hourStr = this.options.publishAtTime.value ? this.options.publishAtTime.value : '00:00';
        hourStr += ':00';

        let nowTime = new Date().getTime();
        let publishAtTime = new Date(dateStr + 'T' + hourStr).getTime();

        return nowTime < publishAtTime;

    }

    isOutdated() {

        if (this.options.publishUntilDate === null || typeof this.options.publishUntilDate === "undefined") {
            return false;
        }

        let dateInfo = this.options.publishUntilDate.value.split('/');
        let dateStr = dateInfo[2]+'-'+dateInfo[1]+'-'+dateInfo[0];
        let hourStr = this.options.publishUntilTime.value ? this.options.publishUntilTime.value : '00:00';
        hourStr += ':00';

        let nowTime = new Date().getTime();
        let publishUntilTime = new Date(dateStr + 'T' + hourStr).getTime();

        return nowTime > publishUntilTime;

    }



    updateSwitchToggle(event) {

        if (this.isScheduled()) {

            if (this.isOutdated()) {
                this.checkboxField.classList.add(this.options.stateClasses.outdated);
                this.checkboxField.classList.remove(this.options.stateClasses.published);
                this.checkboxField.classList.remove(this.options.stateClasses.scheduled);
                this.options.stateClasses.offline && this.checkboxField.classList.remove(this.options.stateClasses.offline);
            }
            else if (this.isIncoming()) {
                this.checkboxField.classList.add(this.options.stateClasses.scheduled);
                this.checkboxField.classList.remove(this.options.stateClasses.outdated);
                this.checkboxField.classList.remove(this.options.stateClasses.published);
                this.options.stateClasses.offline && this.checkboxField.classList.remove(this.options.stateClasses.offline);
            }
            else {
                this.checkboxField.classList.add(this.options.stateClasses.published);
                this.checkboxField.classList.remove(this.options.stateClasses.outdated);
                this.checkboxField.classList.remove(this.options.stateClasses.scheduled);
                this.options.stateClasses.offline && this.checkboxField.classList.remove(this.options.stateClasses.offline);
            }

        }
        else {

            if (this.isPublished()) {
                this.checkboxField.classList.add(this.options.stateClasses.published);
                this.checkboxField.classList.remove(this.options.stateClasses.outdated);
                this.checkboxField.classList.remove(this.options.stateClasses.scheduled);
                this.options.stateClasses.offline && this.checkboxField.classList.remove(this.options.stateClasses.offline);
            }
            else {
                this.checkboxField.classList.remove(this.options.stateClasses.published);
                this.checkboxField.classList.remove(this.options.stateClasses.outdated);
                this.checkboxField.classList.remove(this.options.stateClasses.scheduled);
                this.options.stateClasses.offline && this.checkboxField.classList.add(this.options.stateClasses.offline);
            }

        }

    }

    updateSwitchText(event) {

        if (this.isScheduled()) {

            if (this.isOutdated()) {
                this.labelField.innerHTML = this.options.stateLabels.outdated;
            }
            else if (this.isIncoming()) {
                this.labelField.innerHTML = this.options.stateLabels.scheduled;
            }
            else {
                this.labelField.innerHTML = this.options.stateLabels.published;
            }

        }
        else {

            if (this.isPublished()) {
                this.labelField.innerHTML = this.options.stateLabels.published;
            }
            else {
                this.labelField.innerHTML = this.options.stateLabels.offline;
            }

        }

    }

    updateStatus(event) {
        this.statusField.value = this.checkboxField.checked ? 'online' : 'offline';
    }


    highlightPublishAt() {

        if (typeof this.options.publishAtDate !== "undefined") {

            if (this.isIncoming()) {
                this.options.publishAtDate.classList.add(this.options.publishDateClasses.scheduled);
                this.options.publishAtTime.classList.add(this.options.publishDateClasses.scheduled);
                this.options.publishAtDate.classList.remove(this.options.publishDateClasses.published);
                this.options.publishAtTime.classList.remove(this.options.publishDateClasses.published);
            } else if (this.options.publishAtDate.value.length) {
                this.options.publishAtDate.classList.remove(this.options.publishDateClasses.scheduled);
                this.options.publishAtTime.classList.remove(this.options.publishDateClasses.scheduled);
                this.options.publishAtDate.classList.add(this.options.publishDateClasses.published);
                this.options.publishAtTime.classList.add(this.options.publishDateClasses.published);
            }
            else {
                this.options.publishAtDate.classList.remove(this.options.publishDateClasses.scheduled);
                this.options.publishAtTime.classList.remove(this.options.publishDateClasses.scheduled);
                this.options.publishAtDate.classList.remove(this.options.publishDateClasses.published);
                this.options.publishAtTime.classList.remove(this.options.publishDateClasses.published);
            }

        }

    }

    highlightPublishUntil() {

        if (typeof this.options.publishUntilDate !== "undefined") {

            if (this.isOutdated()) {
                this.options.publishUntilDate.classList.add(this.options.publishDateClasses.outdated);
                this.options.publishUntilTime.classList.add(this.options.publishDateClasses.outdated);
                this.options.publishUntilDate.classList.remove(this.options.publishDateClasses.published);
                this.options.publishUntilTime.classList.remove(this.options.publishDateClasses.published);
            } else if (this.options.publishUntilDate.value.length) {
                this.options.publishUntilDate.classList.remove(this.options.publishDateClasses.outdated);
                this.options.publishUntilTime.classList.remove(this.options.publishDateClasses.outdated);
                this.options.publishUntilDate.classList.add(this.options.publishDateClasses.published);
                this.options.publishUntilTime.classList.add(this.options.publishDateClasses.published);
            }
            else {
                this.options.publishUntilDate.classList.remove(this.options.publishDateClasses.outdated);
                this.options.publishUntilTime.classList.remove(this.options.publishDateClasses.outdated);
                this.options.publishUntilDate.classList.remove(this.options.publishDateClasses.published);
                this.options.publishUntilTime.classList.remove(this.options.publishDateClasses.published);
            }

        }
    }


}

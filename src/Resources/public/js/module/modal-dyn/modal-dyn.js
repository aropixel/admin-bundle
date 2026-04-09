export class ModalDyn {

    constructor(title, message, buttons, options) {

        this.title = title;
        this.message = message;
        this.buttons = buttons;
        this.options = options;

        this.defaults = {
            modalClass: '',
            modalId: 'modalDyn',
            headerClass: '',
            zIndex: 5000
        }

        this.defaultButton = {
            class: 'btn-default',
            text: '',
            icon: '',
            callback: function() {},
        };

        this.params = $.extend({}, this.defaults, this.options);
        this._modal = $( '<div class="modal fade" id="' + this.params.modalId + '"><div class="modal-dialog modal-dialog-centered ' + this.params.modalClass + '"><div class="modal-content"></div></div></div>' );
        this._header = $( '<div class="modal-header ' + this.params.headerClass + '"> \
					<h5 class="modal-title">' + title + '</h5> \
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> \
					</div>' );
        this._body = '<div class="modal-body">'+message+'</div>';

        let _buttonset = '';
        this.classBtn = "btn-default";

        if (buttons && (typeof buttons === 'object') && buttons !== null) {
            _buttonset = this.modalDyn(_buttonset);
        }

        this.showModal(_buttonset);

    }

    modalDyn(_buttonset)
    {

        _buttonset = $( "<div></div>" ).addClass( "modal-footer" );

        $.each(this.buttons, function(name, buttonParams) {

            buttonParams = $.isPlainObject( buttonParams ) ?
                buttonParams :
                { class: this.classBtn, icon: '', callback: buttonParams };

            buttonParams.text = name;
            buttonParams = $.extend({}, this.defaultButton, buttonParams);

            let button = $('<button type="button" class="btn '+buttonParams.class+'" aria-hidden="true"></button>')
                .click(function() {
                    buttonParams.callback.apply(button);
                })
                .prepend(buttonParams.icon ? '<i class="'+buttonParams.icon+'"></i> ' : '')
                .prepend(buttonParams.text)
                .appendTo(_buttonset);

            this.classBtn = "btn-success";
        });

        return _buttonset;

    }

    showModal(_buttonset)
    {

        this._modal.find('.modal-content').append(this._header);
        this._modal.find('.modal-content').append(this._body);
        this._modal.find('.modal-content').append(_buttonset);
        this._modal.css('z-index', 9997);
        this._modal.hide();

        $("body").append(this._modal);

        this._modal.modal('show');

        return this._modal;
    }

}

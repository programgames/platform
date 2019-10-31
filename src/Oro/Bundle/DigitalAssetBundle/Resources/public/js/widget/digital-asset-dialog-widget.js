define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const DialogWidget = require('oro/dialog-widget');
    const errorHandler = require('oroui/js/error');

    const DigitalAssetDialogWidget = DialogWidget.extend({
        options: _.extend({}, DialogWidget.prototype.options, {
            alias: 'dam-dialog',
            title: __('oro.digitalasset.dam.dialog.select_file'),
            url: null,
            stateEnabled: false,
            incrementalPosition: true,
            desktopLoadingBar: true,
            moveAdoptedActions: false,
            dialogOptions: {
                resizable: true,
                autoResize: true,
                allowMaximize: false,
                allowMinimize: false,
                modal: true,
                maximizedHeightDecreaseBy: 'minimize-bar',
                minWidth: 720
            }
        }),

        /**
         * @inheritDoc
         */
        constructor: function DigitalAssetDialogWidget(options) {
            DigitalAssetDialogWidget.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            DigitalAssetDialogWidget.__super__.initialize.call(this, options);

            // Adds Cancel button to the actions container at the bottom of dialog window.
            this.listenTo(this, 'widgetReady', (function($el) {
                const cancelButton = $el.find('[type="reset"]').clone();
                cancelButton.text(__('oro.digitalasset.dam.dialog.cancel.label'));
                cancelButton.on('click', (function() {
                    this.remove();
                }).bind(this));

                this.addAction('cancel', 'main', cancelButton);
            }).bind(this));
        },

        /**
         * @inheritDoc
         */
        initializeWidget: function(options) {
            DigitalAssetDialogWidget.__super__.initializeWidget.call(this, options);

            this.on('formReset', _.bind(this._onFormReset, this));
        },

        /**
         * @inheritDoc
         */
        _onAdoptedFormResetClick: function(form) {
            this._onFormReset(form);
        },

        /**
         * @param {jQuery.Element} [form]
         * @private
         */
        _onFormReset: function(form) {
            form = form || this.form;

            $(form).trigger('reset');
            $(form).find('[type="file"]').trigger('change');
        },

        /**
         * @inheritDoc
         *
         * Overrides parent method to enable JSON-only handling on content load - prevents dialog window from blanking.
         */
        _onContentLoad: function(content) {
            const json = this._getJson(content);

            delete this.loading;

            if (json) {
                this._onJsonContentResponse(json);
            } else {
                this.disposePageComponents();
                this.setContent(content, true);
            }

            if (this.deferredRender) {
                this.deferredRender
                    .done(_.bind(this._triggerContentLoadEvents, this, content))
                    .fail(_.bind(function(error) {
                        if (!this.disposing && !this.disposed) {
                            if (error) {
                                errorHandler.showErrorInConsole(error);
                            }
                            this._triggerContentLoadEvents();
                        }
                    }, this));
            } else {
                this._triggerContentLoadEvents();
            }
        }
    });

    return DigitalAssetDialogWidget;
});
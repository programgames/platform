/*jslint nomen:true*/
/*global define*/
define(function(require) {
    'use strict';

    var ModelAction = require('oro/datagrid/action/model-action');
    var ActionManager = require('oroaction/js/action-manager');

    var ActionWidgetAction = ModelAction.extend({

        /**
         * @property {Object}
         */
        options: {
            operationName: null
        },

        /**
         * @property {ActionManager}
         */
        actionManager: null,

        /**
         * @inheritDoc
         */
        initialize: function() {
            ActionWidgetAction.__super__.initialize.apply(this, arguments);

            this.actionManager = new ActionManager(this);
        },

        /**
         * @inheritdoc
         */
        run: function() {
            this.actionManager.execute();
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.actionManager;

            ActionWidgetAction.__super__.dispose.call(this);
        }
    });

    return ActionWidgetAction;
});

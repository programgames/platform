define(function(require) {
    'use strict';

    /**
     * Displays header of board column
     * @augments BaseView
     */
    var ColumnHeaderView;
    var BaseView = require('oroui/js/app/views/base/view');

    ColumnHeaderView = BaseView.extend({
        /**
         * @inheritDoc
         */
        className: 'board-column-header',

        /**
         * @inheritDoc
         */
        template: require('tpl!../../../../templates/board/column-header-view.html'),

        /**
         * @inheritDoc
         */
        constructor: function ColumnHeaderView() {
            ColumnHeaderView.__super__.constructor.apply(this, arguments);
        }
    });

    return ColumnHeaderView;
});

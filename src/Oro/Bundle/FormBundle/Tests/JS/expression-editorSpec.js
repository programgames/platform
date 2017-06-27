define(function(require) {
    'use strict';

    require('jasmine-jquery');
    var _ = require('underscore');
    var ExpressionEditorView = require('oroform/js/app/views/expression-editor-view');

    //fixtures
    var options = JSON.parse(require('text!./Fixture/expression-editor-options.json'));
    var html = require('text!./Fixture/expression-editor-template.html');

    //variables
    var expressionEditorUtil = null;
    var expressionEditorView = null;
    var typeahead = null;
    var el = null;

    describe('oroform/js/app/views/expression-editor-view', function() {

        beforeEach(function() {
            window.setFixtures(html);

            expressionEditorView = new ExpressionEditorView(_.defaults({}, {
                el: '#expression-editor'
            }, options));

            expressionEditorUtil = expressionEditorView.util;
            typeahead = expressionEditorView.typeahead;
            el = expressionEditorView.el;
        });

        afterEach(function() {
            expressionEditorView.dispose();
            expressionEditorView = null;
            expressionEditorUtil = null;
            typeahead = null;
            el = null;
        });

        describe('check initialization', function() {
            it('component is defined', function() {
                expect(_.isObject(expressionEditorUtil)).toBeTruthy();
            });

            it('view is defined', function() {
                expect(_.isObject(expressionEditorView)).toBeTruthy();
            });
        });

        describe('check rule editor validation', function() {
            var checks = {};
            checks['product'] = false;
            checks['product.'] = false;
            checks['product.id'] = true;
            checks['product.category.id'] = true;
            checks['product.id == 1.234'] = true;
            checks['product.id in [1, 2, 3, 4, 5]'] = true;
            checks['product.sku matches test'] = false;
            checks['product.sku matches "test"'] = true;
            checks['product.id matches "test"'] = false;
            checks['product.id not in [1, 2, 3, 4, 5]'] = true;
            checks['product.id == product.id'] = true;
            checks['product.id != product.id'] = true;
            checks['product.id > product.id'] = true;
            checks['product.id < product.id'] = true;
            checks['someStr == 4'] = false;
            checks['product.someStr == 4'] = false;
            checks['(product.id == 5 and product.id == 10('] = false;
            checks['(product.id == 5 and product.id == 10()'] = false;
            checks['(product.id == 5((((  and product.id == 10()'] = false;
            checks[')product.id == 5 and product.id == 10('] = false;
            checks['(product.id == 5() and product.id == 10)'] = false;
            checks['{product.id == 5 and product.id == 10}'] = false;
            checks['(product.id == 5 and product.id == 10) or (product.sku in ["sku1", "sku2", "sku3"])'] = true;
            checks['pricelist'] = false;
            checks['pricelist.'] = false;
            checks['pricelist.id'] = false;
            checks['pricelist[]'] = false;
            checks['pricelist[].'] = false;
            checks['pricelist[].id'] = false;
            checks['pricelist[1]'] = false;
            checks['pricelist[1].'] = false;
            checks['pricelist[1].id'] = true;
            checks['pricelist[1].prices.value == 1.234'] = true;
            checks['window.category = {id: 1}; true and category.id'] = false;
            checks['"1string" ==' + " 'string'"] = true;
            checks['"2string\\" ==' + " 'string'"] = false;
            checks['"3string\\\\" ==' + " 'string'"] = true;
            checks['"4string" ==' + " 'string\\'"] = false;
            checks['"5string" ==' + " 'string\\\\'"] = true;
            checks['"6str\\"ing" ==' + " 'st\\'ring'"] = true;

            _.each(checks, function(result, check) {
                it('should' + (!result ? ' not' : '') + ' be valid when "' + check + '"', function() {
                    expect(expressionEditorUtil.validate(check)).toEqual(result);
                });
            })
        });

        describe('check autocomplete logic', function() {
            it('chain select', function() {
                el.value = '';
                typeahead.lookup();
                for (var i = 0; i < 6; i++) {
                    typeahead.select();
                }

                expect(el.value).toEqual('product.featured + product.featured + ');
            });

            it('check suggested items for "product.category."', function() {
                el.value = 'product.category.';
                el.selectionStart = el.value.length;

                expect(typeahead.source()).toEqual([
                    'id',
                    'left',
                    'level',
                    'materializedPath',
                    'right',
                    'root',
                    'createdAt',
                    'updatedAt'
                ]);
            });

            it('check suggested items if previous item is entity or scalar(not operation)', function() {
                var values = ['product.featured', '1', '1 in [1,2,3]', '(1 == 1)'];
                _.each(values, function(value) {
                    el.value = value + ' ';
                    el.selectionStart = el.value.length;
                    typeahead.lookup();

                    expect(typeahead.source()).toEqual([
                        '+', '-', '%', '*', '/',
                        'and', 'or',
                        '==', '!=',
                        '>', '<', '<=', '>=',
                        'in', 'not in',
                        'matches'
                    ]);
                });
            });

            it('check suggested items if previous item is operation', function() {
                var values = ['', '+', '(1 =='];
                _.each(values, function(value) {
                    el.value = value + ' ';
                    el.selectionStart = el.value.length;
                    typeahead.lookup();

                    expect(typeahead.source()).toEqual([
                        'product',
                        'pricelist'
                    ]);
                });
            });

            it('check nesting level 1', function() {
                expressionEditorUtil.options.itemLevelLimit = 1;
                expressionEditorUtil._prepareItems();

                expect(_.keys(expressionEditorUtil.entitiesItems)).toEqual([]);
            });

            it('check level 2', function() {
                expressionEditorUtil.options.itemLevelLimit = 2;
                expressionEditorUtil._prepareItems();

                expect(_.keys(expressionEditorUtil.entitiesItems.product.child)).toEqual([
                    'featured',
                    'id',
                    'inventory_status',
                    'sku',
                    'status',
                    'type',
                    'createdAt',
                    'updatedAt'
                ]);
            });

            it('check level 3', function() {
                expressionEditorUtil.options.itemLevelLimit = 3;
                expressionEditorUtil._prepareItems();

                expect(_.keys(expressionEditorUtil.entitiesItems.product.child.category.child)).toEqual([
                    'id',
                    'left',
                    'level',
                    'materializedPath',
                    'right',
                    'root',
                    'createdAt',
                    'updatedAt'
                ]);
            });
        });

        describe('check value update after inserting selected value', function() {
            it('inserting in the field start', function() {
                var checks = [];
                checks.push([2, 'pro', 'product.']);
                checks.push([8, 'product. == 10', 'product.featured  == 10']);
                checks.push([12, 'product.id !', 'product.id != ']);

                _.each(checks, function(check) {
                    el.value = check[1];
                    el.selectionStart = check[0];

                    typeahead.lookup();
                    typeahead.select();

                    expect(el.value).toEqual(check[2]);
                });
            });
        });

        describe('check data source render', function() {
            it('shown if type pricel', function() {
                el.value = 'pricel';
                typeahead.lookup();
                typeahead.select();
                var $dataSource = expressionEditorView.getDataSource('pricelist').$widget;

                expect($dataSource.is(':visible')).toBeTruthy();

                el.value = 'pricelist[1].id + product.id';
                el.selectionStart = 27;
                typeahead.lookup();

                expect($dataSource.is(':visible')).toBeFalsy();
            });
        });
    });
});

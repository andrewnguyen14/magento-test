/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (jQuery, _, uiRegistry, select, modal) {
    'use strict';

    return select.extend({
        dependentFields: [
            'sales_rule_form.sales_rule_form.actions.discount_calculation_type',
            'sales_rule_form.sales_rule_form.actions.y_qty',
            'sales_rule_form.sales_rule_form.actions.each_nth',
        ],
        labelChangeFields: [
            'sales_rule_form.sales_rule_form.actions.y_qty',
            'sales_rule_form.sales_rule_form.actions.discount_step',
            'sales_rule_form.sales_rule_form.actions.discount_qty',
            'sales_rule_form.sales_rule_form.actions.each_nth',
        ],
        initialize: function () {
            var currentValue;
            this._super();
            uiRegistry.promise(this.dependentFields).done(_.bind(function () {
                currentValue = this.value();
                this.enableDisableFields(this.getFieldsToShow(this.value()));
                this.changeFieldsLabel(this.getFieldsToChangeLabel(this.value()));
            }, this));
        },
        onUpdate: function (value) {

            this.enableDisableFields(this.getFieldsToShow(value));
            this.changeFieldsLabel(this.getFieldsToChangeLabel(value));
            return this._super();
        },
        enableDisableFields: function (fields) {
            uiRegistry.get(this.dependentFields, function () {
                _.each(arguments, function (argument) {
                    (_.contains(fields, argument['index'])) ?
                            argument['show']() : argument['hide']();
                });
            });

        },
        changeFieldsLabel: function (fields) {
            var defaultLabels = [];
            defaultLabels['discount_step'] = 'Discount Qty Step (Buy X)';
            defaultLabels['discount_qty'] = 'Maximum Qty Discount is Applied To';
            defaultLabels['y_qty'] = 'Y Qty';
            defaultLabels['each_nth'] = 'nth Qty';
            uiRegistry.get(this.labelChangeFields, function () {
                _.each(arguments, function (argument) {
                    if (fields[argument['index']] != undefined) {
                        jQuery('.admin__field[data-index=' + argument['index'] + '] label span').text(fields[argument['index']]);
                        argument['label'] = fields[argument['index']];
                    } else {
                        jQuery('.admin__field[data-index=' + argument['index'] + '] label span').text(defaultLabels[argument['index']]);
                        argument['label'] = defaultLabels[argument['index']];
                    }
                });
            });

        },
        getFieldsToChangeLabel: function (type) {
            var fieldToChangeLabel = [];
            switch (type) {
                case "eachSpent":
                    fieldToChangeLabel['discount_step'] = 'On Each Spent';
                    fieldToChangeLabel['discount_qty'] = 'Maximum Time(s) Discount is Applied To';
                    break;
                case "buyXGetX":
                    fieldToChangeLabel['y_qty'] = 'X Qty (Discount Product Qty)';
                    break;
                case "nthOrder":
                    fieldToChangeLabel['each_nth'] = 'nth Order';
                    break;
                case "cheapestOff":
                case "expensiveoff":
                case "firstOrder":
                case "relatedProduct":
                case "nthItem":
                case "eachNthItem":
                case "by_percent":
                case "by_fixed":
                case "cart_fixed":
                case "buy_x_get_y":
                case "buyXGetY":
                    fieldToChangeLabel = [];
                    break;
            }
            return fieldToChangeLabel;
        },
        getFieldsToShow: function (type) {
            var fieldToShow = [];
            switch (type) {
                case "cheapestOff":
                case "expensiveoff":
                case "firstOrder":
                case "relatedProduct":
                case "eachSpent":
                    fieldToShow = ['discount_calculation_type'];
                    break;
                case "buyXGetY":
                case "buyXGetX":
                    fieldToShow = ['discount_calculation_type', 'y_qty'];
                    break;
                case "nthItem":
                case "eachNthItem":
                case "nthOrder":
                    fieldToShow = ['discount_calculation_type', 'each_nth'];
                    break;
                case "by_percent":
                case "by_fixed":
                case "cart_fixed":
                case "buy_x_get_y":
                    fieldToShow = [];
                    break;
            }
            return fieldToShow;
        }
    });
});
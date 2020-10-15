<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Model\Rule\Condition;

use Magento\Customer\Api\AccountManagementInterface;

class Order extends \Magento\Rule\Model\Condition\AbstractCondition
{

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType['select'] = ['()', '!()','{}', '!{}'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'completed_order' => __("Completed Order"),
            'order_status' => __('Order Status'),
            'total_sales_amount' => __('Total Sales Amount'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'completed_order':
            case 'total_sales_amount':
                return 'numeric';
            case 'order_status':
                return 'select';
        }
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'completed_order':
            case 'total_sales_amount':
                return 'text';
            case 'order_status':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'order_status':
                    $options = $this->statusCollectionFactory->create()->toOptionArray();
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $customerId = $model->getCustomerId();
        $this->orders = $this->orderFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
        $orderStatuses = [];
        foreach ($this->orders as $orders) {
            $orderStatuses[] = $orders->getStatus();
        }
        $orderStatuses = array_unique($orderStatuses);

        $totalSalesAmountCollection = $this->orders;

        $totalSalesAmountCollection->addFieldToSelect('customer_id');

        $totalSalesAmountCollection->getSelect()->columns("SUM(main_table.base_subtotal - IFNULL(main_table.base_subtotal_canceled, 0) - IFNULL(main_table.base_subtotal_refunded, 0) - ABS(main_table.base_discount_amount) - IFNULL(main_table.base_discount_canceled, 0)) as orders_sum_amount");

        $totalSalesAmountCollection->getSelect()->group('customer_id');

        $totalSalesAmountdata = $totalSalesAmountCollection->getData();

        foreach ($totalSalesAmountdata as $totalSalesAmountValue) {
            $totalSalesAmount = $totalSalesAmountValue['orders_sum_amount'];
        }


        if ('total_sales_amount' == $this->getAttribute() && $totalSalesAmount) {
            $model->setTotalSalesAmount($totalSalesAmount);
        }

        if ('completed_order' == $this->getAttribute()) {
            $this->orders->addAttributeToFilter('status', 'complete');
            $count = count($this->orders);
            if ($count) {
                $model->setData('completed_order', $count);
            }
        }

        if ('order_status' == $this->getAttribute() && $customerId) {
            return $this->validateAttribute($orderStatuses);
        }

        return parent::validate($model);
    }
}

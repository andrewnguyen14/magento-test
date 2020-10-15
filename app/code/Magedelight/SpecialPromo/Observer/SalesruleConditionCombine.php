<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Observer;

use Magedelight\SpecialPromo\Model\RuleAdditionalFactory;

class SalesruleConditionCombine implements \Magento\Framework\Event\ObserverInterface
{

    protected $_conditionCustomer;
    protected $_conditionOrder;

    public function __construct(RuleAdditionalFactory $ruleAdditionalFactory, \Magedelight\SpecialPromo\Model\Rule\Condition\Customer $conditionCustomer, \Magedelight\SpecialPromo\Model\Rule\Condition\Order $conditionOrder)
    {
        $this->ruleAdditional = $ruleAdditionalFactory;
        $this->_conditionCustomer = $conditionCustomer;
        $this->_conditionOrder = $conditionOrder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additional = $observer->getEvent()->getAdditional();
        $orderAttributes = $this->_conditionOrder->loadAttributeOptions()->getAttributeOption();
        $orderAttributesArray = [];
        foreach ($orderAttributes as $code => $label) {
            $orderAttributesArray[] = [
                'value' => 'Magedelight\SpecialPromo\Model\Rule\Condition\Order|' . $code,
                'label' => $label,
            ];
        }

        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $customerAttributesArray = [];
        foreach ($customerAttributes as $code => $label) {
            $customerAttributesArray[] = [
                'value' => 'Magedelight\SpecialPromo\Model\Rule\Condition\Customer|' . $code,
                'label' => $label,
            ];
        }


        $condition = [
            [
                'value' => $orderAttributesArray,
                'label' => __('Order Attribute (Advanced Promotion)'),
            ],
            ['value' => $customerAttributesArray,
                'label' => __('Customer Attributes (Advanced Promotion)'),
            ]
        ];

        $additional->setConditions($condition);
    }
}

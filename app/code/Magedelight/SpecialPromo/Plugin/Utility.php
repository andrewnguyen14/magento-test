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

namespace Magedelight\SpecialPromo\Plugin;

use Magedelight\SpecialPromo\Model\Source\DiscountType;

class Utility
{

    /**
     * @var CalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var array
     */
    private $additionalRuleValidateFor = [
        DiscountType::MOST_CHEAPEST,
        DiscountType::MOST_EXPENSIVE,
        DiscountType::FIRST_ORDER,
        DiscountType::DISCOUNT_ON_EACH_SPENT,
        DiscountType::NTH_ITEM_DISCOUNT,
        DiscountType::EACH_NTH_ITEM_DISCOUNT,
        DiscountType::RELATED_PRODUCT_DISCOUNT
    ];

    public function __construct(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\SalesRule\Model\Rule\Customer $ruleCustomer,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magedelight\SpecialPromo\Helper\Data $promoHelper
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->customerFactory = $ruleCustomer;
        $this->couponFactory = $coupon;
        $this->promoHelper = $promoHelper;
    }

    public function aroundCanProcessRule($subject, $proceed, $rule, $address)
    {
        if (!$this->promoHelper->checkModuleEnable($rule)) {
            return false;
        }
        $items = $address->getAllVisibleItems();
        
        if ($this->needAdditionalValidate($rule) && $this->validateRuleUsage($rule, $address)) {
            if (!empty($items)) {
                switch ($rule->getSimpleAction()) {
                    case DiscountType::NTH_ITEM_DISCOUNT:
                    case DiscountType::DISCOUNT_ON_EACH_SPENT:
                    case DiscountType::EACH_NTH_ITEM_DISCOUNT:
                    case DiscountType::RELATED_PRODUCT_DISCOUNT:
                    case DiscountType::FIRST_ORDER:
                        $items = $items[0];
                        break;
                }

                $rule = $rule->load($rule->getId());
                $discountCalculator = $this->getDiscountCalculator($rule);
                return $discountCalculator->canProcessRule($items, $rule);
            }
        }
        return $proceed($rule, $address);
    }

    private function validateRuleUsage($rule, $address)
    {
        /**
         * check entire usage limit
         */
        $couponCode = $rule->getCode();
        if ($couponCode) {
            $coupon = $this->couponFactory->loadByCode($couponCode);
            if ($coupon->getId()) {
                if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getQuote()->getCustomerId();
            $ruleCustomer = $this->customerFactory->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        return true;
    }

    private function getDiscountCalculator($rule)
    {
        return $this->calculatorFactory->create($rule->getSimpleAction());
    }

    private function needAdditionalValidate($rule)
    {
        return in_array($rule->getSimpleAction(), $this->additionalRuleValidateFor);
    }
}

<?php

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

namespace Magedelight\SpecialPromo\Plugin\Model;

use Magedelight\SpecialPromo\Model\Source\DiscountType;
use Magento\Quote\Model\Quote\Address;

class Validator
{

    /**
     * Rule source collection
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $_rules;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $validatorUtility;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\SalesRule\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * Counter is used for assigning temporary id to quote address
     *
     * @var int
     */
    protected $counter = 0;

    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Utility $utility,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\RulesApplier $rulesApplier
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->validatorUtility = $utility;
        $this->priceCurrency = $priceCurrency;
        $this->rulesApplier = $rulesApplier;
    }

    public function afterProcessShippingAmount($subject, $result, Address $address)
    {

        $shippingAmount = $address->getShippingAmountForDiscount();
        if ($shippingAmount !== null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote = $address->getQuote();
        $appliedRuleIds = [];
        $discountAmountdata = [];
        $rules = [
            DiscountType::RELATED_PRODUCT_DISCOUNT,
            DiscountType::NTH_ORDER_DISCOUNT,
            DiscountType::BUY_X_GET_X,
            DiscountType::MOST_CHEAPEST,
            DiscountType::MOST_EXPENSIVE,
            DiscountType::FIRST_ORDER,
            DiscountType::BUY_X_GET_Y,
            DiscountType::NTH_ITEM_DISCOUNT,
            DiscountType::EACH_NTH_ITEM_DISCOUNT,
            DiscountType::DISCOUNT_ON_EACH_SPENT
                ];
        foreach ($this->_getRules($address, $subject) as $rule) {
            /* @var \Magento\SalesRule\Model\Rule $rule */
            if (!$rule->getApplyToShipping() || !$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (in_array($rule->getSimpleAction(), $rules)) {
                switch ($rule->getSimpleAction()) {
                    case DiscountType::RELATED_PRODUCT_DISCOUNT:
                    case DiscountType::NTH_ORDER_DISCOUNT:
                    case DiscountType::BUY_X_GET_X:
                    case DiscountType::MOST_CHEAPEST:
                    case DiscountType::MOST_EXPENSIVE:
                    case DiscountType::FIRST_ORDER:
                    case DiscountType::BUY_X_GET_Y:
                    case DiscountType::NTH_ITEM_DISCOUNT:
                    case DiscountType::EACH_NTH_ITEM_DISCOUNT:
                    case DiscountType::DISCOUNT_ON_EACH_SPENT:
                        $discountAmountdata = $this->getDiscountData($rule, $address);
                        $discountAmount = $discountAmountdata['discountAmount'];
                        $baseDiscountAmount = $discountAmountdata['baseDiscountAmount'];
                        break;
                }
                $discountAmount = min($address->getShippingDiscountAmount() + $discountAmount, $shippingAmount);
                $baseDiscountAmount = min(
                    $address->getBaseShippingDiscountAmount() + $baseDiscountAmount,
                    $baseShippingAmount
                );
                $address->setShippingDiscountAmount($discountAmount);
                $address->setBaseShippingDiscountAmount($baseDiscountAmount);
            } else {
                return $result;
            }
        }
        return $subject;
    }

    public function getDiscountData($rule, $address)
    {
        $discountAmountdata = [];
        $shippingAmount = $address->getShippingAmountForDiscount();
        if ($shippingAmount !== null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }


        $discountCalcuationType = $rule->getDiscountCalculationType();
        switch ($discountCalcuationType) {
            case 1:
                $rulePercent = min(100, $rule->getDiscountAmount());
                $_rulePct = $rulePercent / 100;
                $shippingDiscountAmount = $address->getShippingAmount() * $_rulePct;

                $shippingBaseDiscountAmount = $address->getBaseShippingAmount() * $_rulePct;
                $discountAmountdata['discountAmount'] = min($address->getShippingAmount(), $shippingDiscountAmount);
                $discountAmountdata['baseDiscountAmount'] = min($address->getBaseShippingAmount(), $shippingBaseDiscountAmount);

                break;
            case 2:
                $shippingDiscountAmount = $address->getShippingAmount();
                $shippingBaseDiscountAmount = $address->getBaseShippingAmount();
                $discountAmountdata['discountAmount'] = min($address->getShippingAmount(), $shippingDiscountAmount);
                $discountAmountdata['baseDiscountAmount'] = min($address->getBaseShippingAmount(), $shippingBaseDiscountAmount);

                break;
            default:
                $rulePercent = min(100, $rule->getDiscountAmount());
                $_rulePct = $rulePercent / 100;
                $shippingDiscountAmount = $address->getShippingAmount() * $_rulePct;

                $shippingBaseDiscountAmount = $address->getBaseShippingAmount() * $_rulePct;
                $discountAmountdata['discountAmount'] = min($address->getShippingAmount(), $shippingDiscountAmount);
                $discountAmountdata['baseDiscountAmount'] = min($address->getBaseShippingAmount(), $shippingBaseDiscountAmount);
        }
        return $discountAmountdata;
    }

    protected function _getRules(Address $address = null, $subject)
    {
        $addressId = $this->getAddressId($address);
        $key = $subject->getWebsiteId() . '_'
                . $subject->getCustomerGroupId() . '_'
                . $subject->getCouponCode() . '_'
                . $addressId;
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = $this->_collectionFactory->create()
                    ->setValidationFilter(
                        $subject->getWebsiteId(),
                        $subject->getCustomerGroupId(),
                        $subject->getCouponCode(),
                        null,
                        $address
                    )
                    ->addFieldToFilter('is_active', 1)
                    ->load();
        }
        return $this->_rules[$key];
    }

    /**
     * @param Address $address
     * @return string
     */
    protected function getAddressId(Address $address)
    {
        if ($address == null) {
            return '';
        }
        if (!$address->hasData('address_sales_rule_id')) {
            if ($address->hasData('address_id')) {
                $address->setData('address_sales_rule_id', $address->getData('address_id'));
            } else {
                $type = $address->getAddressType();
                $tempId = $type . $this->counter++;
                $address->setData('address_sales_rule_id', $tempId);
            }
        }
        return $address->getData('address_sales_rule_id');
    }
}

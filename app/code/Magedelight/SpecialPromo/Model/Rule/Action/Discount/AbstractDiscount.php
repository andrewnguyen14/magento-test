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

namespace Magedelight\SpecialPromo\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Validator;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory as DiscountDataFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount as DiscountAbstractDiscount;
use Magedelight\SpecialPromo\Model\Source\DiscountType;

class AbstractDiscount extends DiscountAbstractDiscount
{

    public $distributeDiscount = [];
    protected $allRuleItemSku = [];
    protected $itemKeyPrice = 0;

    /**
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param DiscountDataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Validator $validator,
        DiscountDataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $qty, $rulePercent);

        return $discountData;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function getRuleItems($item, $rule)
    {
        $allItems = $this->getCartItems($item);
        return $this->fiterRuleItems($allItems, $rule);
    }

    /**
     * @param array $allItems
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function fiterRuleItems($allItems, $rule)
    {
        $this->allRuleItemSku = [];
        foreach ($allItems as $key => $item) {
            if ($item->getParentItem()) {
                unset($allItems[$key]);
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                unset($allItems[$key]);
                continue;
            }

            array_push($this->allRuleItemSku, $item->getSku());
        }
        return $allItems;
    }

    protected function getAllRuleItemSku()
    {
        return $this->allRuleItemSku;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    protected function getCartItems($item)
    {
        return $item->getQuote()->getAllVisibleItems();
    }

    protected function getCartItemsSku($item)
    {
        $skus = [];
        $allItems = $this->getCartItems($item);
        foreach ($allItems as $rowItem) {
            $itemSku = $rowItem->getProduct()->getData('sku');
            array_push($skus, $itemSku);
        }
        return $skus;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param bool $distributeDiscount
     * @return Data
     */
    protected function getDiscountData($rule, $item, $qty, $distributeDiscount = false)
    {
        $discountCalcuationType = $rule->getDiscountCalculationType();
        switch ($discountCalcuationType) {
            case 1:
                $discountData = $this->calculateOnPercentageFinalPrice($rule, $item, $qty);
                break;
            case 2:
                $discountData = $this->calculateOnFixedPrice($rule, $item, $qty, $distributeDiscount);
                break;
            default:
                $discountData = $this->calculateOnPercentageOriginalPrice($rule, $item, $qty);
        }
        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param bool $distributeDiscount
     * @return Data
     */
    protected function calculateOnFixedPrice($rule, $item, $qty, $distributeDiscount)
    {
        if ($distributeDiscount && empty($this->distributeDiscount)) {
            $this->splitDiscount($rule, $item);
        }

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $amount = $this->getFixedDiscountAmount($rule, $item, $qty, $distributeDiscount);
        $actualAmount = $this->getFixedActualDiscountAmount($rule, $item, $qty, $distributeDiscount);

        $discountData->setAmount($amount);
        $discountData->setBaseAmount($actualAmount);
        $discountData->setOriginalAmount($amount);
        $discountData->setBaseOriginalAmount($actualAmount);
        return $discountData;
    }

    private function getFixedDiscountAmount($rule, $item, $qty, $distributeDiscount)
    {
        if ($distributeDiscount) {
            $discountAmount = array_sum(
                array_slice($this->distributeDiscount, $this->itemKeyPrice, $item->getQty())
            );
            return $this->priceCurrency->convert($discountAmount, $item->getQuote()->getStore());
        }
        return $qty * $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
    }

    private function getFixedActualDiscountAmount($rule, $item, $qty, $distributeDiscount)
    {
        if ($distributeDiscount) {
            $discountAmount = array_sum(
                array_slice($this->distributeDiscount, $this->itemKeyPrice, $item->getQty())
            );
            return $qty * $discountAmount;
        }
        return $qty * ($rule->getDiscountAmount() + $rule->getExtraDiscountAmount());
    }

    protected function getTotalQtyForSplit($rule, $item)
    {
        $qty = 0;
        $ruleItems = $this->getRuleItems($item, $rule);
        foreach ($ruleItems as $items) {
            if ($item->getId() == $items->getId()) {
                $this->itemKeyPrice = $qty;
            }
            $qty += $items->getQty();
        }
        return $qty;
    }

    protected function splitDiscount($rule, $item)
    {
        $totalQty = $this->getTotalQtyForSplit($rule, $item);
        $discountAmount = $rule->getDiscountAmount();
        $ratio = round($discountAmount / $totalQty, 2);
        for ($i = 0; $i < $totalQty; $i++) {
            $this->distributeDiscount[$i] = ($i == $totalQty - 1) ?
                    $discountAmount - array_sum($this->distributeDiscount) : $ratio;
        }
    }

    protected function getItemPrice($item, $basePrice = false)
    {
        if ($basePrice) {
            return [
                $this->priceCurrency->convert($item->getProduct()->getPrice(), $item->getQuote()->getStore()),
                $item->getProduct()->getPrice(),
                $this->priceCurrency->convert($item->getProduct()->getPrice(), $item->getQuote()->getStore()),
                $item->getProduct()->getPrice()
            ];
        }

        return [
            $this->validator->getItemPrice($item),
            $this->validator->getItemBasePrice($item),
            $this->validator->getItemOriginalPrice($item),
            $this->validator->getItemBaseOriginalPrice($item),
        ];
    }

    protected function getRulePercentge($rule, $rulePercent)
    {
        return $rulePercent / 100;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    protected function calculateOnPercentageFinalPrice($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $rulePercent = min(100, $rule->getDiscountAmount());

        $_rulePct = $this->getRulePercentge($rule, $rulePercent);

        list($itemPrice, $baseItemPrice, $itemOriginalPrice, $baseItemOriginalPrice) = $this->getItemPrice($item);

        $discountData->setAmount(($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseAmount(($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct);
        $discountData->setOriginalAmount(($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseOriginalAmount(
            ($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct
        );

        if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
            $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
            $item->setDiscountPercent($discountPercent);
        }
        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    protected function calculateOnPercentageOriginalPrice($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $rulePercent = min(100, $rule->getDiscountAmount());
        $_rulePct = $this->getRulePercentge($rule, $rulePercent);

        list($itemPrice, $baseItemPrice, $itemOriginalPrice, $baseItemOriginalPrice) = $this->getItemPrice($item, true);
        $discountData->setAmount(($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseAmount(($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct);
        $discountData->setOriginalAmount(($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseOriginalAmount(
            ($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct
        );

        if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
            $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
            $item->setDiscountPercent($discountPercent);
        }
        return $discountData;
    }
}

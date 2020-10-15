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

use Magedelight\SpecialPromo\Model\Rule\Action\Discount\AbstractDiscount;

class NthItem extends AbstractDiscount
{

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {

        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleItems = $this->getRuleItems($item, $rule);

        $nthItems = $this->getTotalNQtyItems($ruleItems, $rule);

        $totalQty = $this->getTotalQty($ruleItems);
        if ($totalQty >= $rule->getEachNth()) {
            if (array_key_exists($item->getData('sku'), $nthItems)) {
                return $this->getDiscountData($rule, $item, $nthItems[$item->getData('sku')]);
            }
        }
        return $discountData;
    }

    public function canProcessRule($item, $rule)
    {
        if ($rule->getEachNth() < 1) {
            return false;
        }

        $ruleItems = $this->getRuleItems($item, $rule);
        $totalQty = $this->getTotalQty($ruleItems);
        return count($ruleItems) >= $rule->getDiscountStep()
            && $totalQty >= $rule->getEachNth();
    }

    private function getTotalNQtyItems($ruleItems, $rule)
    {
        $totalQty = $this->getTotalQty($ruleItems);
        if ($totalQty < $rule->getEachNth()) {
            return;
        }
        usort($ruleItems, [$this, "cheapestProductSort"]);
        $qtyToAssigned = floor($totalQty / $rule->getEachNth());
        if ($rule->getDiscountQty()) {
            $qtyToAssigned = min($rule->getDiscountQty(), $qtyToAssigned);
        }
        $rulelistItems = array_slice(
            $ruleItems,
            0,
            $qtyToAssigned
        );
        $skus = [];

        foreach ($rulelistItems as $litem) {
            $qty = ($litem->getQty() > $qtyToAssigned)?$qtyToAssigned:$litem->getQty();
            $skus[$litem->getData('sku')] = $qty;
            $qtyToAssigned -= $litem->getQty();
            if ($qtyToAssigned <= 0) {
                break;
            }
        }
        return $skus;
    }

    protected function cheapestProductSort($item1, $item2)
    {
        return $this->validator->getItemBasePrice($item1) > $this->validator->getItemBasePrice($item2);
    }

    private function getTotalQty($items)
    {
        $qty = 0;
        if (!count($items)) {
            return;
        }

        foreach ($items as $item) {
            $qty += $item->getQty();

        }
        return $qty;
    }
}

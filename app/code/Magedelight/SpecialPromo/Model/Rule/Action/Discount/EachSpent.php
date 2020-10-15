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

class EachSpent extends AbstractDiscount
{
    
    protected $ruleItemSku = [];
    
    protected $totalValue = 0;
    
    protected $ruleItems = [];


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
        
        $this->ruleItems = $this->getRuleItems($item, $rule);
        $this->totalValue = $this->getTotalValue($this->ruleItems);
        
        if ($this->totalValue < $rule->getDiscountStep() || $rule->getDiscountStep() <= 0) {
            return $discountData;
        }
        if (in_array($item->getProduct()->getData('sku'), $this->ruleItemSku)) {
            $qty = $item->getQty();
            return $this->getDiscountData($rule, $item, $qty, true);
        }
        
        return $discountData;
    }
    
    public function canProcessRule($items, $rule)
    {
        $this->ruleItems = $this->getRuleItems($items, $rule);
        $this->totalValue = $this->getTotalValue($this->ruleItems);
        if ($this->totalValue < $rule->getDiscountStep() || $rule->getDiscountStep() <= 0) {
            return false;
        }
        return true;
    }

    protected function splitDiscount($rule, $item)
    {
        $totalQty = $this->getTotalQtyForSplit($rule, $item);
        $multiplySpent = (floor($this->totalValue / $rule->getDiscountStep()) / $rule->getDiscountAmount()) * $rule->getDiscountAmount();
        
        if ($rule->getDiscountQty()) {
            $multiplySpent = min($rule->getDiscountQty(), $multiplySpent) * 1;
        }
        $discountAmount = $rule->getDiscountAmount() * $multiplySpent;
        $ratio = round($discountAmount / $totalQty, 2);
        for ($i=0; $i<$totalQty; $i++) {
            $this->distributeDiscount[$i] = ($i==($totalQty -1))?
                    $discountAmount - array_sum($this->distributeDiscount):$ratio;
        }
        return $this->distributeDiscount;
    }
    
    private function getTotalValue($items)
    {
        $price = 0;
        $this->ruleItemSku = [];
        foreach ($items as $item) {
            array_push($this->ruleItemSku, $item->getProduct()->getData('sku'));
            $price += ($item->getQty() * $this->validator->getItemBasePrice($item));
        }
        $shippingAmount = $item->getQuote()->getShippingAddress()->getShippingAmount();
        $taxAmount = $item->getQuote()->getShippingAddress()->getTaxAmount();
        return $price;
    }

    protected function getRulePercentge($rule, $rulePercent)
    {
        $multiplySpent = (floor($this->totalValue / $rule->getDiscountStep()) / $rule->getDiscountAmount()) * $rule->getDiscountAmount();
        if ($rule->getDiscountQty() > 0) {
            $multiplySpent = min($rule->getDiscountQty(), $multiplySpent);
        }
        return ($rulePercent * $multiplySpent) / 100;
    }
}

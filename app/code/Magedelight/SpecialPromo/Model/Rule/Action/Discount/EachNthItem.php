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

class EachNthItem extends AbstractDiscount
{

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

        if ($item->getQty() >= $rule->getEachNth()) {
            return $this->getDiscountData($rule, $item, $this->getQtyToDiscount($item, $rule));
        }
        return $discountData;
    }
    
    public function canProcessRule($item, $rule)
    {
        if ($rule->getEachNth() < 1) {
            return false;
        }
        
        $ruleItems = $this->getRuleItems($item, $rule);
        return (count($ruleItems) >= $rule->getDiscountStep());
    }


    private function getQtyToDiscount($item, $rule)
    {
        $qty = $item->getQty();
        $nthQty = $rule->getEachNth();
        if ($item->getQty() % $nthQty != 0) {
            $qtyToAssigned = ($qty - ($qty % $nthQty)) / $nthQty;
        } else {
            $qtyToAssigned = $qty / $nthQty;
        }
        if ($rule->getDiscountQty()) {
            $qtyToAssigned = min($rule->getDiscountQty(), $qtyToAssigned);
        }
        return $qtyToAssigned;
    }
}

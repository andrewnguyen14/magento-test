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

class Cheapest extends AbstractDiscount
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
        
        $items = $this->getRuleItems($item, $rule);
        $discountStep = $rule->getDiscountStep();
        if (count($items) >= $discountStep) {
            usort($items, [$this, "cheapestProductSort"]);
            $leastItem = current($items);
            $itemSku = $item->getProduct()->getData('sku');
            if ($itemSku == $leastItem->getProduct()->getData('sku')) {
                return $this->getDiscountData($rule, $item, $qty);
            }
        }
        return $discountData;
    }
    
    public function canProcessRule($items, $rule)
    {
        $discountStep = $rule->getDiscountStep();
        if (count($items) >= $discountStep) {
            return true;
        }
        return false;
    }

    protected function cheapestProductSort($item1, $item2)
    {
        return $this->validator->getItemBasePrice($item1) > $this->validator->getItemBasePrice($item2);
    }
}

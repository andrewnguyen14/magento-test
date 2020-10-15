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

namespace Magedelight\SpecialPromo\Model\Source;

class DiscountType implements \Magento\Framework\Option\ArrayInterface
{

    const MODEL_PATH = "Magedelight\SpecialPromo\Model\Rule\Action\Discount\\";
    
    const MOST_CHEAPEST = 'cheapestOff';
    const MOST_EXPENSIVE = 'expensiveoff';
    const FIRST_ORDER = 'firstOrder';
    const BUY_X_GET_Y = 'buyXGetY';
    const NTH_ITEM_DISCOUNT = 'nthItem';
    const EACH_NTH_ITEM_DISCOUNT = 'eachNthItem';
    const DISCOUNT_ON_EACH_SPENT = 'eachSpent';
    
    const NTH_ORDER_DISCOUNT = 'nthOrder';
    const RELATED_PRODUCT_DISCOUNT = 'relatedProduct';
    const BUY_X_GET_X = 'buyXGetX';
    
    /**
     * @return array
     */
    public function getDiscountType()
    {
        
        return [
            self::MOST_CHEAPEST => [
                'label' => 'Most Cheapest Product Discount',
                'class' => self::MODEL_PATH . 'Cheapest'
            ],
            self::MOST_EXPENSIVE => [
                'label' => 'Most Expensive Product Discount',
                'class' => self::MODEL_PATH . 'Expensive'
            ],
            self::FIRST_ORDER => [
                'label' => 'First Order Discount',
                'class' => self::MODEL_PATH . 'FirstOrder'
            ],
            self::DISCOUNT_ON_EACH_SPENT => [
                'label' => 'Discount on Each Spent',
                'class' => self::MODEL_PATH . 'EachSpent'
            ],
            self::BUY_X_GET_Y => [
                'label' => 'Buy X Get Y Discount',
                'class' => self::MODEL_PATH . 'Buyxgety'
            ],
            self::BUY_X_GET_X => [
                'label' => 'Buy X Get X Free',
                'class' => self::MODEL_PATH . 'Buyxgetx'
            ],
            self::NTH_ITEM_DISCOUNT => [
                'label' => 'Every nth Item Discount',
                'class' => self::MODEL_PATH . 'NthItem'
            ],
            self::EACH_NTH_ITEM_DISCOUNT => [
                'label' => 'Each nth Item Qty Discount',
                'class' => self::MODEL_PATH . 'EachNthItem'
            ],
            self::NTH_ORDER_DISCOUNT => [
                'label' => 'Each nth Order Discount',
                'class' => self::MODEL_PATH . 'NthOrder'
            ],
            self::RELATED_PRODUCT_DISCOUNT => [
                'label' => 'Discount on Related Product',
                'class' => self::MODEL_PATH . 'RelatedProduct'
            ],
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $discountTypes = $this->getDiscountType();
        foreach ($discountTypes as $key => $type) {
            $options[] = [
                'label' => __($type['label']),
                'value' => $key
            ];
        }
        
        return $options;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getClassByType($type)
    {
        $discountTypes = $this->getDiscountType();
        return $discountTypes[$type]['class'];
    }
}

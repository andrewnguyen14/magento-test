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

namespace Magedelight\SpecialPromo\Model\Config\Source;

class DiscountButton implements \Magento\Framework\Option\ArrayInterface
{
    const DEFAULT_DISCOUNT = 0;
    const MAGEDELIGHT_PROMO = 1;
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DEFAULT_DISCOUNT, 'label' => __('Default Discount')],
            ['value' => self::MAGEDELIGHT_PROMO, 'label' => __('Discount Popup')],
        ];
    }
}

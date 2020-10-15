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

class DiscountCalculationType implements \Magento\Framework\Option\ArrayInterface
{
    const PERCENTAGE_ORIGINAL_PRICE = 0;
    const PERCENTAGE_FINAL_PRICE = 1;
    const FIXED_ORIGINAL_PRICE = 2;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PERCENTAGE_FINAL_PRICE, 'label' => __('Apply as percentage of final price')],
            ['value' => self::PERCENTAGE_ORIGINAL_PRICE, 'label' => __('Apply as percentage of original')],
            ['value' => self::FIXED_ORIGINAL_PRICE, 'label' => __('Apply as fixed amount')],
        ];
    }
}

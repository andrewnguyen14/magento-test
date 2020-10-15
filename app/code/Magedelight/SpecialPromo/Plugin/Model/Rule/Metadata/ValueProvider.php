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

namespace Magedelight\SpecialPromo\Plugin\Model\Rule\Metadata;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;
use Magedelight\SpecialPromo\Model\Source\DiscountType;

class ValueProvider
{
    protected $discountType;


    public function __construct(
        DiscountType $discountType
    ) {
        $this->discountType = $discountType;
    }
    
    public function afterGetMetadataValues(SalesRuleValueProvider $subject, $result)
    {
        $options = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        $magedeligtPromos[] = [
            'label' => __('Advance Promotions'),
            'value' => $this->discountType->toOptionArray()
        ];
        $options = array_merge($options, $magedeligtPromos);
        return $result;
    }
}

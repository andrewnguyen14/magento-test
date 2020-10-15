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

namespace Magedelight\SpecialPromo\Plugin\Checkout\Block;

use Magedelight\SpecialPromo\Helper\Data as promoHelper;

class LayoutProcessor
{
       
    /**
     * @var promoHelper
     */
    private $promoHelper;
    
    /**
     * @param promoHelper $promoHelper
     */
    public function __construct(
        promoHelper $promoHelper
    ) {
        $this->promoHelper = $promoHelper;
    }

    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $jsLayout)
    {
        if ($this->promoHelper->dispayApplyButton()) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount']);
            }
        }
        return $jsLayout;
    }
}

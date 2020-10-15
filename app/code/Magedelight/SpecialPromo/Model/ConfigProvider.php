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

namespace Magedelight\SpecialPromo\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;
use Magedelight\SpecialPromo\Helper\Data as promoHelper;

class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var promoHelper
     */
    protected $promoHelper;

    public function __construct(
        LayoutInterface $layout,
        promoHelper $promoHelper
    ) {

        $this->layout = $layout;
        $this->promoHelper = $promoHelper;
    }

    public function getCouponBlock()
    {
        $couponBlock = $this->layout->createBlock('Magedelight\SpecialPromo\Block\Coupon')
                ->setTemplate("Magedelight_SpecialPromo::checkout/cart/coupon.phtml");

        $formBlock = $this->layout->createBlock('Magedelight\SpecialPromo\Block\Coupon')
                ->setTemplate("Magedelight_SpecialPromo::checkout/cart/couponForm.phtml");

        $couponBlock->setChild('md.coupon.block.form', $formBlock);
        return $couponBlock->toHtml();
    }

    public function getConfig()
    {
        if ($this->promoHelper->dispayApplyButton()) {
            return [
                'coupon_block' => $this->getCouponBlock()
            ];
        }
        return [];
    }
}

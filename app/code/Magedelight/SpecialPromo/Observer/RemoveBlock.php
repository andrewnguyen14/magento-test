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
namespace Magedelight\SpecialPromo\Observer;

use Magedelight\SpecialPromo\Helper\Data as promoHelper;

class RemoveBlock implements \Magento\Framework\Event\ObserverInterface
{
    const CART_FULL_ACTION_NAME = 'checkout_cart_index';
    
    const MAGENTO_DISCOUNT_BLOCK_NAME = 'checkout.cart.coupon';
    const MAGEDELIGHT_DISCOUNT_BLOCK_NAME = 'md.coupon.block';

    protected $promoHelper;

    public function __construct(
        promoHelper $promoHelper
    ) {
        $this->promoHelper = $promoHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getEvent()->getFullActionName() == self::CART_FULL_ACTION_NAME) {
            $layout = $observer->getLayout();
            $block = $layout->getBlock($this->getDiscountButtonToRemove());
            if ($block) {
                $layout->unsetElement($this->getDiscountButtonToRemove());
            }
        }
    }
    
    private function getDiscountButtonToRemove()
    {
        return ($this->promoHelper->dispayApplyButton())?
            self::MAGENTO_DISCOUNT_BLOCK_NAME:self::MAGEDELIGHT_DISCOUNT_BLOCK_NAME;
    }
}

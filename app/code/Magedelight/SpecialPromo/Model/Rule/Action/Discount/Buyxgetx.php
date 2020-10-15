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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;

class Buyxgetx extends AbstractDiscount
{

    /**
     * @var array
     */
    protected $ruleFreeItems = [];

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Cart
     */
    protected $cart;
    private $isFreeItemSame = false;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        Cart $cart
    ) {

        $this->productRepository = $productRepository;
        $this->cart = $cart;
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $x = $rule->getDiscountStep();
        $y = $rule->getYQty();

        if (!$x) {
            return $discountData;
        }
        $buyAndDiscountQty = $x + $y;

        $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
        $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;
        $discountQty = $fullRuleQtyPeriod * $y;
        if ($freeQty > $x) {
            $discountQty += $freeQty - $x;
        }

        return $this->getDiscountData($rule, $item, $discountQty);
    }
}

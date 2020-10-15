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

class Buyxgety extends AbstractDiscount
{

    /**
     * @var array
     */
    protected $ruleFreeItems = [];
    protected $ruleConditionSetOf = false;

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
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $this->getFreeItemsFromRule($rule);
        $qty = $this->getQty($rule, $item);
        return $this->getDiscountData($rule, $item, $qty);
    }

    private function getQty($rule, $item)
    {
        $maxQty = 0;
        $yQty = $rule->getYQty();
        $qty = $item->getQty();

        $timesApply = $this->getOriginalItemQty($item, $rule);
        $yProductQty = (floor($qty / $yQty)) * $yQty;
        $totalDiscountQty = $yQty * $timesApply;
        $maxQty = min($yProductQty, $totalDiscountQty);

        $ruleMaxQty = (floor($rule->getDiscountQty() / $yQty)) * $yQty;
        if ($rule->getDiscountQty()) {
            $maxQty = min($ruleMaxQty, $maxQty);
        }

        return $maxQty;
    }

    private function getOriginalItemQty($item, $rule)
    {
        $xQty = $rule->getDiscountStep();
        $ruleId = $rule->getId();
        $qty = 0;
        $allItems = $this->getCartItems($item);
        $originalItemSku = [];
        $itemSku = $item->getProduct()->getData('sku');
        foreach ($this->ruleFreeItems[$ruleId] as $key => $freeItems) {
            if ($itemSku == $freeItems) {
                $originalItemSku[] = $key;
            }
        }
        $allSkus = [];
        $allSkusWithQty = [];
        $finalQty = [];
        if ($this->ruleConditionSetOf) {
            foreach ($allItems as $cartItem) {
                $cartItemSku = $cartItem->getProduct()->getData('sku');
                if ($cartItemSku != $itemSku && in_array($cartItemSku, $originalItemSku)) {
                    $allSkus[] = $cartItemSku;
                    $allSkusWithQty[$cartItemSku] = $cartItem->getQty();
                }
            }
            
            $allSkus = array_unique($allSkus);

            if (count($allSkusWithQty) > 0 && count(array_intersect(array_keys($allSkusWithQty), $originalItemSku)) == count($originalItemSku)) {
                $finalQty[] = floor(min(array_values($allSkusWithQty)) / $xQty);
            }
            
            if (!empty($finalQty)) {
                $qty = min($finalQty);
            }
        } else {
            foreach ($allItems as $cartItem) {
                $itemSku = $cartItem->getProduct()->getData('sku');
                if (in_array($itemSku, $originalItemSku) && $cartItem->getQty() >= $xQty) {
                    $qty += floor($cartItem->getQty() / $rule->getDiscountStep());
                }
            }
        }
        return $qty;
    }

    private function getFreeItemsFromRule($rule)
    {
        if (!isset($this->ruleFreeItems[$rule->getId()])) {
            $ruleAction = $rule->getActions()->asArray();
            if (isset($ruleAction['conditions'])) {
                foreach ($ruleAction['conditions'] as $condition) {
                    if (isset($condition['free'])) {
                        if ($condition['operator'] == '()' || $condition['operator'] == '{{}}') {
                            $skus = explode(',', $condition['value']);
                            foreach ($skus as $sku) {
                                $this->ruleFreeItems[$rule->getId()][trim($sku)] = $condition['free'];
                            }
                            if ($condition['operator'] == '{{}}') {
                                $this->ruleConditionSetOf = true;
                            }
                        } else {
                            $this->ruleFreeItems[$rule->getId()][$condition['value']] = $condition['free'];
                        }
                    }
                }
            }
        }
        return $this;
    }
}

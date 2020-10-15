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

class RelatedProduct extends AbstractDiscount
{

    protected $productRepository;

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
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        
        $ruleItems = $this->getRuleItems($item, $rule);

        $relatedItems = $this->getRelatedProductSku($ruleItems, $rule);
        
        //$totalQty = $this->getTotalQty($ruleItems);
        if (array_key_exists($item->getProduct()->getData('sku'), $relatedItems)) {
            if ($rule->getDiscountQty()) {
                $qty = min($rule->getDiscountQty(), $relatedItems[$item->getProduct()->getData('sku')]);
            }
            return $this->getDiscountData($rule, $item, $qty);
        }
        return $discountData;
    }

    public function canProcessRule($item, $rule)
    {
        $ruleItems = $this->getRuleItems($item, $rule);
        $itemSku = [];
        $finalItemSku = [];
        $relatedItemSku = [];
        foreach ($ruleItems as $ruleItem) {
            $relatedproducts = $ruleItem->getProduct()->getRelatedProductCollection();
            if (count($relatedproducts) > 0) {
                foreach ($relatedproducts as $relatedproduct) {
                    $relatedItemSku[$ruleItem->getProduct()->getData('sku')][] = $relatedproduct->getSku();
                }
            }
            $itemSku[] = $ruleItem->getProduct()->getData('sku');
        }
        if (count($relatedItemSku) > 0) {
            foreach ($relatedItemSku as $mainProduct => $relatedProducts) {
                if (count($relatedProducts) > 0) {
                    foreach ($relatedProducts as $relatedProduct) {
                        if (in_array($relatedProduct, $itemSku)) {
                            $finalItemSku[] = $mainProduct;
                        }
                    }
                }
            }
        }
       
        return count($finalItemSku);
    }

    private function getRelatedProductSku($ruleItems, $rule)
    {
        $totalQty = $this->getTotalQty($ruleItems);

        $qtyToAssigned = $totalQty;

        $ruleAction = $rule->getActions()->asArray();
        $ruleSkus = [];
        $allRelatedProducts = [];
        $cartProductSku = [];
        $skuExistInCondition = true;
        foreach ($ruleItems as $litem) {
            $mainProductOfRelated = $this->productRepository->getById($litem->getProductId());
            if ($mainProductOfRelated) {
                $relatedProducts = $mainProductOfRelated->getRelatedProducts();
                if (!empty($relatedProducts)) {
                    foreach ($relatedProducts as $relatedProduct) {
                        $allRelatedProducts[$mainProductOfRelated->getSku()][] = $relatedProduct->getSku();
                    }
                }
            }

            $cartProductSku[$mainProductOfRelated->getSku()] = $litem->getQty();
        }

        $mainProducts = array_keys($allRelatedProducts);


        if (isset($ruleAction['conditions'])) {
            foreach ($ruleAction['conditions'] as $condition) {
                if ($condition['type'] == 'Magedelight\SpecialPromo\Model\Rule\Condition\RelatedProduct') {
                    if ($condition['operator'] == '==') {
                        $ruleSkus[] = $condition['value'];
                    } else {
                        foreach (explode(',', $condition['value']) as $skus) {
                            $ruleSkus[] = trim($skus);
                        }
                    }
                }
            }
        } else {
            $ruleSkus = $mainProducts;
        }

        $ruleSkus = array_unique($ruleSkus);

        $offerMainProducts = [];
        $finalSkus = [];
        if (is_array($ruleSkus)) {
            $offerMainProducts = array_intersect($mainProducts, $ruleSkus);
        } else {
            if (in_array($ruleSkus, $mainProducts)) {
                $offerMainProducts[] = $ruleSkus;
            }
        }

        foreach (array_unique($offerMainProducts) as $offerMainProduct) {
            $offerRelatedProducts = isset($allRelatedProducts[$offerMainProduct]) ? $allRelatedProducts[$offerMainProduct] : [];
            if (count($offerRelatedProducts) > 0) {
                foreach ($offerRelatedProducts as $offerRelatedProduct) {
                    if (isset($cartProductSku[$offerRelatedProduct])) {
                        $finalSkus[$offerRelatedProduct] = $cartProductSku[$offerRelatedProduct];
                    }
                }
            }
        }

        return $finalSkus;
    }

    private function getTotalQty($items)
    {
        $qty = 0;
        if (!count($items)) {
            return;
        }

        foreach ($items as $item) {
            $qty += $item->getQty();
        }
        return $qty;
    }
}

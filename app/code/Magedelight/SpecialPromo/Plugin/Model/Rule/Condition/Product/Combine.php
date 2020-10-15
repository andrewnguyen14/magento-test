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

namespace Magedelight\SpecialPromo\Plugin\Model\Rule\Condition\Product;

use Magedelight\SpecialPromo\Model\Source\DiscountType;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magedelight\SpecialPromo\Helper\Data as promoHelper;

class Combine
{

    const KEY_PRODUCT_ATTRIBUTE_COMBINATION = "Cart Item Attribute";

    private $ruleFreeItems = [];
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $promoHelper;
    protected $productRepository;
    protected $configurationHelper;
    private $ruleConditionIsOneOf = false;
    protected $ruleConditionSetOf = false;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        promoHelper $promoHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper
    ) {

        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->promoHelper = $promoHelper;
        $this->productRepository = $productRepository;
        $this->orderFactory = $orderFactory;
        $this->configurationHelper = $configurationHelper;
    }

    public function afterGetNewChildSelectOptions($subject, $result)
    {
        $productAttributeCombinationKey = $this->getProductAttributeCombinationKey($result);
        $cartItemAttribute = &$result[$productAttributeCombinationKey]['value'];
        $cartItemAttribute[] = [
            'value' => \Magedelight\SpecialPromo\Model\Rule\Condition\Product::class . '|' . 'sku',
            'label' => "XY Combination",
        ];

        $cartItemAttribute[] = [
            'value' => \Magedelight\SpecialPromo\Model\Rule\Condition\RelatedProduct::class . '|' . 'sku',
            'label' => "Related Product",
        ];

        $cartItemAttribute[] = [
            'value' => \Magedelight\SpecialPromo\Model\Rule\Condition\CustomOption::class . '|' . 'customoption',
            'label' => "Custom Option SKU",
        ];
        return $result;
    }

    protected function getProductAttributeCombinationKey($result)
    {
        foreach ($result as $key => $value) {
            if (is_array($value) && isset($value['label'])) {
                if ($value['label']->getText() == self::KEY_PRODUCT_ATTRIBUTE_COMBINATION) {
                    return $key;
                }
            }
        }
    }

    public function aroundValidate(
        \Magento\Rule\Model\Condition\Combine $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $item
    ) {
        $rule = $subject->getRule();
        $rule = $rule->load($rule->getId());
        /* if item is bundle and rule dicount calculation type is original price then do not validate */
        if ($item->getProductType() == 'bundle' && $rule->getDiscountCalculationType() == 0) {
            return false;
        }
        if (!$this->promoHelper->checkModuleEnable($rule)) {
            return false;
        }
        if ($rule->getSimpleAction() == DiscountType::BUY_X_GET_Y) {
            return $this->isFreeItem($rule, $item);
        }

        if ($rule->getSimpleAction() == DiscountType::BUY_X_GET_X) {
            return $this->hasBuyxGetX($rule, $item);
        }

        if ($rule->getSimpleAction() == DiscountType::EACH_NTH_ITEM_DISCOUNT) {
            if ($rule->getEachNth() < 1) {
                return false;
            }
            return $item->getQty() && $item->getQty() >= $rule->getEachNth();
        }
        if ($rule->getSimpleAction() == DiscountType::FIRST_ORDER) {
            return $this->isFirstOrder($item);
        }
        if ($rule->getSimpleAction() == DiscountType::NTH_ORDER_DISCOUNT) {
            return $this->isnthOrder($rule, $item);
        }
        return $proceed($item);
    }

    private function hasBuyxGetX($rule, $item)
    {
        $x = $rule->getDiscountStep();
        $y = $rule->getYQty();

        if (!$x) {
            return false;
        }
        $buyAndDiscountQty = $x + $y;

        $qty = $rule->getDiscountQty() > 0 ? min($item->getQty(), $rule->getDiscountQty()) : $item->getQty();
        $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
        $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;
        $discountQty = $fullRuleQtyPeriod * $y;
        if ($freeQty > $x) {
            $discountQty += $freeQty - $x;
        }

        if ($discountQty > 0) {
            return true;
        }
        return false;
    }

    private function isFirstOrder($item)
    {
        $customerId = $item->getQuote()->getCustomerId();
        if (!$customerId) {
            return false;
        }
        $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('customer_id', $customerId)->create();

        $orders = $this->orderRepository->getList($searchCriteria);
        return ($orders->getSize()) ? false : true;
    }

    protected function getRuleItems($item, $rule)
    {
        $allItems = $this->getCartItems($item);
        return $this->fiterRuleItems($allItems, $rule);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    protected function getCartItems($item)
    {
        return $item->getQuote()->getAllItems();
    }

    /**
     * @param array $allItems
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function fiterRuleItems($allItems, $rule)
    {
        foreach ($allItems as $key => $item) {
            if ($item->getParentItemId()) {
                unset($allItems[$key]);
            }
        }
        return $allItems;
    }

    private function isFreeItem($rule, $item)
    {
        $this->getFreeItemsFromRule($rule);

        if (empty($this->ruleFreeItems)) {
            return false;
        }

        $itemSku = $item->getProduct()->getData('sku');

        if (in_array($itemSku, $this->ruleFreeItems)) {
            return $this->validateItemQty($rule, $item);
        }
        return (in_array($itemSku, $this->ruleFreeItems));
    }

    protected function validateItemQty($rule, $item)
    {
        $skus = $this->getCartItemsSkuAndQty($item);
        $yQty = $rule->getYQty();
        $qty = $item->getQty();
        $itemSku = $item->getProduct()->getData('sku');
        $allItems = $item->getQuote()->getAllVisibleItems();
        $allSkus = [];
        $allSkusWithQty = [];
        $finalQty = [];
        foreach ($this->ruleFreeItems as $key => $freeItems) {
            if ($itemSku == $freeItems) {
                $originalItemSku[] = $key;
            }
        }
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
                $finalQty[] = floor(min(array_values($allSkusWithQty)) / $rule->getDiscountStep());
            }
            if (!empty($finalQty)) {
                $qty = min($finalQty);
                if ($qty > 0) {
                    return true;
                }
            }
        } else {
            $originalItem = $this->checkIfOriginalItemExists($itemSku, $this->ruleFreeItems, $skus, $rule);
            
            if ($originalItem && $qty >= $yQty) {
                return true;
            }
        }

        return false;
    }

    protected function checkIfOriginalItemExists($freeItem, $ruleItems, $cartItems, $rule)
    {
        $flag = false;
        foreach ($ruleItems as $orignalItem => $item) {
            if ($item == $freeItem && array_key_exists($orignalItem, $cartItems)) {
                if ($cartItems[$orignalItem] >= (int) $rule->getDiscountStep() && $orignalItem !== $item) {
                    if ($this->ruleConditionIsOneOf) {
                        return true;
                    } else {
                        $flag = true;
                    }
                } else {
                    return false;
                }
            }
        }

        return $flag;
    }

    protected function getCartItemsSkuAndQty($item)
    {
        $skus = [];
        $allItems = $item->getQuote()->getAllVisibleItems();
        foreach ($allItems as $rowItem) {
            $itemSku = $rowItem->getProduct()->getData('sku');
            $skus[$itemSku] = $rowItem->getQty() * 1;
        }
        return $skus;
    }

    private function isnthOrder($rule, $item)
    {
        $customerId = $item->getQuote()->getCustomerId();
        if (!$customerId) {
            return;
        }
        $orderStatusExist = false;
        $this->orders = $this->orderFactory->create()->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customerId);
        $ruleAction = $rule->getConditions()->asArray();

        if (isset($ruleAction['conditions'])) {
            foreach ($ruleAction['conditions'] as $condition) {
                if ($condition['type'] == 'Magedelight\SpecialPromo\Model\Rule\Condition\Order' && $condition['attribute'] == 'order_status') {
                    $orderStatusExist = true;
                    if ($condition['operator'] == '==') {
                        $this->orders->addAttributeToFilter('status', $condition['value']);
                    } else {
                        $this->orders->addAttributeToFilter('status', ['neq' => $condition['value']]);
                    }
                }
            }
        }

        $count = count($this->orders);

        return ($rule->getEachNth() > 0 && ($count + 1) % $rule->getEachNth() == 0) ? true : false;
    }

    private function getFreeItemsFromRule($rule)
    {
        $this->ruleFreeItems = null;
        $ruleAction = $rule->getActions()->asArray();
        if (isset($ruleAction['conditions'])) {
            foreach ($ruleAction['conditions'] as $condition) {
                if (isset($condition['free'])) {
                    if ($condition['operator'] == '()' || $condition['operator'] == '{{}}') {
                        if ($condition['operator'] == '()') {
                            $this->ruleConditionIsOneOf = true;
                        }
                        $skus = explode(',', $condition['value']);
                        foreach ($skus as $sku) {
                            $this->ruleFreeItems[trim($sku)] = $condition['free'];
                        }
                        if ($condition['operator'] == '{{}}') {
                            $this->ruleConditionSetOf = true;
                        }
                    } else {
                        $this->ruleFreeItems[$condition['value']] = $condition['free'];
                    }
                }
            }
        }
        return $this;
    }
}

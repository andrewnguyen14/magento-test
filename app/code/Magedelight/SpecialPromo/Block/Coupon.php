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

namespace Magedelight\SpecialPromo\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SalesRulesCollection;
use Magedelight\SpecialPromo\Model\Source\DiscountType;
use Magedelight\SpecialPromo\Helper\Data as promoHelper;
use Magento\SalesRule\Model\Validator as SalesRuleValidator;
use Magento\SalesRule\Model\Utility;

class Coupon extends \Magento\Checkout\Block\Cart\Coupon
{

    /**
     * @var SalesRulesCollection
     */
    protected $salesRuleCollection;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SalesRuleValidator
     */
    protected $salesValidator;

    /**
     * @var array
     */
    protected $ruleFreeItems = [];

    /**
     * @var promoHelper
     */
    private $promoHelper;
    protected $productRepository;
    protected $dateTimeZone;
    protected $productMetadata;
    protected $ruleConditionSetOf = false;

    /**
     * @var array
     */
    private $additionalRuleValidateFor = [
        DiscountType::MOST_CHEAPEST,
        DiscountType::MOST_EXPENSIVE,
        DiscountType::FIRST_ORDER,
        DiscountType::DISCOUNT_ON_EACH_SPENT,
        DiscountType::BUY_X_GET_Y,
        DiscountType::BUY_X_GET_X,
        DiscountType::NTH_ITEM_DISCOUNT,
        DiscountType::EACH_NTH_ITEM_DISCOUNT,
        DiscountType::NTH_ORDER_DISCOUNT,
        DiscountType::RELATED_PRODUCT_DISCOUNT,
    ];

    /**
     * Coupon constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param SalesRulesCollection $salesRuleCollection
     * @param promoHelper $promoHelper
     * @param SalesRuleValidator $salesRuleValidator
     * @param Utility $validatorUtility
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        SalesRulesCollection $salesRuleCollection,
        promoHelper $promoHelper,
        SalesRuleValidator $salesRuleValidator,
        Utility $validatorUtility,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Stdlib\DateTime\Timezone $dateTimeZone,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->storeManager = $context->getStoreManager();
        $this->date = $context->getLocaleDate();
        $this->_isScopePrivate = true;
        $this->salesRuleCollection = $salesRuleCollection;
        $this->checkoutSession = $checkoutSession;
        $this->promoHelper = $promoHelper;
        $this->salesValidator = $salesRuleValidator;
        $this->validatorUtility = $validatorUtility;
        $this->productRepository = $productRepository;
        $this->dateTimeZone = $dateTimeZone;
        $this->productMetadata = $productMetadata;
        $this->getCouponList();
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getQuoteItems()
    {
        return $this->checkoutSession->getQuote()->getAllVisibleItems();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCouponList()
    {
        $validCouponCodes = [];
        $rules = $this->getSalesRules();
        if ($rules->getSize()) {
            foreach ($rules as $rule) {
                $ruleData = $rule->getData();
                if ($this->validateItems($rule)) {
                    $validCouponCodes[$rule->getCode()] = $ruleData;
                }

                $this->afterValidate($rule, $validCouponCodes);
            }
        }
        return $validCouponCodes;
    }

    /**
     * @param $rule
     * @param $validCouponCodes
     * @return void
     */
    private function afterValidate($rule, $validCouponCodes)
    {
        if ($this->getQuote()->getCouponCode() && $rule->getCode() == $this->getCouponCode() && !array_key_exists($rule->getCode(), $validCouponCodes)) {
            $this->getQuote()->setCouponCode('');
        }
    }

    /**
     * @param $rule
     * @return bool
     */
    private function validateItems($rule)
    {

        $items = $this->getQuoteItems();
        $rule->setValidItems(0)->setTotalAmount(0)->setValidQty(0);
        foreach ($items as $item) {
            $addressItem = $item->getAddress();
            if (!$this->validatorUtility->canProcessRule($rule, $addressItem)) {
                return false;
            }
            if (!$rule->getConditions()->validate($addressItem)) {
                return false;
            }
            if ($rule->getActions()->validate($item) && $this->additionalValidate($rule, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $coupon
     * @return bool
     */
    public function isSelectedCoupon($coupon)
    {
        return $this->getCouponCode() == $coupon;
    }

    /**
     * @param $rule
     * @param $item
     * @return bool
     */
    private function additionalValidate(&$rule, $item)
    {
        if (!$this->needAdditionalValidate($rule)) {
            return true;
        }
        if ($rule->getSimpleAction() == DiscountType::DISCOUNT_ON_EACH_SPENT) {
            $rule->setTotalAmount($rule->getTotalAmount() + ($item->getQty() * $this->salesValidator->getItemBasePrice($item)));
            $shippingAmount = $this->checkoutSession->getQuote()->getShippingAddress()->getShippingAmount();
            $taxAmount = $this->checkoutSession->getQuote()->getShippingAddress()->getTaxAmount();
            $total = $rule->getTotalAmount();
            if ($total >= $rule->getDiscountStep()) {
                return true;
            }
            return false;
        }

        if ($rule->getSimpleAction() == DiscountType::BUY_X_GET_Y) {
            return $this->hasFreeItemInCart($rule, $item);
        }

        if ($rule->getSimpleAction() == DiscountType::BUY_X_GET_X) {
            return $this->hasBuyxGetX($rule, $item);
        }

        if ($rule->getSimpleAction() == DiscountType::NTH_ITEM_DISCOUNT) {
            if ($rule->getEachNth() < 1) {
                return false;
            }
            $rule->setValidQty($rule->getValidQty() + $item->getQty());
        }
        $rule->setValidItems($rule->getValidItems() + 1);

        if ($rule->getValidItems() >= $rule->getDiscountStep()) {
            if ($rule->getSimpleAction() == DiscountType::NTH_ITEM_DISCOUNT && $rule->getValidQty() < $rule->getEachNth()) {
                return false;
            }
            return true;
        }
        return false;
    }

    private function hasBuyxGetX($rule, $item)
    {
        $x = $rule->getDiscountStep();
        $y = $rule->getYQty();

        if (!$x) {
            return false;
        }
        $buyAndDiscountQty = $x + $y;

        $qty = $rule->getDiscountQty() > 0 ?min($item->getQty(), $rule->getDiscountQty()):$item->getQty();
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

    /**
     * @param $rule
     * @param $item
     * @return bool
     */
    private function hasFreeItemInCart($rule, $item)
    {
        $this->getFreeItemsFromRule($rule);
        $skus = $this->getCartItemsSku();
        $itemSku = $item->getProduct()->getData('sku');
        $allSkus = [];
        $allSkusWithQty = [];
        $allItems = $item->getQuote()->getAllVisibleItems();
        $originalItemSku = [];

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

            if (count(array_intersect(array_keys($allSkusWithQty), $originalItemSku)) == count($originalItemSku)) {
                return true;
            }
        } else {
            if (in_array($itemSku, $this->ruleFreeItems) && in_array($itemSku, array_unique($skus))) {
                return $this->checkIfOriginalItemExists($itemSku, $this->ruleFreeItems, array_unique($skus));
            }
        }

        return false;
    }

    /**
     * @param $item
     * @return array
     */
    protected function getCartItemsSku()
    {
        $skus = [];
        $allItems = $this->getQuoteItems();
        foreach ($allItems as $rowItem) {
            $itemSku = $rowItem->getProduct()->getData('sku');

            array_push($skus, $itemSku);
        }
        return $skus;
    }

    /**
     * @param $rule
     * @return $this
     */
    private function getFreeItemsFromRule($rule)
    {
        $this->ruleFreeItems = null;
        $ruleAction = $rule->getActions()->asArray();
        if (isset($ruleAction['conditions'])) {
            foreach ($ruleAction['conditions'] as $condition) {
                if (isset($condition['free'])) {
                    if ($condition['operator'] == '()' || $condition['operator'] == '{{}}') {
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

    /**
     * @param $freeItem
     * @param $ruleFreeItemsArray
     * @param $cartSkus
     * @return bool
     */
    private function checkIfOriginalItemExists($freeItem, $ruleFreeItemsArray, $cartSkus)
    {
        $bool = false;
        foreach ($ruleFreeItemsArray as $orignalItem => $item) {
            if ($item == $freeItem && in_array($orignalItem, $cartSkus)) {
                $bool = true;
            }
        }
        return $bool;
    }

    /**
     * @param $rule
     * @return bool
     */
    private function needAdditionalValidate($rule)
    {
        if (in_array($rule->getSimpleAction(), $this->additionalRuleValidateFor)) {
            return true;
        }
        return false;
    }

    /**
     * @return SalesRulesCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSalesRules()
    {
        $quote = $this->checkoutSession->getQuote();
        $store = $this->storeManager->getStore($quote->getStoreId());

        $collection = $this->salesRuleCollection->create()
                        ->addWebsiteGroupDateFilter($store->getWebsiteId(), $quote->getCustomerGroupId())
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('coupon_type', 2)
                        ->addFieldToFilter('use_auto_generation', 0);
        
        $edition = $this->productMetadata->getEdition(); //Enterprise or Community
       
        if ($edition == 'Enterprise') {
            $now = strtotime($this->dateTimeZone->formatDatetime(date("Y-m-d H:i:s")));
            $collection->getSelect()->where('main_table.created_in <= ?', $now);
            $collection->getSelect()->where('main_table.updated_in > ?', $now);
            $collection->getSelect()->setPart('disable_staging_preview', true);
        }
        
        return $collection;
    }

    /**
     * @param $data
     * @return \Magento\Framework\Phrase
     */
    public function getDescription($data)
    {
        if ($this->promoHelper->dispayDiscription() && isset($data['description'])) {
            return __($data['description']);
        }
    }

    /**
     * @param $data
     * @return \Magento\Framework\Phrase
     */
    public function getExpirationDate($data)
    {
        if ($this->promoHelper->dispayValidTIll() && isset($data['to_date']) && $data['to_date'] != '') {
            return __("Valid Till %1", $this->date->date(strtotime($data['to_date']))->format('jS F, Y'));
        }
    }
}

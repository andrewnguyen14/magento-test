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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class FirstOrder extends AbstractDiscount
{
    protected $orderRepository;
    
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $qty, $rulePercent);

        return $discountData;
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
        
        if ($this->isFirstOrder($item)) {
            return $this->getDiscountData($rule, $item, $qty, true);
        }

        return $discountData;
    }
    
    public function canProcessRule($Item, $rule)
    {
        return $this->isFirstOrder($Item);
    }


    private function isFirstOrder($item)
    {
        $customerId = $item->getQuote()->getCustomerId();
        if (!$customerId) {
            return;
        }
        $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)->create();
        
        $orders = $this->orderRepository->getList($searchCriteria);
        return ($orders->getSize())?false:true;
    }
}

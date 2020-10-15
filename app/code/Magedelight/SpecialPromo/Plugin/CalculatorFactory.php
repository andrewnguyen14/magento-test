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

namespace Magedelight\SpecialPromo\Plugin;

use Magedelight\SpecialPromo\Model\Source\DiscountType;

class CalculatorFactory
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    protected $discountType;
    protected $discountTypeArray = [];
    protected $promoHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DiscountType $discountType,
        \Magedelight\SpecialPromo\Helper\Data $promoHelper
    ) {

        $this->objectManager = $objectManager;
        $this->discountType = $discountType;
        $this->discountTypeArray = $this->discountType->getDiscountType();
        $this->promoHelper = $promoHelper;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
     * @param callable                                                        $proceed
     * @param                                                                 $type
     *
     * @return mixed
     */
    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        if (isset($this->discountTypeArray[$type])) {
            return $this->objectManager->create($this->discountType->getClassByType($type));
        }
        return $proceed($type);
    }
}

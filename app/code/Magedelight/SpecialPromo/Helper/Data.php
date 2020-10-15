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

namespace Magedelight\SpecialPromo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magedelight\SpecialPromo\Model\Source\DiscountType;

class Data extends AbstractHelper
{
    const XML_PATH_SPECIALPROMO_ACTIVE = 'special_promo/general/enable';
    const XML_PATH_DISPAY_BUTTON = 'special_promo/display/discount_button';
    const XML_PATH_DISPAY_DISCRIPTION = 'special_promo/display/dispay_discription';
    const XML_PATH_DISPAY_VALID_TILL = 'special_promo/display/display_valid_till';
    
    protected $scopeConfig;

    protected $discountType;

    public function __construct(
        Context $context,
        DiscountType $discountType
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->discountType = $discountType;
    }
    
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SPECIALPROMO_ACTIVE, ScopeInterface::SCOPE_STORE);
    }
    public function dispayApplyButton()
    {
        if (!$this->isModuleEnable()) {
            return false;
        }
        return $this->scopeConfig->getValue(self::XML_PATH_DISPAY_BUTTON, ScopeInterface::SCOPE_STORE);
    }
    public function dispayDiscription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DISPAY_DISCRIPTION, ScopeInterface::SCOPE_STORE);
    }
    public function dispayValidTIll()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DISPAY_VALID_TILL, ScopeInterface::SCOPE_STORE);
    }
    public function checkModuleEnable($rule)
    {
        if (!$this->isModuleEnable()) {
            $types = $this->discountType->getDiscountType();
            return (isset($types[$rule->getSimpleAction()]))?false:true;
        }
        return true;
    }
}

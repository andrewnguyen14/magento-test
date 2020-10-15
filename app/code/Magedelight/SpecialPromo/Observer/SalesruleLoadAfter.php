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

use Magedelight\SpecialPromo\Model\RuleAdditionalFactory;

class SalesruleLoadAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected $ruleAdditional;
    protected $specialPromoHelper;
    protected $additionalDataFields = [
        'discount_calculation_type',
        'y_qty',
        'each_nth'
        ];

    public function __construct(RuleAdditionalFactory $ruleAdditionalFactory, \Magedelight\SpecialPromo\Helper\Data $specialPromoHelper
    )
    {
        $this->ruleAdditional = $ruleAdditionalFactory;
        $this->specialPromoHelper = $specialPromoHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getEvent()->getRule();
        $model = $this->ruleAdditional->create()->loadByRuleId($rule);
        if ($this->specialPromoHelper->isModuleEnable()) {
            foreach ($this->additionalDataFields as $fields) {
                $rule->setData($fields, $model->getData($fields));
            }
        }
    }
}

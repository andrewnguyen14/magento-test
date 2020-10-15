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

class SalesrulePrepareSave implements \Magento\Framework\Event\ObserverInterface
{
    
    protected $ruleAdditional;

    protected $additionalDataFields = [
        'discount_calculation_type',
        'y_qty',
        'each_nth'
    ];
    
    public function __construct(RuleAdditionalFactory $ruleAdditionalFactory)
    {
        $this->ruleAdditional = $ruleAdditionalFactory;
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
        $data = $rule->getData();
        $model = $this->ruleAdditional->create()->loadByRuleId($rule);
        foreach ($this->additionalDataFields as $fields) {
            if (isset($data[$fields])) {
                $model->setData($fields, $data[$fields]);
            }
        }
        if (!empty($model->getData())) {
            $model->setRuleId($rule->getId());
            $model->save();
        }
    }
}

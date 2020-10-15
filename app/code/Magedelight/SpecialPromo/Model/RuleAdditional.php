<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Model;

use Magento\SalesRule\Model\Rule as SalesRule;

class RuleAdditional extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magedelight\SpecialPromo\Model\ResourceModel\RuleAdditional');
    }
    
    public function loadByRuleId($rule)
    {
        if ($rule instanceof SalesRule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = $rule;
        }
        $this->load($ruleId, 'rule_id');
        return $this;
    }
}

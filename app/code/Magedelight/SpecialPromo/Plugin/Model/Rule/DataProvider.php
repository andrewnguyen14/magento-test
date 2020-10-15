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

namespace Magedelight\SpecialPromo\Plugin\Model\Rule;

use Magento\SalesRule\Model\Rule\DataProvider as SalesRuleDataProvider;
use Magedelight\SpecialPromo\Model\RuleAdditionalFactory;

class DataProvider
{
    
    protected $ruleAdditional;
    
    public function __construct(RuleAdditionalFactory $ruleAdditionalFactory)
    {
        $this->ruleAdditional = $ruleAdditionalFactory;
    }

    public function afterGetData(SalesRuleDataProvider $subject, $result)
    {
        if ($result != null) {
            $id = key($result);
            $model = $this->ruleAdditional->create()->loadByRuleId($id);
            if ($model->getId()) {
                $result[$id] = array_merge($result[$id], $model->getData());
            }
        }
        return $result;
    }
}

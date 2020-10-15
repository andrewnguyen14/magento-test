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

namespace Magedelight\SpecialPromo\Plugin\Model\Condition;

use Magento\SalesRule\Model\Rule\DataProvider as SalesRuleDataProvider;
use Magedelight\SpecialPromo\Model\RuleAdditionalFactory;

class AbstractCondition
{
    public function afterGetDefaultOperatorOptions(\Magento\Rule\Model\Condition\AbstractCondition $subject, $result)
    {
        $result['{{}}'] = __('set of');
        return $result;
    }
}

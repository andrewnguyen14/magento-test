<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Controller\Adminhtml\SpecialPromo;

use Magento\Backend\App\Action;

class Chooser extends Action
{
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        switch ($request->getParam('attribute')) {
            case 'customer_id':
                $block = $this->_view->getLayout()->createBlock(
                    \Magedelight\SpecialPromo\Block\Adminhtml\SpecialPromo\Widget\Chooser\Customer::class,
                    'promo_widget_chooser_customer',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;
            default:
                $block = false;
                break;
        }
        
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}

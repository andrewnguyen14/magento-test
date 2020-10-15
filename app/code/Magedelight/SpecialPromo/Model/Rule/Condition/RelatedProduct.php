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

namespace Magedelight\SpecialPromo\Model\Rule\Condition;

class RelatedProduct extends \Magento\SalesRule\Model\Rule\Condition\Product
{

    public function asHtml()
    {
        $html = $this->getTypeElementHtml();
        $html .= 'Product SKU' . $this->getOperatorElementHtml();
        $html .= $this->setCustomAttribute('relatedproduct')->getValueElement()->getHtml() .
                $this->getChooserContainerHtml() .
                $this->getRemoveLinkHtml();
        return $html;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType = [
                'relatedproduct' => ['()', '!()', '{}', '!{}']
            ];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'relatedproduct';
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $allVisibleItems = $model->getQuote()->getAllVisibleItems();
        $itemSku = [];
        $finalItemSku = [];
        $relatedItemSku = [];
        foreach ($allVisibleItems as $item) {
            $relatedproducts = $item->getProduct()->getRelatedProductCollection();
            if (count($relatedproducts) > 0) {
                foreach ($relatedproducts as $relatedproduct) {
                    $relatedItemSku[$item->getProduct()->getData('sku')][] = $relatedproduct->getSku();
                }
            }
            $itemSku[] = $item->getProduct()->getData('sku');
        }
        if (count($relatedItemSku) > 0) {
            foreach ($relatedItemSku as $mainProduct => $relatedProducts) {
                if (count($relatedProducts) > 0) {
                    foreach ($relatedProducts as $relatedProduct) {
                        if (in_array($relatedProduct, $itemSku)) {
                            $finalItemSku[] = $mainProduct;
                        }
                    }
                }
            }
        }

        return $this->validateAttribute($finalItemSku);
    }
}

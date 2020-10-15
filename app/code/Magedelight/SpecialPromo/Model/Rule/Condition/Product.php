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

class Product extends \Magento\SalesRule\Model\Rule\Condition\Product
{

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElementHtml();
        $html .= $this->getAttributeElementHtml() .
                $this->getOperatorElementHtml() .
                $this->setCustomAttribute('sku')->getValueElement()->getHtml();

        $html .= 'then Y SKU to discount';
        $html .= $this->setCustomAttribute('free')->getValueElement()->getHtml() .
                $this->getRemoveLinkHtml() .
                $this->getChooserContainerHtml();
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
                'sku' => ['==', '()', '{{}}']
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
        return 'sku';
    }

    /**
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'free' => $this->getFree(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
        return $out;
    }

    /**
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadArray($arr)
    {
        $this->setType($arr['type']);
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $this->setOperator(isset($arr['operator']) ? $arr['operator'] : false);
        $this->setValue(isset($arr['value']) ? $arr['value'] : false);
        $this->setFree(isset($arr['free']) ? $arr['free'] : false);
        $this->setIsValueParsed(isset($arr['is_value_parsed']) ? $arr['is_value_parsed'] : false);
        return $this;
    }

    public function getValueElement()
    {
        if ($this->getCustomAttribute() == 'free') {
            $elemName = $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][free]';
            $id = $this->getPrefix() . '__' . $this->getId() . '-free__value';
            $value = $this->getFree();
            $valueName = ($this->getFree()) ? $this->getFree() : '...';
        } else {
            $elemName = $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]';
            $id = $this->getPrefix() . '__' . $this->getId() . '__value';
            $value = $this->getValue();
            $valueName = $this->getValueName();
        }

        $elementParams = [
            'name' => $elemName,
            'value' => $value,
            'values' => $this->getValueSelectOptions(),
            'value_name' => $valueName,
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName()
        ];

        return $this->getForm()->addField(
            $id,
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
            $this->getValueElementRenderer()
        );
    }
}

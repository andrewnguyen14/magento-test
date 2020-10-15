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

class ProductAbstractType
{

    public function afterGetOrderOptions(\Magento\Catalog\Model\Product\Type\AbstractType $subject, $result, $product)
    {
        $options = $result;
        $itemInfoBuyRequest = $product->getCustomOption('info_buyRequest');
        ;
        $itemDataSerialized = $itemInfoBuyRequest->getData('value');
        $itemData = [];
        $selectOptions = [
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO,
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ];
        $multiSelectOptions = [
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
            \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ];
        if ($itemDataSerialized) {
            $itemData = json_decode($itemDataSerialized, true);
        }
        $optionSku = [];
        if (isset($itemData['options'])) {
            foreach ($itemData['options'] as $optionId => $value) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    if (in_array($option->getType(), $selectOptions)) {
                        foreach ($option->getValues() as $optionvalue) {
                            if (in_array($option->getType(), $multiSelectOptions)) {
                                foreach ($value as $valueId) {
                                    if ($optionvalue->getOptionTypeId() == $valueId) {
                                        $optionSku[$optionId][] = $optionvalue->getSku();
                                    }
                                }
                            } else {
                                if ($optionvalue->getOptionTypeId() == $value) {
                                    $optionSku[$optionId] = $optionvalue->getSku();
                                }
                            }
                        }
                    } else {
                        $optionSku[$optionId] = $option->getSku();
                    }
                }
            }
            if (isset($options['options'])) {
                foreach ($options['options'] as $key => $option) {
                    $result['options'][$key]['sku'] = is_array($optionSku[$option['option_id']]) ? implode(',', $optionSku[$option['option_id']]) : $optionSku[$option['option_id']];
                }
            }
        }
        return $result;
    }
}

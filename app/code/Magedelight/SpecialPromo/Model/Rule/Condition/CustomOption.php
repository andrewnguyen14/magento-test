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

class CustomOption extends \Magento\SalesRule\Model\Rule\Condition\Product
{

    protected $configurationHelper;

    public function __construct(\Magento\Rule\Model\Condition\Context $context, \Magento\Backend\Helper\Data $backendData, \Magento\Eav\Model\Config $config, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Catalog\Model\ResourceModel\Product $productResource, \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection, \Magento\Framework\Locale\FormatInterface $localeFormat, \Magento\Catalog\Helper\Product\Configuration $configurationHelper, array $data = [], \Magento\Catalog\Model\ProductCategoryList $categoryList = null)
    {
        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data, $categoryList);
        $this->configurationHelper = $configurationHelper;
    }

    public function asHtml()
    {
        $html = $this->getTypeElementHtml();
        $html .= 'Custom Option SKU' . $this->getOperatorElementHtml();
        $html .= $this->setCustomAttribute('customoption')->getValueElement()->getHtml() .
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
                'customoption' => ['()', '!()', '{}', '!{}']
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
        return 'customoption';
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
            'attribute' => 'customoption',
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
        return $out;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $product = $model->getProduct();

        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $product = $this->productRepository->getById($model->getProductId());
        }

        $options = $product->getTypeInstance(true)->getOrderOptions($product);


        if (isset($options['options'])) {
            $customOptions = $options['options'];
            foreach ($customOptions as $customOption) {
                $itemOptionSkusArray = explode(',', $customOption['sku']);
                foreach ($itemOptionSkusArray as $itemOptionSku) {
                    $itemOptionSkus[] = $itemOptionSku;
                }
            }

            return $this->validateAttribute($itemOptionSkus);
        }
    }
}

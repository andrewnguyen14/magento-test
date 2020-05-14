<?php

namespace FNL\Address\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */

    public function __construct
    (
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->eavConfig            = $eavConfig;
        $this->_eavSetupFactory     = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /**
         * Add custom Customer Address attribute 'address_type'
         */
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        $customerEntity = $this->eavConfig->getEntityType('customer_address');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $eavSetup->addAttribute('customer_address', 'andrew_address_type',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Andrew Address Type',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
            'sort_order' => 1000,
            'position' => 1000,
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'andrew_address_type')
            ->addData
            ([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' =>
                    [
                        'adminhtml_customer_address',
                        'customer_address_edit', 
                        'customer_register_address'
                    ]
            ]);

        $customAttribute->save();

        $setup->endSetup();
    }
}
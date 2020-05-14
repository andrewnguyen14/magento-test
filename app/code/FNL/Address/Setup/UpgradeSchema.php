<?php
namespace FNL\Address\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context )
    {
        $installer = $setup;

        $installer->startSetup();


        if(version_compare($context->getVersion(), '1.0.1', '<'))
        {
            $eavTable1 = $installer->getTable('quote_address');
            $eavTable2 = $installer->getTable('sales_order_address');

            $columns =
            [
                'andrew_address_type' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Andrew Address Type',
                ]
            ];

            $connection = $installer->getConnection();

            foreach($columns as $name => $definition)
            {
                $connection->addColumn($eavTable1, $name, $definition);
                $connection->addColumn($eavTable2, $name, $definition);
            }
        }



        $installer->endSetup();
    }
}
<?php

namespace FNL\FedexImport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'FedEx'
         */
        $table = $installer->getConnection()->newTable
        (
            $installer->getTable('fedex')
        )
        ->addColumn
        (
            'fedex_tracking_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Fedex Tracking Number'
        )
        ->setComment("Fedex Table");
        
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
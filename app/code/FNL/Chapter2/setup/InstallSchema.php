<?php

namespace FNL\Chapter2\Setup;

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
         * Create table 'Meals'
         */
        $table = $installer->getConnection()->newTable
        (
            $installer->getTable('meals')
        )
        ->addColumn
        (
            'meal_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Meal ID'
        )
        ->addColumn
        (
            'meal_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Meal Name'
        )
        ->setComment("Meal Table");
        
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
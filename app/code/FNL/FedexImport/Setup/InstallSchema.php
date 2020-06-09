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
				->addColumn(
					'test_order_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Test Order Id'
        ) 
        ->addColumn(
					'email',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'Email'
				)
        ->addColumn(
					'tracking',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'Tracking'
				)

        ->setComment("Fedex Table");
        
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
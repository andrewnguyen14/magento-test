<?php
namespace FNL\Chapter2\Setup;

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
          $setup -> getConnection() -> addColumn (
            $setup -> getTable ('meals'),
            'description',
            [
              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              'length' => 16,
              'nullable' => false,
              'default' => '',
              'comment' => 'Description'
            ]
          );
        }

        $installer->endSetup();
    }
}
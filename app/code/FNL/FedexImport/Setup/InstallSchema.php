<?php
  
namespace FNL\FedexImport\Setup;
  
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
  
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('fedex')) {
            $table = $installer->getConnection()
                     ->newTable($installer->getTable('fedex'))
                     ->addColumn(
                         'fedex_id',
                         \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                         null,
                         [
                             'identity' => true,
                             'nullable' => false,
                             'primary'  => true,
                             'unsigned' => true,
                         ],
                         'ID'
                     )
                     ->addColumn(
                         'name',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                         255,
                         ['nullable => false'],
                         'Name'
                     )
                     ->addColumn(
                         'email',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                         255,
                         [],
                         'Email'
                     )
                     ->addColumn(
                         'url_key',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                         255,
                         [],
                         'URL Key'
                     )
                     ->addColumn(
                         'message',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                         '18K',
                         [],
                         'Message'
                     )
                     ->addColumn(
                         'status',
                         \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                         1,
                         [],
                         'Fedex Status'
                     )
                     ->addColumn(
                         'created_at',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                         null,
                         ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                         'Created At'
                     )->addColumn(
                         'updated_at',
                         \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                         null,
                         ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                         'Updated At')
                     ->setComment('fedex Table');
            $installer->getConnection()->createTable($table);
  
            $installer->getConnection()->addIndex(
                $installer->getTable('fedex'),
                $setup->getIdxName(
                    $installer->getTable('fedex'),
                    ['name', 'email', 'url_key', 'message'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'email', 'url_key', 'message'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
<?php
namespace FNL\Chapter2\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
      $setup->startSetup();

        if($context->getVersion() && version_compare($context->getVersion(), '1.0.1', '<'))
        {
          $table = $setup -> getTable('meals');
          $setup -> getConnection()
            -> insertForce($table, ['meal_name' => 'Vegan', 'description' => 'Awesome Meal']);
        }

        $setup->endSetup();
    }
}
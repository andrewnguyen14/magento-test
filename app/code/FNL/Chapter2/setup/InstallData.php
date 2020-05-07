<?php

namespace FNL\Chapter2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
          ['meal_name' => 'Protein Plus'],
          ['meal_name' => 'Keto']
      ];

        foreach ($data as $dataRow)
        {
            $setup->getConnection()->insert($setup->getTable('meals'), $dataRow);
        }

        $setup->endSetup();
    }
}
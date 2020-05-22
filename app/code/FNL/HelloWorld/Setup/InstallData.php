<?php

namespace FNL\HelloWorld\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
          ['name' => 'How to Create Controller', 'url_key' => '', 'post_content' => 'Lorem Ipsum', 'tags' => '', 'status' => '1'],
          ['name' => 'CRUD', 'url_key' => '', 'post_content' => 'Andrew was here', 'tags' => '', 'status' => '1']
      ];

        foreach ($data as $dataRow)
        {
            $setup->getConnection()->insert($setup->getTable('mageplaza_helloworld_post'), $dataRow);
        }

        $setup->endSetup();
    }
}
<?php
  
// namespace FNL\FedexImport\Setup;
  
// use Magento\Framework\Setup\InstallDataInterface;
// use Magento\Framework\Setup\ModuleDataSetupInterface;
// use Magento\Framework\Setup\ModuleContextInterface;
  
// class InstallData implements InstallDataInterface
// {
//     protected $_fedexFactory;
  
//     public function __construct(\FNL\FedexImport\Model\FedexFactory $fedexFactory)
//     {
//         $this->_fedexFactory = $fedexFactory;
//     }
  
//     public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
//     {
//         $data = [
//           [          
//             'name' => 'Thomus Raj',
//             'email' => 'roni_cost@xample.com',
//             'url_key' => 'http://www.blogtreat.com',
//             'message' => 'My first comment',
//             'status' => 1
//           ],
//           [
//             'name' => 'Andrew Nguyen',
//             'email' => 'andrewnguyen14@gmail.com',
//             'url_key' => 'http://www.andrewnguyen14.com',
//             'message' => 'My second comment',
//             'status' => 1
//           ]
//         ];
//         foreach ($data as $dataRow)
//         {
//           $post = $this->_fedexFactory->create();
//           $post->addData($dataRow)->save();
//         }
//     }
// }
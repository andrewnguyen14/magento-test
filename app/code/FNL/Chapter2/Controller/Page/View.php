<?php 

namespace FNL\Chapter2\Controller\Page;

use Magento\Framework\App\Action\Context;

class View extends \Magento\Framework\App\Action\Action{
  
  protected $resultJsonFactory;

  public function __construct (
    Context $context,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) 
    {
    $this->resultJsonFactory = $resultJsonFactory;
    parent::__construct($context);
    }

  public function execute() {
    $result = $this->resultJsonFactory->create();
    $data = ['message'=>"Hello World"];

    return $result->setData($data);
  }
}
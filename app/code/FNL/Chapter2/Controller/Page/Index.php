<?php
namespace FNL\Chapter2\Controller\Page;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_mealFactory;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $pageFactory,
            \FNL\Chapter3\Model\mealsFactory $mealFactory
            )
    {
            $this->_pageFactory = $pageFactory;
            $this->_mealFactory = $mealFactory;
            return parent::__construct($context);
    }

    public function execute()
    {
            $meals = $this->_mealFactory->create();
            $collection = $meals->getCollection()->setPageSize(2);
            foreach($collection as $item){
                    echo "<pre>";
                    print_r($item->getData());
                    echo "</pre>";
            }
            exit();
            return $this->_pageFactory->create();
    }
}
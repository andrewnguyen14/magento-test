<?php
namespace FNL\FedexImport\Block;
class Index extends \Magento\Framework\View\Element\Template
{
	protected $_fedexFactory;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\FNL\FedexImport\Model\FedexFactory $fedexFactory
	)
	{
		$this->_fedexFactory = $fedexFactory;
		parent::__construct($context);
	}

	public function getFedexCollection(){
		$fedex = $this->_fedexFactory->create();
		return $fedex->getCollection();
	}
}
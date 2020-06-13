<?php
namespace FNL\FedexImport\Model\ResourceModel;


class Fedex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
    $this->_init('fedex', 'fedex_id');
	}
	
}
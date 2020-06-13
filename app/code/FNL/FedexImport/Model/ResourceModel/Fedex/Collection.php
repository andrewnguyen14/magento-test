<?php
namespace FNL\FedexImport\Model\ResourceModel\Fedex;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'fedex_id';
	protected $_eventPrefix = 'fedex_collection';
	protected $_eventObject = 'fedex_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('FNL\FedexImport\Model\Fedex', 'FNL\FedexImport\Model\ResourceModel\Fedex');
	}

}
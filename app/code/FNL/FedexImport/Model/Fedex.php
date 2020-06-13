<?php
namespace FNL\FedexImport\Model;
class Fedex extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'fedex';

	protected $_cacheTag = 'fedex';

	protected $_eventPrefix = 'fedex';

	protected function _construct()
	{
		$this->_init('FNL\FedexImport\Model\ResourceModel\Fedex');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
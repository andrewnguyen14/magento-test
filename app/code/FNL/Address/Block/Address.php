<?php
namespace FNL\Address\Block;
class Address extends \Magento\Framework\View\Element\Template
{
	// public function sayHello()
	// {
	// 	return __('Hello World');
	// }
	public function getAllOptions() {
		if ($this->_options === null) {
				$this->_options = [
						['label' => __('--Select--'), 'value' => ''],
						['label' => __('Residential'), 'value' => 'Residential'],
						['label' => __('Business'), 'value' => 'Business']
				];
		}
		return $this->_options;
}
}
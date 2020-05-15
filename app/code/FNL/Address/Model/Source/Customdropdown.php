<?php
 
namespace FNL\Address\Model\Source;
 
class Customdropdown extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource 
{
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
<?php

namespace FNL\Chapter3\Model\ResourceModel\Meals;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init('FNL\Chapter3\Model\Meals', 'FNL\Chapter3\Model\ResourceModel\Meals');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
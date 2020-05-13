<?php

namespace FNL\Chapter3\Model\ResourceModel;


class Meals extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('meals', 'meal_id');
    }
}
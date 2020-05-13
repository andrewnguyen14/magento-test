<?php

namespace FNL\Chapter3\Model;

class Meals extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('FNL\Chapter3\Model\ResourceModel\Meals');
    }
}
<?php
namespace FNL\FedexImport\Model\Import\FedexImport;
  
interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_TITLE = 'InvalidValueTITLE';
    const ERROR_ID_IS_EMPTY = 'Empty';
  
    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init($context);
}
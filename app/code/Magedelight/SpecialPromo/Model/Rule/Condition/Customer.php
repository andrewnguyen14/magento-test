<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Model\Rule\Condition;

use Magento\Customer\Api\AccountManagementInterface;

class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer $customer
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Customer\Model\ResourceModel\Customer $customer,
        \Magento\Eav\Model\Config $eavConfig,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        array $data = []
    ) {
        $this->_backendData = $backendData;
        $this->eavConfig = $eavConfig;
        $this->customer = $customer;
        $this->accountManagement = $accountManagement;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $data);
    }

    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType['firstname'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['middlename'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['lastname'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['email'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['customer_id'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['tax_vat'] = ['==', '!=', '{}', '!{}', '()', '!()'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'customer_id' => __("Customer ID"),
            'confirmation' => __('Customer Confirmation'),
            'email' => __('E-Mail'),
            'firstname' => __('First Name'),
            'middlename' => __('Middle Name'),
            'lastname' => __('Last Name'),
            'customer_dob' => __('Date Of Birth'),
            'gender' => __('Gender'),
            'customer_created_at' => __('Created At'),
            'tax_vat' => __('Tax/Vat Number')
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() == 'customer_id') {
            return 'customer_id';
        }
        if ($this->getAttribute() == 'email') {
            return 'email';
        }
        switch ($this->getAttribute()) {
            case 'customer_created_at':
            case 'customer_dob':
                return 'date';
            case 'gender':
            case 'confirmation':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'gender':
            case 'confirmation':
                return 'select';
            case 'customer_created_at':
            case 'customer_dob':
                return 'date';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'gender':
                    $options = $this->customer->getAttribute('gender')->getSource()->getAllOptions();
                    break;
                case 'confirmation':
                    $options = ["account_confirmed" => 'Confirmed', 'account_confirmation_required' => 'Confirmation Required', 'account_confirmation_not_required' => 'Confirmation Not Required'];
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'customer_id':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                    $image .
                    '" alt="" class="v-middle rule-chooser-trigger" title="' .
                    __(
                        'Open Chooser'
                    ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve value element chooser URelse {
      $this->setValue(
      (new \DateTime($this->getData('value')))->format('Y-m-d H:i:s')
      );
      $this->setIsValueParsed(true);
      }L
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'customer_id':
                $url = 'magedelight_specialpromo/specialpromo/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'customer_id':
                return true;
            default:
                break;
        }
        return false;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if($model instanceof \Magento\Quote\Model\Quote\Address) {
            $customerId = $model->getQuote()->getCustomerId();
        } else {
            $customerId = $model->getCustomerId();
        }        
        $customer = $this->customerRepositoryInterface->getById($customerId);


        if ('customer_id' == $this->getAttribute() && $customerId) {
            $model->setCustomerId($customerId);
        }
        if ('email' == $this->getAttribute() && $customerId) {
            $model->setEmail($customer->getEmail());
        }
        if ('firstname' == $this->getAttribute() && $customerId) {
            $model->setFirstname($customer->getFirstname());
        }
        if ('middlename' == $this->getAttribute() && $customerId) {
            $model->setMiddlename($customer->getMiddlename());
        }
        if ('lastname' == $this->getAttribute() && $customerId) {
            $model->setLastname($customer->getLastname());
        }
        if ('gender' == $this->getAttribute() && $customerId) {
            $model->setGender($customer->getGender());
        }
        if ('customer_dob' == $this->getAttribute() && $customerId) {
            $model->setCustomerDob((new \DateTime($customer->getDob()))->format('Y-m-d H:i:s'));
        }
        if ('customer_created_at' == $this->getAttribute() && $customerId) {
            $model->setCustomerCreatedAt((new \DateTime($customer->getCreatedAt()))->format('Y-m-d 00:00:00'));
        }
        if ('confirmation' == $this->getAttribute() && $customerId) {
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customerId);
            $model->setConfirmation($confirmationStatus);
        }
        if ('tax_vat' == $this->getAttribute() && $customerId) {
            $model->setTaxVat($customer->getTaxvat());
        }
        return parent::validate($model);
    }
}

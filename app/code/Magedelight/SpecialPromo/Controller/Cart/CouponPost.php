<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SpecialPromo\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\SalesRule\Model\CouponFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class CouponPost extends Action
{

    protected $quoteRepository;
    protected $couponFactory;
    protected $resultJsonFactory;
    protected $cart;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        CouponFactory $couponFactory,
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        PageFactory $resultPageFactory
    ) {
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $resultPage = $this->resultPageFactory->create();
        
        $couponCode = $this->getRequest()->getParam('remove') == 1 ? '' : trim($this->getRequest()->getParam('coupon_code'));

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return;
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }
            
            $mdCouponSection = $resultPage->getLayout()
                ->createBlock('Magedelight\SpecialPromo\Block\Coupon')
                ->setTemplate('Magedelight_SpecialPromo::checkout/cart/couponForm.phtml');
            
            $mdCouponSection->setCouponCode($couponCode);
            
            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccess(__('You used coupon code "%1".', $escaper->escapeHtml($couponCode)));
                        $result->setData([
                            'status' => 'success',
                            'couponsection' => $mdCouponSection->toHtml(),
                            'couponstatus' => 'applied',
                            'message' => __('You used coupon code "%1".', $escaper->escapeHtml($couponCode))
                        ]);
                    } else {
                        $this->messageManager->addError(__('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)));
                        $result->setData([
                            'status' => 'error',
                            'couponsection' => $mdCouponSection->toHtml(),
                            'couponstatus' => '',
                            'message' => __('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode))
                        ]);
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccess(__('You used coupon code "%1".', $escaper->escapeHtml($couponCode)));
                        $result->setData([
                            'status' => 'success',
                            'couponsection' => $mdCouponSection->toHtml(),
                            'couponstatus' => 'applied',
                            'message' => __(__('You used coupon code "%1".', $escaper->escapeHtml($couponCode)))
                        ]);
                    } else {
                        $this->messageManager->addError(__('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)));
                        $result->setData([
                            'status' => 'error',
                            'couponsection' => $mdCouponSection->toHtml(),
                            'couponstatus' => '',
                            'message' => __('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode))
                        ]);
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
                $result = $result->setData([
                    'status' => 'success',
                    'couponsection' => $mdCouponSection->toHtml(),
                    'couponstatus' => '',
                    'message' => __('You canceled the coupon code.')
                ]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        } catch (\Exception $e) {
            $result = $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $result;
    }
}

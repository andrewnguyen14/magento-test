<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 *
 * @author      Ryan Hoerr <info@paradoxlabs.com>
 * @license     http://store.paradoxlabs.com/license.html
 */

namespace ParadoxLabs\Subscriptions\Observer\Payment;

/**
 * CheckTokenBaseActiveObserver Class
 */
class CheckTokenBaseActiveObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \ParadoxLabs\Subscriptions\Model\Service\Payment
     */
    protected $paymentService;

    /**
     * @var \ParadoxLabs\TokenBase\Api\CardRepositoryInterface
     */
    protected $cardRepository;

    /**
     * CheckTokenBaseActiveObserver constructor.
     *
     * @param \ParadoxLabs\Subscriptions\Model\Service\Payment $paymentService
     * @param \ParadoxLabs\TokenBase\Api\CardRepositoryInterface $cardRepository
     */
    public function __construct(
        \ParadoxLabs\Subscriptions\Model\Service\Payment $paymentService,
        \ParadoxLabs\TokenBase\Api\CardRepositoryInterface $cardRepository
    ) {
        $this->paymentService = $paymentService;
        $this->cardRepository = $cardRepository;
    }

    /**
     * Before running a subscription order, verify that the tokenbase card is active and usable.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getData('quote');

        try {
            if ($quote instanceof \Magento\Quote\Model\Quote
                && !empty($quote->getPayment()->getData('tokenbase_id'))
                && $this->paymentService->isTokenBaseMethod($quote->getPayment()->getMethod())) {
                // This could throw an exception, but if it does the order will fail eventually anyway, so that's okay.
                $card = $this->cardRepository->getById(
                    $quote->getPayment()->getData('tokenbase_id')
                );

                if ((int)$card->getActive() === 0) {
                    throw new \Magento\Framework\Exception\PaymentException(__('Card is not active.'));
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\PaymentException(
                __('There is no payment information configured for the order.'),
                $e
            );
        }
    }
}

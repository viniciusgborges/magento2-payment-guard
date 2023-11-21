<?php

namespace Vbdev\PaymentGuard\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Vbdev\PaymentGuard\Service\OrderService;
use Vbdev\PaymentGuard\Service\OrderAttemptsService;

class PaymentInfoManagement
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderAttemptsService $orderAttemptsService
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface                      $paymentMethod,
        AddressInterface                      $billingAddress = null
    ): void {
        if ($this->orderService->validate() || $this->orderAttemptsService->validateAttempts()) {
            throw new LocalizedException(__("The order can't be placed."));
        }
    }
}

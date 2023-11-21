<?php

namespace Vbdev\PaymentGuard\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Vbdev\PaymentGuard\Service\OrderService;
use Vbdev\PaymentGuard\Service\OrderAttemptsService;

class GuestPaymentInfoManagement
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
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface                           $paymentMethod,
        AddressInterface                           $billingAddress = null
    ): void {
        if ($this->orderService->validate(true) || $this->orderAttemptsService->validateAttempts(true)) {
            throw new LocalizedException(__("The order can't be placed."));
        }
    }
}

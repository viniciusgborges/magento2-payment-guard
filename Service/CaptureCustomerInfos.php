<?php

namespace Vbdev\PaymentGuard\Service;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CaptureCustomerInfos
{
    public function __construct(
        protected LoggerInterface       $logger,
        protected RemoteAddress         $remoteAddress,
        protected Session               $session,
        protected StoreManagerInterface $storeManager,
    ) {
    }

    public function getRemoteIp(): ?string
    {
        return $this->remoteAddress?->getRemoteAddress();
    }

    public function getCustomerId(): ?int
    {
        return $this->session?->getCustomerId();
    }

    public function getCustomerEmail(): ?string
    {
        return $this->session->getCustomer()?->getEmail();
    }

    public function getStoreName(): string
    {
        try {
            return $this->storeManager->getStore()->getName();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }
}

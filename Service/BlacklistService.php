<?php

namespace Vbdev\PaymentGuard\Service;

use Vbdev\PaymentGuard\Model\PaymentGuardBlacklist as ModelPaymentGuardBlacklist;
use Vbdev\PaymentGuard\Model\PaymentGuardBlacklistFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardBlacklist;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardBlacklistFactory as ResourcePaymentGuardBlacklistFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardBlacklist\CollectionFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardBlacklist\Collection;
use Exception;
use Psr\Log\LoggerInterface;

class BlacklistService
{
    public function __construct(
        protected PaymentGuardBlacklistFactory         $modelPaymentGuardBlacklistFactory,
        protected ResourcePaymentGuardBlacklistFactory $resourcePaymentGuardBlacklistFactory,
        protected PaymentGuardBlacklist                $resourcePaymentGuardBlacklist,
        protected CollectionFactory                    $collectionFactory,
        protected LoggerInterface                      $logger,
    ) {
    }

    public function addToBlacklist($userIp): void
    {
        try {
            $paymentGuardBlacklistCollection = $this->getPaymentGuardBlacklistCollection();
            $paymentGuardBlacklistCollection->addFieldToFilter('user_ip', $userIp);
            if (!$paymentGuardBlacklistCollection->getSize()) {
                $paymentGuardBlacklistModel = $this->getPaymentGuardBlacklistModel();
                $paymentGuardBlacklistModel
                    ->setData('user_ip', $userIp);
                $this->getResourcePaymentGuardBlacklist()->save($paymentGuardBlacklistModel);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function removeFromBlacklist($userIp): void
    {
        try {
            $paymentGuardBlacklistCollection = $this->getPaymentGuardBlacklistCollection();
            $paymentGuardBlacklistCollection->addFieldToFilter('user_ip', $userIp);
            if ($paymentGuardBlacklistCollection->getSize()) {
                $paymentGuardBlacklist = $paymentGuardBlacklistCollection->getFirstItem();
                /** @var ModelPaymentGuardBlacklist $paymentGuardBlacklist */
                $this->resourcePaymentGuardBlacklist->delete($paymentGuardBlacklist);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    protected function getPaymentGuardBlacklistModel(): ModelPaymentGuardBlacklist
    {
        return $this->modelPaymentGuardBlacklistFactory->create();
    }

    protected function getResourcePaymentGuardBlacklist(): PaymentGuardBlacklist
    {
        return $this->resourcePaymentGuardBlacklistFactory->create();
    }

    protected function getPaymentGuardBlacklistCollection(): Collection
    {
        return $this->collectionFactory->create();
    }
}

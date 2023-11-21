<?php

namespace Vbdev\PaymentGuard\Cron;

use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts\CollectionFactory;
use Psr\Log\LoggerInterface;
use Exception;

class ClearPaymentGuardOrderAttempts
{
    public function __construct(
        protected CollectionFactory $collectionFactory,
        protected LoggerInterface   $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $orderAttemptsCollection = $this->collectionFactory->create();
            $connection = $orderAttemptsCollection->getConnection();
            $tableName = $orderAttemptsCollection->getMainTable();
            $connection->delete(
                $tableName,
                ['created_at < ?' => date('Y-m-d H:i:s', strtotime('-30 day'))]
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

    }
}

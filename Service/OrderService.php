<?php

namespace Vbdev\PaymentGuard\Service;

use Vbdev\PaymentGuard\Model\Config;
use Exception;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;

class OrderService
{
    public function __construct(
        protected OrderCollectionFactory  $orderCollection,
        protected LoggerInterface         $logger,
        protected Config                  $paymentGuardConfig,
        protected CaptureCustomerInfos    $captureCustomerInfos,
        protected PaymentGuardLogsService $guardLogsService
    ) {
    }

    public function validate(bool $guest = false): bool
    {
        try {
            if ($this->paymentGuardConfig->getEnabled()) {
                $remoteIp = $this->captureCustomerInfos->getRemoteIp();

                if ($this->guardLogsService->checkIfUserIsOnBlacklist($this->guardLogsService->getBlacklistStatusByUserIp($remoteIp))) {
                    return true;
                }

                $customerId = $guest ? null : $this->captureCustomerInfos->getCustomerId();

                $time = $this->paymentGuardConfig->getTimeInterval();
                $qty = $this->paymentGuardConfig->getTransactionLimit();
                $resultByCustomerId = 0;
                if (!empty($customerId)) {
                    $resultByCustomerId = $this->getOrderResultCountByCustomerId($customerId, $time);
                }
                $resultByRemoteIp = $this->getOrderResultCountByRemoteIp($remoteIp, $time);

                if ($resultByCustomerId > $qty || $resultByRemoteIp > $qty) {
                    $userEmails = $this->getUserEmails($remoteIp);
                    $this->guardLogsService->populatePaymentGuardLogs($remoteIp, $userEmails);
                    return true;
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }
        return false;
    }

    private function getOrderResultCountByCustomerId(int $customerId, string $time): int
    {
        try {
            $orderResultCollection = $this->getOrderResultCollection($time);
            $orderResultCollection->addFieldToFilter('customer_id', $customerId);
            return $orderResultCollection->getSize();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    private function getOrderResultCountByRemoteIp(string $remoteIp, string $time): int
    {
        try {
            $orderResultCollection = $this->getOrderResultCollection($time);
            $orderResultCollection->addFieldToFilter('remote_ip', $remoteIp);
            return $orderResultCollection->getSize();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    private function getOrderResultCollection(string $time): OrderCollection|int
    {
        try {
            $dateFor = strtotime("-$time minutes");
            $dateFor = date("Y-m-d H:i:s", $dateFor);
            $dateTo = date('Y-m-d H:i:s');
            $orderCollection = $this->getOrderCollection();
            $orderCollection->addFieldToFilter('created_at', ['from' => $dateFor, 'to' => $dateTo]);

            return $orderCollection;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    public function getUserEmails($remoteIp): string|int
    {
        try {
            $orderCollection = $this->getOrderCollection();
            if (!$orderCollection && !$orderCollection->getSize()) {
                return 0;
            }
            $orderCollection->addFieldToFilter('remote_ip', $remoteIp);
            $orderCollection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns('customer_email')
                ->group(array('customer_email'));
            return implode('<br>', $orderCollection->getColumnValues('customer_email'));
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    protected function getOrderCollection(): OrderCollection
    {
        return $this->orderCollection->create();
    }
}

<?php

namespace Vbdev\PaymentGuard\Service;

use Vbdev\PaymentGuard\Model\PaymentGuardLogs;
use Vbdev\PaymentGuard\Model\PaymentGuardLogsFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs as ResourcePaymentGuardLogs;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogsFactory as ResourcePaymentGuardLogsFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs\Collection;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs\CollectionFactory;
use Vbdev\PaymentGuard\Service\CaptureCustomerInfos;
use Exception;
use Psr\Log\LoggerInterface;

class PaymentGuardLogsService
{
    public function __construct(
        protected LoggerInterface                 $logger,
        protected PaymentGuardLogsFactory         $paymentGuardLogs,
        protected ResourcePaymentGuardLogsFactory $resourcePaymentGuardLogs,
        protected CollectionFactory               $collectionFactory,
        protected CaptureCustomerInfos            $captureCustomerInfos
    ) {
    }

    public function populatePaymentGuardLogs(string $remoteIp): void
    {
        try {
            $paymentGuardModelLogCollection = $this->getPaymentGuardLogCollection();
            $paymentGuardModelLogCollection->addFieldToFilter('user_ip', $remoteIp);
            $userEmail = $this->captureCustomerInfos->getCustomerEmail();
            if (!$paymentGuardModelLogCollection->getSize()) {
                $this->createNewPaymentGuardLog($remoteIp, $userEmail);
            } else {
                $this->updateExistingPaymentGuardLog($paymentGuardModelLogCollection, $userEmail);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function createNewPaymentGuardLog(string $remoteIp, ?string $userEmail): void
    {
        try {
            $paymentGuardLogModel = $this->getPaymentGuardLogModel();
            $paymentGuardLogModel
                ->setUserEmails($userEmail)
                ->setUserIp($remoteIp)
                ->setStore($this->captureCustomerInfos->getStoreName())
                ->setBlacklistStatus('unlocked')
                ->setAttempts(1);

            $this->getResourcePaymentGuardLogModel()->save($paymentGuardLogModel);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function updateExistingPaymentGuardLog(Collection $paymentGuardLogCollection, ?string $userEmail): void
    {
        try {
            $paymentGuardLogItem = $paymentGuardLogCollection->getFirstItem();

            $emailIsOnGrid = $userEmail
                ? $this->checkIfUserEmailIsOnGrid($paymentGuardLogCollection, $userEmail)
                : true;

            if (!$emailIsOnGrid) {
                $paymentGuardLogItem->getUserEmails()
                    ? $paymentGuardLogItem->setUserEmails($paymentGuardLogItem->getUserEmails() . '<br>' . $userEmail)
                    : $paymentGuardLogItem->setUserEmails($userEmail);
            }

            $attempts = $paymentGuardLogItem->getAttempts();
            $attempts++;
            $paymentGuardLogItem->setAttempts($attempts);
            $paymentGuardLogItem->setLastAttempt();
            /** @var PaymentGuardLogs $paymentGuardLogItem */
            $this->getResourcePaymentGuardLogModel()->save($paymentGuardLogItem);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function getBlacklistStatusByUserIp($remoteIp): Collection|int
    {
        try {
            $paymentGuardLogCollection = $this->getPaymentGuardLogCollection();
            return $paymentGuardLogCollection->addFieldToFilter('user_ip', $remoteIp);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    public function checkIfUserIsOnBlacklist(Collection|int $resultCollection): bool
    {
        try {
            if ($resultCollection->getSize()) {
                $status = $resultCollection->getFirstItem()->getBlacklistStatus();
                if ($status == 'blocked') {
                    return true;
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }
        return false;
    }

    public function checkIfUserEmailIsOnGrid(Collection $paymentGuardLogCollection, string $userEmail)
    {
        $gridUserEmails = explode('<br>', $paymentGuardLogCollection->getFirstItem()?->getUserEmails() ?? '');
        return in_array($userEmail, $gridUserEmails, true);
    }

    public function getPaymentGuardLogModel(): PaymentGuardLogs
    {
        return $this->paymentGuardLogs->create();
    }

    public function getPaymentGuardLogCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function getResourcePaymentGuardLogModel(): ResourcePaymentGuardLogs
    {
        return $this->resourcePaymentGuardLogs->create();
    }
}

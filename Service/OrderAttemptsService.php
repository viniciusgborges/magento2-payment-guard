<?php

namespace Vbdev\PaymentGuard\Service;

use Vbdev\PaymentGuard\Model\Config;
use Vbdev\PaymentGuard\Model\PaymentGuardOrderAttempts as ModelPaymentGuardOrderAttempts;
use Vbdev\PaymentGuard\Model\PaymentGuardOrderAttemptsFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts\CollectionFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts\Collection;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttemptsFactory as ResourcePaymentGuardOrderAttemptsFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts;
use Psr\Log\LoggerInterface;
use Exception;

class OrderAttemptsService
{
    public function __construct(
        protected PaymentGuardOrderAttemptsFactory         $modelPaymentGuardOrderAttemptsFactory,
        protected ResourcePaymentGuardOrderAttemptsFactory $resourcePaymentGuardOrderAttemptsFactory,
        protected PaymentGuardOrderAttempts                $resourcePaymentGuardOrderAttempts,
        protected CollectionFactory                        $collectionFactory,
        protected Config                                   $paymentGuardConfig,
        protected LoggerInterface                          $logger,
        protected CaptureCustomerInfos                     $captureCustomerInfos,
        protected PaymentGuardLogsService                  $guardLogsService,
        protected OrderService                             $orderService
    ) {
    }

    public function validateAttempts(bool $guest = false): bool
    {
        try {
            if ($this->paymentGuardConfig->getEnabled()) {
                $customerId = $guest ? null : $this->captureCustomerInfos->getCustomerId();
                $remoteIp = $this->captureCustomerInfos->getRemoteIp();
                $userEmail = $this->captureCustomerInfos->getCustomerEmail();
                $this->createAttempt($customerId, $remoteIp, $userEmail);

                if ($this->guardLogsService->checkIfUserIsOnBlacklist($this->guardLogsService->getBlacklistStatusByUserIp($remoteIp))) {
                    return true;
                }

                $attemptsLimit = $this->paymentGuardConfig->getAttemptsLimit();
                $attemptsTime = $this->paymentGuardConfig->getAttemptsInterval();
                $resultByCustomerId = 0;
                if (!empty($customerId)) {
                    $resultByCustomerId = $this->getUserAttemptsByUserId($customerId, $attemptsTime);
                }
                $resultByRemoteIp = $this->getUserAttemptsByRemoteIp($remoteIp, $attemptsTime);

                if ($resultByCustomerId > $attemptsLimit || $resultByRemoteIp > $attemptsLimit) {
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

    protected function createAttempt(?string $customerId, string $remoteIp, string $userEmail): void
    {
        try {
            $paymentGuardAttemptsModel = $this->getPaymentGuardOrderAttemptsModel();
            $paymentGuardAttemptsModel
                ->addData(
                    [
                        'user_id' => $customerId,
                        'user_email' => $userEmail,
                        'user_ip' => $remoteIp
                    ]
                );
            $this->getResourcePaymentGuardOrderAttempts()->save($paymentGuardAttemptsModel);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    protected function getUserAttemptsByUserId(int $customerId, string $time): int
    {
        try {
            $attemptsResultCollection = $this->getAttemptsResultAfterTimerFilter($time);
            if (!$attemptsResultCollection) {
                return 0;
            }
            $attemptsResultCollection->addFieldToFilter('user_id', $customerId);
            return $attemptsResultCollection->getSize();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    protected function getUserAttemptsByRemoteIp(string $remoteIp, string $time): int
    {
        try {
            $attemptsResultCollection = $this->getAttemptsResultAfterTimerFilter($time);
            if (!$attemptsResultCollection) {
                return 0;
            }
            $attemptsResultCollection->addFieldToFilter('user_ip', $remoteIp);
            return $attemptsResultCollection->getSize();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    protected function getAttemptsResultAfterTimerFilter(string $time): Collection|int
    {
        try {
            $dateFor = strtotime("-$time minutes");
            $dateFor = date("Y-m-d H:i:s", $dateFor);
            $dateTo = date('Y-m-d H:i:s');
            $attemptsCollection = $this->getPaymentGuardOrderAttemptsCollection();
            $attemptsCollection->addFieldToFilter('created_at', ['from' => $dateFor, 'to' => $dateTo]);

            return $attemptsCollection;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    public function getUserEmails(string $remoteIp): string|int
    {
        try {
            $attemptsCollection = $this->getPaymentGuardOrderAttemptsCollection();
            if (!$attemptsCollection->getSize()) {
                return 0;
            }
            $attemptsCollection->addFieldToFilter('user_ip', $remoteIp);
            $attemptsCollection->getSelect()
                ->group(array('user_email'));
            return implode('<br>', $attemptsCollection->getColumnValues('user_email'));
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return 0;
        }
    }

    protected function getPaymentGuardOrderAttemptsCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    protected function getPaymentGuardOrderAttemptsModel(): ModelPaymentGuardOrderAttempts
    {
        return $this->modelPaymentGuardOrderAttemptsFactory->create();
    }

    protected function getResourcePaymentGuardOrderAttempts(): PaymentGuardOrderAttempts
    {
        return $this->resourcePaymentGuardOrderAttemptsFactory->create();
    }
}

<?php

namespace Vbdev\PaymentGuard\Controller\Adminhtml\Logs;

use Vbdev\PaymentGuard\Model\PaymentGuardLogs;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Vbdev\PaymentGuard\Service\BlacklistService;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs as PaymentGuardLogsResource;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs\CollectionFactory;

class MassRemoveFromBlacklist extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Vbdev_PaymentGuard::remove_from_blacklist';

    protected Filter $filter;
    protected CollectionFactory $collectionFactory;
    protected PaymentGuardLogsResource $paymentGuardLogsResource;
    protected BlacklistService $blacklistService;

    public function __construct(
        Context                  $context,
        Filter                   $filter,
        CollectionFactory        $collectionFactory,
        PaymentGuardLogsResource $paymentGuardLogsResource,
        BlacklistService         $blacklistService
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->paymentGuardLogsResource = $paymentGuardLogsResource;
        $this->blacklistService = $blacklistService;
    }

    public function execute(): Redirect
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $itemsSize = 0;
            foreach ($collection->getItems() as $paymentGuardLogs) {
                $this->blacklistService->removeFromBlacklist($paymentGuardLogs->getUserIp());
                if ($paymentGuardLogs->getBlacklistStatus() == 'blocked') {
                    $paymentGuardLogs->setBlacklistStatus('unlocked');
                    /** @var PaymentGuardLogs $paymentGuardLogs */
                    $this->paymentGuardLogsResource->save($paymentGuardLogs);
                    $itemsSize++;
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while remove items from blacklist %1. %2', $paymentGuardLogs->getId(), $e->getMessage())
            );
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been removed from blacklist', $itemsSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('paymentguard/logs');
    }
}

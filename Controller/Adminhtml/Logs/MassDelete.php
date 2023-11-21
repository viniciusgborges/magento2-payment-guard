<?php

namespace Vbdev\PaymentGuard\Controller\Adminhtml\Logs;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs\CollectionFactory;
use Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs as PaymentGuardLogsResource;
use Vbdev\PaymentGuard\Model\PaymentGuardLogs;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Vbdev_PaymentGuard::mass_delete';

    protected CollectionFactory $collectionFactory;

    protected Filter $filter;
    protected PaymentGuardLogsResource $paymentGuardLogsResource;

    public function __construct(
        Context                  $context,
        CollectionFactory        $collectionFactory,
        Filter                   $filter,
        PaymentGuardLogsResource $paymentGuardLogsResource
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->paymentGuardLogsResource = $paymentGuardLogsResource;
    }

    public function execute(): Redirect
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $itemsSize = $collection->getSize();
            foreach ($collection->getItems() as $paymentGuardLogs) {
                /** @var PaymentGuardLogs $paymentGuardLogs */
                $this->paymentGuardLogsResource->delete($paymentGuardLogs);
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while deleting log %1. %2', $paymentGuardLogs->getId(), $e->getMessage())
            );
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted', $itemsSize));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('paymentguard/logs');
    }
}

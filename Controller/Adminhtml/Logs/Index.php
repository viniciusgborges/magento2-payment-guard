<?php

declare(strict_types=1);

namespace Vbdev\PaymentGuard\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Vbdev_PaymentGuard::logs';

    protected $pageFactory;

    public function __construct(
        Context     $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Vbdev_PaymentGuard::logs');
        $page->getConfig()->getTitle()->prepend(__('Payment Guard Logs'));
        return $page;
    }
}

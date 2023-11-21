<?php

namespace Vbdev\PaymentGuard\Model\ResourceModel;

class PaymentGuardOrderAttempts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vbdev_payment_guard_order_attempts', 'entity_id');
    }
}

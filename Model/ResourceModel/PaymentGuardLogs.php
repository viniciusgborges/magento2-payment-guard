<?php

namespace Vbdev\PaymentGuard\Model\ResourceModel;

class PaymentGuardLogs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vbdev_payment_guard_logs', 'entity_id');
    }
}

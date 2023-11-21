<?php

namespace Vbdev\PaymentGuard\Model\ResourceModel;

class PaymentGuardBlacklist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vbdev_payment_guard_blacklist', 'entity_id');
    }
}

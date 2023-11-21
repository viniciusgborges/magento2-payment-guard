<?php

namespace Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Vbdev\PaymentGuard\Model\PaymentGuardOrderAttempts::class,
            \Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardOrderAttempts::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}

<?php

namespace Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs;

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
            \Vbdev\PaymentGuard\Model\PaymentGuardLogs::class,
            \Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardLogs::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}

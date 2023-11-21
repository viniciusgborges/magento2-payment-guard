<?php

declare (strict_types=1);

namespace Vbdev\PaymentGuard\Model;

use Magento\Framework\Model\AbstractModel;

class PaymentGuardBlacklist extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const NOROUTE_ENTITY_ID = 'no-route';
    const ENTITY_ID = 'entity_id';
    const CACHE_TAG = 'vbdev_payment_guard_blacklist';

    protected $_cacheTag = 'vbdev_payment_guard_blacklist';

    protected $_eventPrefix = 'vbdev_payment_guard_blacklist';

    protected function _construct()
    {
        $this->_init(\Vbdev\PaymentGuard\Model\ResourceModel\PaymentGuardBlacklist::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRoute();
        }
        return parent::load($id, $field);
    }

    public function noRoute()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    public function setId($id): PaymentGuardBlacklist
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}

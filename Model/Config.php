<?php

namespace Vbdev\PaymentGuard\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const XML_CONFIG_PATH = 'payment_guard/vbdev_config/';
    public const XML_CONFIG_MODULE_ENABLED_PATH = self::XML_CONFIG_PATH . 'module_enable';
    public const XML_CONFIG_TRANSACTION_LIMIT_PATH = self::XML_CONFIG_PATH . 'transaction_limit';
    public const XML_CONFIG_TIME_INTERVAL_PATH = self::XML_CONFIG_PATH . 'time_interval';
    public const XML_CONFIG_ATTEMPTS_LIMIT_PATH = self::XML_CONFIG_PATH . 'attempts_limit';
    public const XML_CONFIG_ATTEMPTS_INTERVAL_PATH = self::XML_CONFIG_PATH . 'attempts_time_interval';

    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_MODULE_ENABLED_PATH);
    }

    public function getTransactionLimit(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONFIG_TRANSACTION_LIMIT_PATH);
    }

    public function getTimeInterval(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_TIME_INTERVAL_PATH);
    }

    public function getAttemptsLimit(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONFIG_ATTEMPTS_LIMIT_PATH);
    }

    public function getAttemptsInterval(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_ATTEMPTS_INTERVAL_PATH);
    }
}

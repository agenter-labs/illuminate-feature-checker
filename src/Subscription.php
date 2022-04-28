<?php

namespace AgenterLab\FeatureChecker;

use AgenterLab\FeatureChecker\Exceptions\FeatureException;
use AgenterLab\FeatureChecker\Exceptions\FeatureNotFoundException;
use AgenterLab\FeatureChecker\Exceptions\SubscriptionExpiredException;

class Subscription
{
    /**
     * @var int
     */
    private int $endDate;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var int
     */
    private int $ttl;

    /**
     * Create a new confide instance.
     *
     * @param int $id
     * @param int $endDate
     * @return void
     * @throws \AgenterLab\FeatureChecker\Exceptions\SubscriptionExpiredException
     */
    public function __construct(int $id, int $endDate)
    {
        $this->id = $id;
        $this->endDate = $endDate;

        $this->setTTL();
    }
    

    /**
     * Set ttl
     * 
     * @throws \AgenterLab\FeatureChecker\Exceptions\SubscriptionExpiredException
     */
    private function setTTL()
    {
        $this->ttl = $this->endDate - time();

        if ($this->ttl <= 0) {
            throw new SubscriptionExpiredException;
        }
    }

    /**
     * Check Allow feature
     * 
     * @param string $featureName
     * @param string|int|null $default
     * @throws \AgenterLab\FeatureChecker\Exceptions\FeatureException
     */
    public function allow(string $featureName, string|int|null $dfValue = null)
    {
        if (!$this->can($featureName, $dfValue)) {
            throw new FeatureException($featureName);
        }
    }

    /**
     * Check feature
     * 
     * @param string $featureName
     * @param string|int|null $default
     * @return bool
     */
    public function can(string $featureName, string|int|null $dfValue = null): bool
    {
        $feature = app('saas')->feature($this->id, $featureName, $this->ttl);

        if (!$feature) {
            return (bool)$dfValue;
        }

        // Explode feature dtype, value, usage
        list($dtype, $value, $usage) = $feature;

        $method = 'validate' . ucfirst($dtype);

        return $this->$method($value, $usage, $dfValue);
    }

    /**
     * Validate string
     */
    private function validateString($value, $usage, $dfValue): bool
    {
        return $value === $dfValue;
    }
    
    /**
     * Validate numeric
     */
    private function validateNumeric($value, $usage, $dfValue): bool
    {
        return (int)$value > (int)$usage;
    }

    /**
     * Validate options
     */
    private function validateOptions($value, $usage, $dfValue): bool
    {
        return in_array($dfValue, explode(',', $value));
    }

    /**
     * Record usage
     * 
     * @param string $featureName
     * @param int $newValue
     * @throws \AgenterLab\FeatureChecker\Exceptions\FeatureNotFoundException
     */
    public function recordUsage(string $featureName, int $newValue = 1)
    {
        $feature = app('saas')->feature($this->id, $featureName, $this->ttl);

        if (!$feature) {
            throw new FeatureNotFoundException;
        }

        // Explode feature dtype, value, usage
        list($dtype, $value, $usage) = $feature;

        if ($dtype != 'numeric') {
            return;
        }

        $usage = (int)$usage + $newValue;

        app('saas')->recordUsage($this->id, $featureName, $usage);
    }
}

<?php

namespace AgenterLab\FeatureChecker;

use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Config;
use AgenterLab\FeatureChecker\Exceptions\SubscriptionException;
use AgenterLab\FeatureChecker\Exceptions\FeatureNotFoundException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\DB;

class Saas
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private Repository $repository;

    // /**
    //  * @var bool
    //  */
    // private bool $isPrefix = false;

    /**
     * @var \AgenterLab\FeatureChecker\Subscription
     */
    private ?\AgenterLab\FeatureChecker\Subscription $subscription = null;

    /**
     * Create a new confide instance.
     *
     * @param string $storage
     * @return void
     */
    public function __construct(string $storage)
    {
        $this->repository = Cache::store($storage);

        // if ($this->repository instanceof \Illuminate\Cache\RedisStore) {
        //     $this->isPrefix = true;
        //     $this->repository->getStore()->setPrefix('');
        // }
    }

    /**
     * Get repository 
     * 
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * Get subscription 
     * 
     * @return \AgenterLab\FeatureChecker\Subscription
     * @throws \AgenterLab\FeatureChecker\Exceptions\SubscriptionException
     */
    public function subscription(int|string $id): Subscription
    {
        if (is_null($this->subscription)) {
            $this->subscription = $this->newInstance($id);
        }

        return $this->subscription;
    }

    /**
     * Get new subscription instance
     * 
     * @return \AgenterLab\FeatureChecker\Subscription
     * @throws \AgenterLab\FeatureChecker\Exceptions\SubscriptionException
     */
    public function newInstance(int|string $id): Subscription
    {

        // if ($this->isPrefix) {
        //     $this->repository->getStore()->setPrefix('');
        // }
    
        $ttl = $this->repository->get('subscription_' . $id);

        if (!$ttl) {
            throw new SubscriptionException("Subscription not exists");
        }

        // if ($this->isPrefix) {
        //     $this->repository->getStore()->setPrefix('sub_' . $id);
        // }
        
        return new Subscription($id, $ttl);
    }

    /**
     * Get feature 
     * 
     * @param int $id
     * @param string $name
     * @param int $ttl
     * @return null|array
     */
    public function feature(int|string $id, string $name): null|array
    {
        return $this->repository->get($id . '_' . $name);
    }

    /**
     * delete
     * 
     * @param int $id
     * @param array $features
     * @param bool $subscription
     */
    public function delete(int|string $id, array $features, bool $subscription = false) {

        $values = array_map(function($name) use($id) {
            return $id . '_' . $name;
        }, $features);

        if ($subscription) {
            $values[] = 'subscription_' . $id;
        }
        
        $this->repository->deleteMultiple($values);

        return true;
    }

    /**
     * Sync
     * 
     * @param int $id
     * @param int $ttl
     * @param array $features
     */
    public function sync(int|string $id, int $ttl, array $features) {

        $values = ['subscription_' . $id => $ttl];

        foreach($features as $feature) {
            $values[$id . '_' . $feature['name']] = [
                'd' => $feature['dtype'],
                'v' => $feature['value'],
                'u' => $feature['usage'] ?? 0
            ];
        }

        $this->repository->putMany($values , $ttl);
    }

    /**
     * Record usage
     * 
     * @param int $id
     * @param string $name
     * @param int $newValue
     * @param int $ttl
     */
    public function recordUsage(int|string $id, string $name, int $newValue, int $ttl)
    {

        $feature = $this->feature($id, $name);

        if (!$feature) {
            throw new FeatureNotFoundException;
        }

        if ($feature['d'] != 'numeric') {
            return;
        }

        $feature['u'] = (int)$feature['u'] + $newValue;

        $this->repository->set($id . '_' . $name, $feature, $ttl);
    }
}
<?php

namespace AgenterLab\FeatureChecker;

use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Config;
use AgenterLab\FeatureChecker\Exceptions\SubscriptionException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\DB;

class Saas
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private Repository $repository;

    /**
     * @var string
     */
    private string $subscriptionModelName;

    /**
     * @var string
     */
    private string $featureModelName;

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
    public function __construct(string $storage, string $subscriptionModelName, string $featureModelName)
    {
        $this->repository = Cache::store($storage);
        $this->subscriptionModelName = $subscriptionModelName;
        $this->featureModelName = $featureModelName;

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
    public function subscription(int $id): Subscription
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
    public function newInstance(int $id): Subscription
    {

        // if ($this->isPrefix) {
        //     $this->repository->getStore()->setPrefix('');
        // }
    
        $subscription = $this->repository->rememberForEver('subscription_' . $id, function () use($id) {
            $subscription = call_user_func($this->subscriptionModelName . '::firstWhere', [
                'id' => $id,
                'is_deleted' => 0
            ]);
            if (!$subscription) {
                throw new SubscriptionException("Subscription does not exists");
            }
            return ['end_date' => $subscription->getEndDate()];
        });

        // if ($this->isPrefix) {
        //     $this->repository->getStore()->setPrefix('sub_' . $id);
        // }
        
        return new Subscription($id, $subscription['end_date']);
    }

    /**
     * Get feature 
     * 
     * @param int $id
     * @param string $name
     * @param int $ttl
     * @return null|array
     */
    public function feature(int $id, string $name, int $ttl): null|array
    {

        $key = $id . '_' . $name;

        $feature = $this->repository->remember($key, $ttl, function () use($id, $name) {

            $feature = call_user_func($this->featureModelName . '::firstWhere', [
                'subscription_id' => $id,
                'name' => $name
            ]);
            if (!$feature) {
                return null;
            }

            return implode('|', [$feature->dtype, $feature->value, $feature->usage]);
        });

        if ($feature) {
            $feature = explode('|', $feature, 3);
        }

        return $feature;
    }

    /**
     * delete
     * 
     * @param int $id
     */
    public function delete(int $id) {
        
        $subscription = call_user_func($this->subscriptionModelName . '::firstWhere', 'id', $id);

        if (!$subscription) {
            return;
        }

        $features = $subscription->features->pluck('name')->all();

        foreach($features as $feature) {
            $this->repository->delete($id . '_' . $feature);
        }

        $tableName = (new $this->featureModelName)->getTable();
        DB::table($tableName)->where('subscription_id', $id)->delete();

        $subscription->fill(['is_deleted' => 1])->save();
        $this->repository->delete('subscription_' . $id);

        return true;
    }

    /**
     * Sync
     * 
     * @param int $id
     * @param int $endDate
     * @param array $features
     */
    public function sync(int $id, int $endDate, array $features) {

        $subscription = call_user_func($this->subscriptionModelName . '::firstWhere', 'id', $id);

        if (!$subscription) {
            $subscription = call_user_func($this->subscriptionModelName . '::create', [
                'id' => $id,
                'end_date' => $endDate,
                'is_deleted' => 0
            ]);
        }

        $existingFeatures = $subscription->features->keyBy('name')->all();

        $values = [];
        $tableName = (new $this->featureModelName)->getTable();

        foreach($features as $feature) {
            if (empty($existingFeatures[$feature['name']]))
            {
                $values[] = [
                    'subscription_id' => $id,
                    'name' => $feature['name'],
                    'dtype' => $feature['dtype'],
                    'value' => $feature['value'],
                    'usage' => $feature['usage'] ?? ($feature['dtype'] == 'numeric' ? 0 : '')
                ];
            }
        }

        DB::table($tableName)->insert($values);
    }

    /**
     * Record usage
     * 
     * @param int $id
     * @param string $name
     * @param int $usage
     */
    public function recordUsage(int $id, string $name, int $usage)
    {
        $tableName = (new $this->featureModelName)->getTable();
        DB::table($tableName)
        ->where([
            'subscription_id' => $id,
            'name' => $name
        ])
        ->update(['usage' => $usage]);

        $this->repository->delete($id . '_' . $name);
    }
}
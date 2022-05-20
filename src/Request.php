<?php

namespace AgenterLab\FeatureChecker;

use AgenterLab\FeatureChecker\Exceptions\SubscriptionException;

class Request
{

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
    public function __construct(private string $key, private string $tokenName, private bool $restict)
    {

    }

    /**
     * Get repository 
     * 
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function validate()
    {
        $token = app('request')->headers->get($this->tokenName);
        if (empty($token)) {
            $token = app('request')->cookie($this->tokenName);
        }
        
        if (empty($token)) {
            return;
        }

        $parts = explode(':', $token);

        if (count($parts) != 2) {
            if (!$this->restict) {
                return;
            }
            throw new SubscriptionException("Subscription token invalid");
        }

        $signature = $this->signature($parts[0]);

        if ($signature != $parts[1]) {
            if (!$this->restict) {
                return;
            }
            throw new SubscriptionException('Subscription signature failed');
        }

        try {
            $this->subscription = app('saas')->newInstance($parts[0]);
        } catch (SubscriptionException $e) {
            if ($this->restict) {
                throw $e;
            }
        }
        
    }



    /**
     * Get subscription 
     * 
     * @return string
     */
    public function signature(int|string $id): string
    {
        return hash_hmac('sha256', $id, $this->key);
    }

    /**
     * Get subscription 
     * 
     * @return \AgenterLab\FeatureChecker\Subscription
     * @throws \AgenterLab\FeatureChecker\Exceptions\SubscriptionException
     */
    public function subscription(): Subscription
    {
        if (is_null($this->subscription)) {
            throw new SubscriptionException('Subscription empty');
        }

        return $this->subscription;
    }
}
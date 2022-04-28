<?php

namespace AgenterLab\FeatureChecker;

// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Config;
// use AgenterLab\FeatureChecker\Exceptions\FeatureException;
// use AgenterLab\FeatureChecker\Exceptions\SubscriptionException;

interface SubscriptionContract
{

    /**
     * Get end date
     */
    public function getEndDate(): int;
}

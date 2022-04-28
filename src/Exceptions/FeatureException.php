<?php

namespace AgenterLab\FeatureChecker\Exceptions;

class FeatureException extends SubscriptionException
{
    private $feature;

    // Redefine the exception so message isn't optional
    public function __construct(
        string $feature,
        $value = ''
    ) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct('You are not allowed this action', 403);

        $this->feature = $feature;
    }
}
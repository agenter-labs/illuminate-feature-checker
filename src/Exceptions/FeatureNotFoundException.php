<?php

namespace AgenterLab\FeatureChecker\Exceptions;

class FeatureNotFoundException extends SubscriptionException
{
    private $feature;

    // Redefine the exception so message isn't optional
    public function __construct(
        string $feature,
        $value = ''
    ) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct('Requested features does not exists', 403);

        $this->feature = $feature;
    }
}
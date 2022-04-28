Install
---
```
composer require agenter-labs/illuminate-feature-checker
```

Enviornment
----
```
SAAS_STORAGE_CACHE=redis,file
SAAS_MODEL_SUBSCRIPTION=Subscription model class name
SAAS_MODEL_FEATURE=Subscription feature model class name
SAAS_KEY=Encryption key
SAAS_TOKEN_NAME= Header or Cookie name
```

Setup
----
Register service provider
```
$app->register(AgenterLab\FeatureChecker\FeatureCheckerServiceProvider::class);
```

Register route middleware
```
$app->routeMiddleware([
    'subscription' => \AgenterLab\FeatureChecker\SubscriptionMiddleware::class
]);
```

Generate signature 
```
app('saas.request')->signature();
```

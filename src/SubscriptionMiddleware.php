<?php

namespace AgenterLab\FeatureChecker;

use Closure;

class SubscriptionMiddleware
{
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        app('saas.request')->validate();

        return $next($request);
    }
}

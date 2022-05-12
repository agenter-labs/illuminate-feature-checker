<?php

namespace Tests\Unit;

use Tests\TestCase;
use AgenterLab\FeatureChecker\Exceptions\FeatureException;

class RouteTest extends TestCase
{

    public function testMiddleware()
    {
        app('saas')->getRepository()->clear();
        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);

        $key = '1:' . app('saas.request')->signature(1);
        
        $this->get('feature', ['sub-tkn' => $key])->seeStatusCode(200);
    }

    public function testMiddlewareAllow()
    {
        app('saas')->getRepository()->clear();
        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature2',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);

        $key = '1:' . app('saas.request')->signature(1);
        
        $this->get('feature', ['sub-tkn' => $key])->seeStatusCode(500);
    }

    public function testMiddlewareRestrictConfig()
    {
        config([
            'saas.request_restrict' => true
        ]);

        app('saas')->getRepository()->clear();
        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);

        $key = '2:' . app('saas.request')->signature(1);
        
        $this->get('feature', ['sub-tkn' => $key])->seeStatusCode(500);
    }
}
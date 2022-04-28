<?php

namespace Tests\Unit;

use Tests\TestCase;


class RouteTest extends TestCase
{

    public function testMiddleware()
    {
        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);

        $key = '1:' . hash_hmac('sha256', 1, config('saas.key'));
        
        $this->get('feature', ['sub-tkn' => $key])->seeStatusCode(200);
    }
}
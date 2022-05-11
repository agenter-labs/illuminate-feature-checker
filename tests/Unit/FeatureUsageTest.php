<?php

namespace Tests\Unit;

use Tests\TestCase;

use AgenterLab\FeatureChecker\Exceptions\FeatureException;
use AgenterLab\FeatureChecker\Exceptions\SubscriptionExpiredException;

class FeatureUsageTest extends TestCase
{

    public function testSync()
    {
        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);
        $this->assertTrue(app('saas')->subscription(1)->can('feature1', true));

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature2',
                'dtype' => 'numeric',
                'value' => '50',
                'usage' => '50'
            ]
        ]);
        $this->assertFalse(app('saas')->subscription(1)->can('feature2', true));
    }

    public function testSyncExpired()
    {
        $this->expectException(SubscriptionExpiredException::class);

        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() - 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '50'
            ]
        ]);
        $this->assertTrue(app('saas')->subscription(1)->can('feature1', true));
    }

    public function testDefaultCan()
    {
        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() + 35000, []);

        $this->assertTrue(app('saas')->subscription(1)->can('feature-one', true));
        $this->assertFalse(app('saas')->subscription(1)->can('feature-one', false));
    }


    public function testDefaultAllowException()
    {
        app('saas')->getRepository()->clear();
        $this->expectException(FeatureException::class);

        app('saas')->sync(1, time() + 35000, []);

        app('saas')->subscription(1)->allow('feature-one', false);
    }

    public function testCan()
    {
        app('saas')->getRepository()->clear();
        app('saas')->sync(1, time() + 35000, []);


        $this->assertTrue(app('saas')->subscription(1)->can('invoice', true));
    }

    public function testFullyUsedCan()
    {
        app('saas')->getRepository()->clear();
        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'invoice',
                'dtype' => 'numeric',
                'value' => '50',
                'usage' => '50'
            ]
        ]);

        $this->assertFalse(app('saas')->subscription(1)->can('invoice', true));
    }

    public function testRecordUsage()
    {
        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '2'
            ]
        ]);

        $this->assertTrue(app('saas')->subscription(1)->can('feature1', true));
        app('saas')->subscription(1)->recordUsage('feature1');
        $this->assertTrue(app('saas')->subscription(1)->can('feature1', true));
        app('saas')->subscription(1)->recordUsage('feature1');
        $this->assertFalse(app('saas')->subscription(1)->can('feature1', true));

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature2',
                'dtype' => 'numeric',
                'value' => '10'
            ]
        ]);
        app('saas')->subscription(1)->recordUsage('feature2', 10);
        $this->assertFalse(app('saas')->subscription(1)->can('feature2', true));

    }

    public function testDelete()
    {
        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '2'
            ]
        ]);

        $this->assertTrue(app('saas')->subscription(1)->can('feature1', false));
        app('saas')->subscription(1)->recordUsage('feature1');

        $this->assertTrue(app('saas')->delete(1, ['feature1']));
        
        $this->assertFalse(app('saas')->subscription(1)->can('feature1', false));


        // Restore

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'numeric',
                'value' => '2'
            ]
        ]);

        $this->assertTrue(app('saas')->subscription(1)->can('feature1', false));
        app('saas')->subscription(1)->recordUsage('feature1');
    }

    public function testAliases()
    {
        app('saas')->getRepository()->clear();

        app('saas')->sync(1, time() + 35000, [
            [
                'name' => 'feature1',
                'dtype' => 'string',
                'value' => 'Y'
            ]
        ]);
        app('saas')->subscription(1)->setAliases(['alias1' => 'feature1']);
        $this->assertTrue(app('saas')->subscription(1)->can('alias1', 'Y'));
    }
}
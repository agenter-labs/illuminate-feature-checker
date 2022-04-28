<?php

namespace Tests\Unit;

use Tests\TestCase;

use Database\Factories\SubscriptionFactory;
use Database\Factories\FeatureFactory;
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
        $subscription = SubscriptionFactory::new()->create();

        $this->assertTrue(app('saas')->subscription($subscription['id'])->can('feature-one', true));
        $this->assertFalse(app('saas')->subscription($subscription['id'])->can('feature-one', false));
    }


    public function testDefaultAllowException()
    {
        app('saas')->getRepository()->clear();
        $this->expectException(FeatureException::class);

        $subscription = SubscriptionFactory::new()->create();

        app('saas')->subscription($subscription['id'])->allow('feature-one', false);
    }

    public function testCan()
    {
        app('saas')->getRepository()->clear();
        $subscription = SubscriptionFactory::new()
        ->has(FeatureFactory::new()->count(1))
        ->create();


        $this->assertTrue(app('saas')->subscription($subscription['id'])->can('invoice', true));
    }

    public function testFullyUsedCan()
    {
        app('saas')->getRepository()->clear();
        $subscription = SubscriptionFactory::new()
        ->has(FeatureFactory::new()->fullyUsed()->count(1))
        ->create();

        $this->assertFalse(app('saas')->subscription($subscription['id'])->can('invoice', true));
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
}
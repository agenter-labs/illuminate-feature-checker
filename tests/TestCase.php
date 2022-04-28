<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Http\Request as LumenRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    public function runDatabaseMigrations()
    {
        $path = 'tests/database/migrations';

        $this->artisan('migrate:fresh', ['--path' => $path]);

        $this->beforeApplicationDestroyed(function () use ($path) {
            $this->artisan('migrate:rollback', ['--path' => $path]);
        });
    }

    protected function setUp(): void
    {    
        parent::setUp();
        config([
            'saas.storage.subscription' => Models\Subscription::class,
            'saas.storage.feature' => Models\Feature::class,
            'saas.key' => 'a48cf9fc4972f4939356676c5f032301',
            'auth.guards.api.driver' => 'token'
        ]);
    }
    
    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string  $content
     * @return \Illuminate\Http\Response
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->currentUri = $this->prepareUrlForRequest($uri);

        $symfonyRequest = SymfonyRequest::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );

        $this->app['request'] = LumenRequest::createFromBase($symfonyRequest);

        // $this->app['auth']->setRequest($this->app['request']);

        return $this->response = TestResponse::fromBaseResponse(
            $this->app->prepareResponse($this->app->handle($this->app['request']))
        );
    }
}

<?php

$router->group(['middleware' => ['subscription']], function () use ($router) {

    $router->get('feature', function() {

        app('saas.request')->subscription()->allow('feature1', false);

        return "Feature";
    });
});


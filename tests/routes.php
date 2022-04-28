<?php

$router->group(['middleware' => ['subscription']], function () use ($router) {

    $router->get('feature', function() {
        return "Feature";
    });
});


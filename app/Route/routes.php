<?php

/** @var Router $router */
use Minute\Model\Permission;
use Minute\Routing\Router;

$router->get('/uploader/api-key/{site_name}', 'Uploader/ApiKey', true, 'm_api_keys[site_name][1] as api_keys')
       ->setDefault('_noView', true);
$router->post('/uploader/api-key/{site_name}', null, true, 'm_api_keys as api_keys');

$router->get('/uploader/authorize/{service}', 'Uploader/Authorize', true)->setDefault('_noView', true);
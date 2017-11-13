<?php
/*
	Routes
	controller needs to be registered in dependency.php
*/

$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders));


$app->get('/', 'App\Controllers\HomeController:dispatch')->setName('homepage');

$app->get('/users', 'App\Controllers\UserController:dispatch')->setName('userpage');

$app->get('/database', 'App\Controllers\DatabaseController:dispatch')->setName('database');

$app->post('/validate', 'App\Controllers\BankController:dispatch')->setName('validate');
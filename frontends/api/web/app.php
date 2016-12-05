<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

// Environment variables change.
// To reproduce specific debug error reporting you need the computed value
// Ex: E_ALL & ~E_NOTICE & ~E_STRICT = 37011
$debug = getenv('SYMFONY_DEBUG') ?: 0;
$env = getenv('SYMFONY_ENV') ?: 'prod';

if (0 !== $debug) {
    Debug::enable(E_ALL);
}
$kernel = new AppKernel($env, (bool) $debug);
$kernel->loadClassCache();

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

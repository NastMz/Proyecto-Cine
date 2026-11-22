<?php

declare(strict_types=1);

use Cine\App;
use Cine\Config;
use Cine\Http\Request;
use Cine\Http\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$config = Config::load($root);
date_default_timezone_set($config->timezone);
ini_set('display_errors', $config->debug ? '1' : '0');

$request = Request::fromGlobals();
$app = new App($config, Session::native($request->isSecure()), $root);
$app->handle($request)->send();

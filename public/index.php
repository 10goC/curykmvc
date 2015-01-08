<?php
define('LIB_PATH', __DIR__ . '/../lib');
define('APPLICATION_PATH', __DIR__ . '/../application');
define('PUBLIC_PATH', __DIR__);

require_once LIB_PATH . '/Mvc/Application.php';

$application = new Mvc\Application();

$application->run();
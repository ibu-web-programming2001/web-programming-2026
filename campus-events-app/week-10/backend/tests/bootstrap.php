<?php
/*
 * Runs once before the tests.
 */
require __DIR__ . '/../vendor/autoload.php';

// Services and DAOs need a Config class. On a fresh clone (or in CI) there is no config.php yet,
// so we create it from the template – the unit tests never connect to the database anyway.
$config = __DIR__ . '/../config/config.php';
if (!file_exists($config)) {
    copy(__DIR__ . '/../config/config.example.php', $config);
}

require_once __DIR__ . '/api/ApiTestCase.php';

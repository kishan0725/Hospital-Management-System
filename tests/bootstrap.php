<?php
// this file gets oaded by phpunit before any test runs.

require __DIR__ . '/../vendor/autoload.php';

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

fwrite(STDERR, "[bootstrap] loaded. PROJECT_ROOT=" . PROJECT_ROOT . "\n");
fwrite(STDERR, "[bootstrap] defined check: " . (defined('PROJECT_ROOT') ? 'YES' : 'NO') . "\n");

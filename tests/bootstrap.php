<?php
// this file gets loaded by phpunit before any test runs.

echo "[bootstrap] STARTING\n";

require __DIR__ . '/../vendor/autoload.php';

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

echo "[bootstrap] PROJECT_ROOT=" . PROJECT_ROOT . "\n";
echo "[bootstrap] defined: " . (defined('PROJECT_ROOT') ? 'YES' : 'NO') . "\n";

<?php
//  shared base class.

namespace Hospital\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Includes a project PHP file with the supplied superglobals and
     * returns whatever it printed.
     */
    protected function captureScript(
        string $relativePath,
        array $post = [],
        array $get = [],
        array $session = []
    ): string {
        $_POST    = $post;
        $_GET     = $get;
        $_SESSION = $session;

        ob_start();
        try {
           require \PROJECT_ROOT . '/' . ltrim($relativePath, '/');
        } finally {
            $output = ob_get_clean();
        }
        return $output ?: '';
    }
}

<?php
// shared base class.

namespace Hospital\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Includes a project PHP file with the supplied superglobals and
     * returns whatever it printed.
     *
     * Sessions are tricky in CLI: the source files all call session_start()
     * at the top, which would reset $_SESSION to [] if no session is active.
     * We sidestep that by starting a session ourselves first — session_start()
     * is a no-op when a session is already active, so our injected $_SESSION
     * survives.
     */
    protected function captureScript(
        string $relativePath,
        array $post = [],
        array $get = [],
        array $session = []
    ): string {
        $_POST = $post;
        $_GET  = $get;

        // Start a session if one isn't already active. Suppress warnings
        // because CLI doesn't have real cookies/headers — we only care
        // about $_SESSION being preserved across the require.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = $session;

        $projectRoot = dirname(__DIR__);

        ob_start();
        try {
            require $projectRoot . '/' . ltrim($relativePath, '/');
        } finally {
            $output = ob_get_clean();
        }

        return $output ?: '';
    }
}

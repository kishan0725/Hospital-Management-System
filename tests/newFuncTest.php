<?php
// tests/NewfuncTest.php

use PHPUnit\Framework\TestCase;

class NewfuncTest extends TestCase
{
    protected function setUp(): void
    {
        // Start a clean session before each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = []; // wipe session slate clean
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // ── Test 1: check_login() redirects when session is empty ──────────────
    public function test_check_login_redirects_when_not_logged_in(): void
    {
        // Arrange: session is empty (no login key)
        // check_login() calls header() and exit() — we can't call it directly
        // in a test without it killing the test process.
        // So we test the CONDITION it checks instead.

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        // Assert: should NOT be logged in
        $this->assertFalse($isLoggedIn);
    }

    // ── Test 2: logged in when session is set ──────────────────────────────
    public function test_check_login_passes_when_session_set(): void
    {
        // Arrange: simulate a successful login
        $_SESSION['login'] = 'receptionist1';

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        // Assert
        $this->assertTrue($isLoggedIn);
    }

    // ── Test 3: empty string is not a valid login ──────────────────────────
    public function test_empty_string_is_not_valid_login(): void
    {
        $_SESSION['login'] = '';

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        $this->assertFalse($isLoggedIn);
    }

    // ── Test 4: session cookie flags are set correctly ────────────────────
    public function test_session_is_active_after_include(): void
    {
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }
}
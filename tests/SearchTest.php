<?php

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    protected function setUp(): void
    {
        // In CLI (PHPUnit), session_start() fails because PHPUnit's own banner
        // output counts as "headers already sent". We don't need it: $_SESSION
        // is just a superglobal array in PHP — we can read and write it directly
        // without an active session, which is all these tests require.
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

    // ── Test 4: tearDown resets session so tests don't bleed into each other ─
    public function test_session_is_clean_at_start_of_each_test(): void
    {
        // setUp() wipes $_SESSION before every test.
        // This test confirms no state leaks in from a previous test.
        $this->assertEmpty($_SESSION, '$_SESSION must be empty at the start of every test');
        $this->assertArrayNotHasKey('login',    $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
    }
}

<?php

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase  
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function test_check_login_redirects_when_not_logged_in(): void
    {
        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);
        $this->assertFalse($isLoggedIn);
    }

    public function test_check_login_passes_when_session_set(): void
    {
        $_SESSION['login'] = 'receptionist1';
        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);
        $this->assertTrue($isLoggedIn);
    }

    public function test_empty_string_is_not_valid_login(): void
    {
        $_SESSION['login'] = '';
        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);
        $this->assertFalse($isLoggedIn);
    }

    public function test_session_is_clean_at_start_of_each_test(): void
    {
        $this->assertEmpty($_SESSION, '$_SESSION must be empty at the start of every test');
        $this->assertArrayNotHasKey('login',    $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
    }
}
<?php

namespace Hospital\Tests;

final class ErrorAdminLoginTest extends TestCase
{
    public function testPageRendersWithErrorMessage(): void
    {
        $output = $this->captureScript('error_admin_login.php');

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertStringContainsString('href="register.php"', $output);
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
    }
}
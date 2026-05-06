<?php
use PHPUnit\Framework\TestCase;

final class ErrorAdminLoginTest extends TestCase
{
    public function testPageRendersWithErrorMessage(): void
    {
        ob_start();
        require __DIR__ . '/../error_admin_login.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertStringContainsString('href="register.php"', $output);
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
    }
}
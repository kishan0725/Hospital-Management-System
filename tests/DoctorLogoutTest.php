<?php
// verifies session is cleared and exit page renders.

namespace Hospital\Tests;

final class DoctorLogoutTest extends TestCase
{
    public function testRendersLogoutPage(): void
    {
        $output = $this->captureScript('doctor_logout.php', [], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        $this->assertStringContainsString('You have logged out', $output);
        $this->assertStringContainsString('Back to Home Page', $output);
    }

    public function testClearsSessionData(): void
    {
        $this->captureScript('doctor_logout.php', [], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);
        $this->assertSame([], $_SESSION);
    }

    public function testLinkPointsToRegisterPage(): void
    {
        $output = $this->captureScript('doctor_logout.php');
        $this->assertMatchesRegularExpression('/href=["\']register\.php["\']/', $output);
    }
}

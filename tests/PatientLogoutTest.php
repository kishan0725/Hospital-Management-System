<?php
// verifies session is cleared and exit page renders for patient logout.

namespace Hospital\Tests;

final class PatientLogoutTest extends TestCase
{
    public function testRendersLogoutPage(): void
    {
        $output = $this->captureScript('patient_logout.php', [], [], [
            'role' => 'patient',
            'pid'  => 42,
        ]);

        $this->assertStringContainsString('You have logged out', $output);
        $this->assertStringContainsString('Back to Login Page', $output);
    }

    public function testClearsSessionData(): void
    {
        $this->captureScript('patient_logout.php', [], [], [
            'role' => 'patient',
            'pid'  => 42,
            'fname' => 'Jane',
            'lname' => 'Doe',
        ]);
        $this->assertSame([], $_SESSION);
    }

    public function testLinkPointsToPatientLogin(): void
    {
        $output = $this->captureScript('patient_logout.php');
        $this->assertMatchesRegularExpression('/href=["\']patient_login\.php["\']/', $output);
    }
}

<?php
namespace Hospital\Tests;

final class PatientLoginTest extends TestCase
{
    public function testPageRenders(): void
    {
        $output = $this->captureScript('patient_login.php');

        $this->assertNotEmpty(trim($output));
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
    }

    public function testHasPatientLoginHeading(): void
    {
        $output = $this->captureScript('patient_login.php');
        $this->assertStringContainsString('Patient Login', $output);
    }

    public function testFormPostsToPatientAuth(): void
    {
        $output = $this->captureScript('patient_login.php');

        // Form must POST to patient_auth.php — otherwise login is broken.
        $this->assertMatchesRegularExpression(
            '/<form[^>]*method=["\']POST["\'][^>]*action=["\']patient_auth\.php["\']/i',
            $output
        );
    }

    public function testHasEmailAndPasswordFields(): void
    {
        $output = $this->captureScript('patient_login.php');

        $this->assertMatchesRegularExpression(
            '/<input[^>]*type=["\']email["\'][^>]*name=["\']email["\']/i',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/<input[^>]*name=["\']password2["\'][^>]*type=["\']password["\']|<input[^>]*type=["\']password["\'][^>]*name=["\']password2["\']/i',
            $output
        );
    }

    public function testSubmitButtonNameMatchesAuthHandler(): void
    {
        $output = $this->captureScript('patient_login.php');

        // patient_auth.php checks isset($_POST['patsub']). If ths name
        // changes the auth flow silently breaks.
        $this->assertMatchesRegularExpression(
            '/<input[^>]*type=["\']submit["\'][^>]*name=["\']patsub["\']/i',
            $output
        );
    }

    public function testNavLinksToRegisterAndContact(): void
    {
        $output = $this->captureScript('patient_login.php');

        $this->assertMatchesRegularExpression('/href=["\']register\.php["\']/', $output);
        $this->assertMatchesRegularExpression('/href=["\']contact\.html["\']/', $output);
    }
}

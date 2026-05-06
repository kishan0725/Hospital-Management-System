<?php


namespace Hospital\Tests;

final class RegisterTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        // Render once and reuse — the page does no work between requests.
        $this->output = $this->captureScript('register.php');
    }

    public function testPageRenders(): void
    {
        $this->assertNotEmpty(trim($this->output));
        $this->assertStringContainsString('GLOBAL HOSPITALS', $this->output);
    }

    public function testHasAllThreeTabs(): void
    {
        $this->assertStringContainsString('Patient',      $this->output);
        $this->assertStringContainsString('Doctor',       $this->output);
        $this->assertStringContainsString('Receptionist', $this->output);
    }

    // --- Patient registration form -----------------------------------------

    public function testPatientFormPostsToRegisterHandler(): void
    {
        $this->assertMatchesRegularExpression(
            '/<form[^>]*method=["\']post["\'][^>]*action=["\']patient_register_handler\.php["\']/i',
            $this->output
        );
    }

    public function testPatientFormHasRequiredFields(): void
    {
        // First name, last name, email, password, confirm password, contact, gender.
        $this->assertMatchesRegularExpression('/name=["\']fname["\']/',     $this->output);
        $this->assertMatchesRegularExpression('/name=["\']lname["\']/',     $this->output);
        $this->assertMatchesRegularExpression('/name=["\']email["\']/',     $this->output);
        $this->assertMatchesRegularExpression('/name=["\']password["\']/',  $this->output);
        $this->assertMatchesRegularExpression('/name=["\']cpassword["\']/', $this->output);
        $this->assertMatchesRegularExpression('/name=["\']contact["\']/',   $this->output);
        $this->assertMatchesRegularExpression('/name=["\']gender["\']/',    $this->output);
    }

    public function testPatientSubmitButtonName(): void
    {
        // patient_register_handler.php checks isset($_POST['patsub1']).
        $this->assertMatchesRegularExpression('/name=["\']patsub1["\']/', $this->output);
    }

    // --- Doctor login form -------------------------------------------------

    public function testDoctorFormPostsToDoctorAuth(): void
    {
        $this->assertMatchesRegularExpression(
            '/<form[^>]*action=["\']doctor_auth\.php["\']/i',
            $this->output
        );
    }

    public function testDoctorSubmitButtonName(): void
    {
        // doctor_auth.php checks isset($_POST['docsub1']).
        $this->assertMatchesRegularExpression('/name=["\']docsub1["\']/', $this->output);
    }

    public function testDoctorFormHasUsernameAndPassword(): void
    {
        $this->assertMatchesRegularExpression('/name=["\']username3["\']/', $this->output);
        $this->assertMatchesRegularExpression('/name=["\']password3["\']/', $this->output);
    }

    // --- Admin login form --------------------------------------------------

    public function testAdminFormPostsToAdminAuth(): void
    {
        $this->assertMatchesRegularExpression(
            '/<form[^>]*action=["\']admin_auth\.php["\']/i',
            $this->output
        );
    }

    public function testAdminSubmitButtonName(): void
    {
        // admin_auth.php checks isset($_POST['adsub']).
        $this->assertMatchesRegularExpression('/name=["\']adsub["\']/', $this->output);
    }

    public function testAdminFormHasUsernameAndPassword(): void
    {
        $this->assertMatchesRegularExpression('/name=["\']username1["\']/', $this->output);
        $this->assertMatchesRegularExpression('/name=["\']password2["\']/', $this->output);
    }
}

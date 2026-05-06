<?php
// tests/PatientSearchTest.php — covers admin-only patient lookup by contact.

namespace Hospital\Tests;

use mysqli;

final class PatientSearchTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM patreg");

        // Seed one known patient.
        $hash = password_hash('whatever', PASSWORD_DEFAULT);
        $stmt = $this->con->prepare(
            "INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword)
             VALUES ('Jane', 'Doe', 'Female', 'jane@example.com', '5551234567', ?, ?)"
        );
        $stmt->bind_param('ss', $hash, $hash);
        $stmt->execute();
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM patreg");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    public function testNoPostRendersEmptyShell(): void
    {
        $output = $this->captureScript('patient_search.php', [], [], [
            'role' => 'admin',
        ]);

        // No search submitted yet — should render the page shell but no
        // patient data tables.
        $this->assertStringNotContainsString('Jane', $output);
        $this->assertStringNotContainsString('jane@example.com', $output);
    }

    public function testAdminFindsExistingPatient(): void
    {
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Jane', $output);
        $this->assertStringContainsString('Doe', $output);
        $this->assertStringContainsString('jane@example.com', $output);
        $this->assertStringContainsString('5551234567', $output);
    }

    public function testAdminSearchForUnknownContactShowsAlert(): void
    {
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => '0000000000',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testNonAdminRejected(): void
    {
        // Patient role tries to access — should be redirected away
        // and produce no patient data in the response body.
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => '5551234567',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
        $this->assertStringNotContainsString('jane@example.com', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        // No session at all — should also be denied.
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => '5551234567',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
        $this->assertStringNotContainsString('jane@example.com', $output);
    }

    public function testPasswordNeverAppearsInOutput(): void
    {
        // Defense-in-depth: even a successful admin search must not leak
        // the password hash. The query intentionally doesn't SELECT it.
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('$2y$', $output, 'bcrypt prefix must not leak');
        $this->assertStringNotContainsString('password', strtolower($output), 'no "password" label/value should appear');
    }

    public function testSqlInjectionAttemptYieldsNoResults(): void
    {
        // Prepared statement should treat this as a literal contact.
        $output = $this->captureScript('patient_search.php', [
            'patient_search_submit' => '1',
            'patient_contact'       => "' OR '1'='1",
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('Jane', $output);
    }
}

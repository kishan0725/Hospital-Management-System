<?php
// tests/DoctorSearchTest.php — covers admin-only doctor lookup by email.

namespace Hospital\Tests;

use mysqli;

final class DoctorSearchTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM doctb");

        // Seed one known doctor.
        $hash = password_hash('whatever', PASSWORD_DEFAULT);
        $stmt = $this->con->prepare(
            "INSERT INTO doctb (username, password, email, spec, docFees)
             VALUES ('drhouse', ?, 'house@example.com', 'Diagnostics', 200)"
        );
        $stmt->bind_param('s', $hash);
        $stmt->execute();
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM doctb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    public function testNoPostRendersEmptyShell(): void
    {
        $output = $this->captureScript('doctor_search.php', [], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('drhouse',  $output);
        $this->assertStringNotContainsString('house@example.com', $output);
    }

    public function testAdminFindsExistingDoctor(): void
    {
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => 'house@example.com',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('drhouse', $output);
        $this->assertStringContainsString('house@example.com', $output);
        $this->assertStringContainsString('200', $output);
    }

    public function testAdminSearchForUnknownEmailShowsAlert(): void
    {
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => 'nobody@nowhere.test',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('drhouse', $output);
    }

    public function testNonAdminRejected(): void
    {
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => 'house@example.com',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringNotContainsString('drhouse', $output);
        $this->assertStringNotContainsString('house@example.com', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => 'house@example.com',
        ]);

        $this->assertStringNotContainsString('drhouse', $output);
        $this->assertStringNotContainsString('house@example.com', $output);
    }

    public function testPasswordHashNeverAppearsInOutput(): void
    {
        // Source explicitly skips the password column in its SELECT.
        // This locks that promise in.
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => 'house@example.com',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('$2y$', $output, 'bcrypt prefix must not leak');
    }

    public function testSqlInjectionAttemptYieldsNoResults(): void
    {
        $output = $this->captureScript('doctor_search.php', [
            'doctor_search_submit' => '1',
            'doctor_contact'       => "' OR '1'='1",
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('drhouse', $output);
    }
}

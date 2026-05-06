<?php
// tests/AppointmentSearchTest.php — covers admin-only appointment lookup.
// Locks the (userStatus, doctorStatus) → display mapping in particular,
// since those mappings are easy to get backwards in a refactor.

namespace Hospital\Tests;

use mysqli;

final class AppointmentSearchTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM appointmenttb");
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    /**
     * Helper to seed one appointment with the given user/doctor status.
     */
    private function seedAppointment(string $contact, int $userStatus, int $doctorStatus): void
    {
        $stmt = $this->con->prepare(
            "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, docFees,
                                        appdate, apptime, userStatus, doctorStatus, payment)
             VALUES (1, 'Jane', 'Doe', 'Female', 'jane@example.com', ?, 'drhouse', 200,
                     '2026-01-01', '10:00', ?, ?, 'pending')"
        );
        $stmt->bind_param('sii', $contact, $userStatus, $doctorStatus);
        $stmt->execute();
        $stmt->close();
    }

    public function testNoPostRendersEmptyShell(): void
    {
        $this->seedAppointment('5551234567', 1, 1);
        $output = $this->captureScript('appointment_search.php', [], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testAdminFindsExistingAppointment(): void
    {
        $this->seedAppointment('5551234567', 1, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Jane', $output);
        $this->assertStringContainsString('Doe', $output);
        $this->assertStringContainsString('5551234567', $output);
        $this->assertStringContainsString('drhouse', $output);
        $this->assertStringContainsString('2026-01-01', $output);
    }

    public function testAdminSearchForUnknownContactShowsAlert(): void
    {
        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '0000000000',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
    }

    public function testNonAdminRejected(): void
    {
        $this->seedAppointment('5551234567', 1, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        $this->seedAppointment('5551234567', 1, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    // --- Status mapping locks ---------------------------------------------

    public function testActiveAppointmentRendersAsActive(): void
    {
        $this->seedAppointment('5551234567', 1, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Active', $output);
        $this->assertStringNotContainsString('Cancelled', $output);
    }

    public function testUserCancelledRendersAsCancelledByYou(): void
    {
        $this->seedAppointment('5551234567', 0, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Cancelled by You', $output);
    }

    public function testDoctorCancelledRendersAsCancelledByDoctor(): void
    {
        $this->seedAppointment('5551234567', 1, 0);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Cancelled by Doctor', $output);
    }

    public function testSqlInjectionAttemptYieldsNoResults(): void
    {
        $this->seedAppointment('5551234567', 1, 1);

        $output = $this->captureScript('appointment_search.php', [
            'app_search_submit' => '1',
            'app_contact'       => "' OR '1'='1",
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('Jane', $output);
    }
}

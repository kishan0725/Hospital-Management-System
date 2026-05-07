<?php
// tests/SearchTest.php — covers doctor-only appointment search.
// Critical security property: results MUST be scoped to the logged-in
// doctor's own appointments, not other doctors' patients.

namespace Hospital\Tests;

use mysqli;

final class SearchTest extends TestCase
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

    private function seedAppointment(
        string $doctor,
        string $contact,
        string $fname,
        string $lname,
        string $email = 'patient@example.com'
    ): void {
        $stmt = $this->con->prepare(
            "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, docFees,
                                        appdate, apptime, userStatus, doctorStatus, payment)
             VALUES (1, ?, ?, 'Female', ?, ?, ?, 200,
                     '2026-01-01', '10:00', 1, 1, 'pending')"
        );
        $stmt->bind_param('sssss', $fname, $lname, $email, $contact, $doctor);
        $stmt->execute();
        $stmt->close();
    }

    public function testNoPostRendersNothing(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        // Without a search submission, the page should produce no results table.
        $this->assertSame('', trim($output));
    }

    public function testDoctorFindsOwnAppointment(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        $this->assertStringContainsString('Jane', $output);
        $this->assertStringContainsString('Doe', $output);
        $this->assertStringContainsString('5551234567', $output);
        $this->assertStringContainsString('2026-01-01', $output);
    }

    public function testDoctorCannotSeeOtherDoctorsPatients(): void
    {
        // Two appointments, same patient contact, different doctors.
        $this->seedAppointment('drhouse', '5551234567', 'Jane',  'Doe',  'jane@example.com');
        $this->seedAppointment('drwho',   '5551234567', 'Steve', 'Roe',  'steve@example.com');

        // drhouse searches — should see Jane (theirs) but NOT Steve (drwho's).
        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        $this->assertStringContainsString('Jane',  $output, 'drhouse should see their own patient');
        $this->assertStringNotContainsString('Steve', $output, 'drhouse must not see drwho\'s patient');
        $this->assertStringNotContainsString('steve@example.com', $output);
    }

    public function testSearchForUnknownContactRendersEmptyTable(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '0000000000',
        ], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        // Empty result set — table renders but no patient rows.
        $this->assertStringNotContainsString('Jane', $output);
        $this->assertStringContainsString('Search Results', $output);
    }

    public function testNonDoctorRejected(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testDoctorWithEmptyDnameRejected(): void
    {
        // Defense in depth: even if 'role' is set to 'doctor', a missing
        // 'dname' must not let the user enumerate everyone's appointments.
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ], [], [
            'role'  => 'doctor',
            'dname' => '',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testSqlInjectionAttemptYieldsNoResults(): void
    {
        $this->seedAppointment('drhouse', '5551234567', 'Jane', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => "' OR '1'='1",
        ], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        $this->assertStringNotContainsString('Jane', $output);
    }

    public function testHtmlEscapedInOutput(): void
    {
        // Stored XSS guard: a patient name containing HTML must be escaped.
        $this->seedAppointment('drhouse', '5551234567', '<b>x</b>', 'Doe');

        $output = $this->captureScript('search.php', [
            'search_submit' => '1',
            'contact'       => '5551234567',
        ], [], [
            'role'  => 'doctor',
            'dname' => 'drhouse',
        ]);

        $this->assertStringNotContainsString('<b>x</b>', $output);
        $this->assertStringContainsString('&lt;b&gt;', $output);
    }
}

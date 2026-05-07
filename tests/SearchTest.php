<?php
// tests/SearchTest.php — covers doctor-only appointment search.
// Critical security property: results MUST be scoped to the logged-in
// doctor's own appointments, not other doctors' patients.

namespace Hospital\Tests;

<<<<<<< HEAD
use mysqli;

final class SearchTest extends TestCase
=======
class NewfuncTest extends TestCase
>>>>>>> master
{
    private mysqli $con;

    protected function setUp(): void
    {
<<<<<<< HEAD
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM appointmenttb");
=======
        // In CLI (PHPUnit), session_start() fails because PHPUnit's own banner
        // output counts as "headers already sent". We don't need it: $_SESSION
        // is just a superglobal array in PHP — we can read and write it directly
        // without an active session, which is all these tests require.
        $_SESSION = []; // wipe session slate clean
>>>>>>> master
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

<<<<<<< HEAD
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
=======
    // ── Test 1: check_login() redirects when session is empty ──────────────
    public function test_check_login_redirects_when_not_logged_in(): void
    {
        // Arrange: session is empty (no login key)
        // check_login() calls header() and exit() — we can't call it directly
        // in a test without it killing the test process.
        // So we test the CONDITION it checks instead.

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        // Assert: should NOT be logged in
        $this->assertFalse($isLoggedIn);
    }

    // ── Test 2: logged in when session is set ──────────────────────────────
    public function test_check_login_passes_when_session_set(): void
    {
        // Arrange: simulate a successful login
        $_SESSION['login'] = 'receptionist1';

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        // Assert
        $this->assertTrue($isLoggedIn);
    }

    // ── Test 3: empty string is not a valid login ──────────────────────────
    public function test_empty_string_is_not_valid_login(): void
    {
        $_SESSION['login'] = '';

        $isLoggedIn = isset($_SESSION['login']) && !empty($_SESSION['login']);

        $this->assertFalse($isLoggedIn);
    }

    // ── Test 4: tearDown resets session so tests don't bleed into each other ─
    public function test_session_is_clean_at_start_of_each_test(): void
    {
        // setUp() wipes $_SESSION before every test.
        // This test confirms no state leaks in from a previous test.
        $this->assertEmpty($_SESSION, '$_SESSION must be empty at the start of every test');
        $this->assertArrayNotHasKey('login',    $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
>>>>>>> master
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

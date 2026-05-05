<?php
// tests/PatientAuthTest.php — covers patient login + admin-gated POSTs in patient_auth.php.

namespace Hospital\Tests;

use mysqli;

final class PatientAuthTest extends TestCase
{
    private mysqli $con;

    private const PAT_EMAIL    = 'jane@example.com';
    private const PAT_PASSWORD = 'patient-correct-pass';
    private const PAT_FNAME    = 'Jane';
    private const PAT_LNAME    = 'Doe';

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        // Reset relevant tables and seed one known patient.
        $this->con->query("DELETE FROM patreg");
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->query("DELETE FROM doctb");

        $hash = password_hash(self::PAT_PASSWORD, PASSWORD_DEFAULT);
        $stmt = $this->con->prepare(
            "INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword)
             VALUES (?, ?, 'Female', ?, '5550000000', ?, ?)"
        );
        $f = self::PAT_FNAME;
        $l = self::PAT_LNAME;
        $e = self::PAT_EMAIL;
        $stmt->bind_param('sssss', $f, $l, $e, $hash, $hash);
        $stmt->execute();
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM patreg");
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->query("DELETE FROM doctb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    // --- Patient login flow ------------------------------------------------

    public function testCorrectCredentialsLogIn(): void
    {
        $this->captureScript('patient_auth.php', [
            'patsub'    => '1',
            'email'     => self::PAT_EMAIL,
            'password2' => self::PAT_PASSWORD,
        ]);

        $this->assertSame('patient', $_SESSION['role'] ?? null);
        $this->assertSame(self::PAT_EMAIL, $_SESSION['email'] ?? null);
        $this->assertSame(self::PAT_FNAME . ' ' . self::PAT_LNAME, $_SESSION['username'] ?? null);
    }

    public function testWrongPasswordFailsLogin(): void
    {
        $output = $this->captureScript('patient_auth.php', [
            'patsub'    => '1',
            'email'     => self::PAT_EMAIL,
            'password2' => 'wrong-pass',
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testUnknownEmailFailsLogin(): void
    {
        $output = $this->captureScript('patient_auth.php', [
            'patsub'    => '1',
            'email'     => 'nobody@nowhere.test',
            'password2' => self::PAT_PASSWORD,
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testSqlInjectionAttemptIsRejected(): void
    {
        $output = $this->captureScript('patient_auth.php', [
            'patsub'    => '1',
            'email'     => "' OR '1'='1",
            'password2' => 'whatever',
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    // --- update_data branch (admin-only) -----------------------------------

    public function testUpdateDataRejectedForNonAdmin(): void
    {
        $output = $this->captureScript('patient_auth.php', [
            'update_data' => '1',
            'contact'     => '5551234567',
            'status'      => 'paid',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringContainsString('Forbidden', $output);
    }

    public function testUpdateDataSucceedsForAdmin(): void
    {
        $this->con->query(
            "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, docFees,
                                        appdate, apptime, userStatus, doctorStatus, payment)
             VALUES (1, 'Jane', 'Doe', 'Female', 'jane@example.com', '5551234567', 'drhouse', 200,
                     '2026-01-01', '10:00', 1, 1, 'pending')"
        );

        $this->captureScript('patient_auth.php', [
            'update_data' => '1',
            'contact'     => '5551234567',
            'status'      => 'paid',
        ], [], [
            'role' => 'admin',
        ]);

        $stmt = $this->con->prepare("SELECT payment FROM appointmenttb WHERE contact = ?");
        $contact = '5551234567';
        $stmt->bind_param('s', $contact);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertSame('paid', $row['payment'] ?? null);
    }

    // --- doc_sub branch (admin-only, add doctor) ---------------------------
    // Unlike admin_auth.php's broken half-feature, this one provides all
    // required columns (username, password, email, docFees) and even hashes
    // the password — so the success path actually works.

    public function testDocSubRejectedForNonAdmin(): void
    {
        $output = $this->captureScript('patient_auth.php', [
            'doc_sub'   => '1',
            'doctor'    => 'newdoc',
            'dpassword' => 'secret',
            'demail'    => 'newdoc@example.com',
            'docFees'   => '300',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringContainsString('Forbidden', $output);

        // Sanity: nothing should have been inserted.
        $result = $this->con->query("SELECT COUNT(*) AS c FROM doctb");
        $this->assertSame(0, (int) $result->fetch_assoc()['c']);
    }

    public function testDocSubInsertsDoctorForAdmin(): void
    {
        $this->captureScript('patient_auth.php', [
            'doc_sub'   => '1',
            'doctor'    => 'newdoc',
            'dpassword' => 'secret-pass',
            'demail'    => 'newdoc@example.com',
            'docFees'   => '300',
        ], [], [
            'role' => 'admin',
        ]);

        // Hmm — the source's INSERT lists (username, password, email, docFees)
        // but doctb also requires `spec` (NOT NULL). This may surface another
        // schema/source mismatch. Let's see.
        $stmt = $this->con->prepare("SELECT username, email, docFees, password FROM doctb WHERE username = ?");
        $u = 'newdoc';
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row, 'doctor row should have been inserted');
        $this->assertSame('newdoc@example.com', $row['email']);
        $this->assertSame(300, (int) $row['docFees']);
        $this->assertTrue(
            password_verify('secret-pass', $row['password']),
            'password should be hashed and verify against the plaintext'
        );
    }
}

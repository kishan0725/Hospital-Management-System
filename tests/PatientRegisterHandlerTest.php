<?php
// tests/PatientRegisterHandlerTest.php — covers the patient registration flow.

namespace Hospital\Tests;

use mysqli;

final class PatientRegisterHandlerTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM patreg");
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->query("DELETE FROM doctb");
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM patreg");
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->query("DELETE FROM doctb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    private function basePostFor(string $email = 'jane@example.com'): array
    {
        return [
            'patsub1'   => '1',
            'fname'     => 'Jane',
            'lname'     => 'Doe',
            'gender'    => 'Female',
            'email'     => $email,
            'contact'   => '5551234567',
            'password'  => 'sup3r-secure',
            'cpassword' => 'sup3r-secure',
        ];
    }

    private function patientRowByEmail(string $email): ?array
    {
        $stmt = $this->con->prepare(
            "SELECT pid, fname, lname, gender, email, contact, password, cpassword
             FROM patreg WHERE email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    private function patientCount(): int
    {
        $result = $this->con->query("SELECT COUNT(*) AS c FROM patreg");
        return (int) $result->fetch_assoc()['c'];
    }

    // --- Happy path --------------------------------------------------------

    public function testValidRegistrationCreatesPatient(): void
    {
        $this->captureScript('patient_register_handler.php', $this->basePostFor());

        $row = $this->patientRowByEmail('jane@example.com');
        $this->assertNotNull($row);
        $this->assertSame('Jane', $row['fname']);
        $this->assertSame('Doe', $row['lname']);
        $this->assertSame('Female', $row['gender']);
        $this->assertSame('5551234567', $row['contact']);
    }

    public function testPasswordIsHashedNotStoredPlaintext(): void
    {
        $this->captureScript('patient_register_handler.php', $this->basePostFor());

        $row = $this->patientRowByEmail('jane@example.com');
        $this->assertNotNull($row);
        $this->assertNotSame('sup3r-secure', $row['password'], 'password must not be stored as plaintext');
        $this->assertTrue(
            password_verify('sup3r-secure', $row['password']),
            'stored hash must verify against the original password'
        );
    }

    public function testSessionEstablishedAfterRegistration(): void
    {
        $this->captureScript('patient_register_handler.php', $this->basePostFor());

        $this->assertSame('patient', $_SESSION['role'] ?? null);
        $this->assertSame('jane@example.com', $_SESSION['email'] ?? null);
        $this->assertSame('Jane Doe', $_SESSION['username'] ?? null);
        $this->assertArrayHasKey('pid', $_SESSION);
    }

    // --- Validation paths --------------------------------------------------

    public function testPasswordMismatchRejectsRegistration(): void
    {
        $post = $this->basePostFor();
        $post['cpassword'] = 'different-pass';

        $this->captureScript('patient_register_handler.php', $post);

        $this->assertSame(0, $this->patientCount(), 'No patient should be created on password mismatch');
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testShortPasswordIsRejected(): void
    {
        $post = $this->basePostFor();
        $post['password']  = 'abc';   // < 6 chars
        $post['cpassword'] = 'abc';

        $output = $this->captureScript('patient_register_handler.php', $post);

        $this->assertStringContainsString('at least 6 characters', $output);
        $this->assertSame(0, $this->patientCount());
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testNoPostDoesNothing(): void
    {
        $this->captureScript('patient_register_handler.php');

        $this->assertSame(0, $this->patientCount());
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    // --- Admin-gated branches (consistency with other auth files) ----------

    public function testUpdateDataRejectedForNonAdmin(): void
    {
        $output = $this->captureScript('patient_register_handler.php', [
            'update_data' => '1',
            'contact'     => '5551234567',
            'status'      => 'paid',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringContainsString('Forbidden', $output);
    }

    public function testDocSubRejectedForNonAdmin(): void
    {
        $output = $this->captureScript('patient_register_handler.php', [
            'doc_sub' => '1',
            'name'    => 'newdoc',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringContainsString('Forbidden', $output);
    }
}

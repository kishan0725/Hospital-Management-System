<?php
// covers the doctor login flow.

namespace Hospital\Tests;

use mysqli;

final class DoctorAuthTest extends TestCase
{
    private mysqli $con;

    // Known credentials we'll seed for every test.
    private const DOC_USERNAME = 'drhouse';
    private const DOC_PASSWORD = 'correct-horse-battery-staple';
    private const DOC_EMAIL    = 'house@example.com';

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error, 'Test DB connection failed');

        // Reset doctb and seed exactly one known doctor.
        $this->con->query("DELETE FROM doctb");
        $hash = password_hash(self::DOC_PASSWORD, PASSWORD_DEFAULT);
        $stmt = $this->con->prepare(
            "INSERT INTO doctb (username, password, email, spec, docFees) VALUES (?, ?, ?, 'Diagnostics', 200)"
        );
        $u = self::DOC_USERNAME;
        $e = self::DOC_EMAIL;
        $stmt->bind_param('sss', $u, $hash, $e);
        $stmt->execute();
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM doctb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    public function testNoPostDoesNothing(): void
    {
        $output = $this->captureScript('doctor_auth.php');

        $this->assertSame('', trim($output));
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testCorrectCredentialsLogIn(): void
    {
        $this->captureScript('doctor_auth.php', [
            'docsub1'   => '1',
            'username3' => self::DOC_USERNAME,
            'password3' => self::DOC_PASSWORD,
        ]);

        $this->assertSame('doctor', $_SESSION['role'] ?? null);
        $this->assertSame(self::DOC_USERNAME, $_SESSION['dname'] ?? null);
    }

    public function testWrongPasswordFailsLogin(): void
    {
        $output = $this->captureScript('doctor_auth.php', [
            'docsub1'   => '1',
            'username3' => self::DOC_USERNAME,
            'password3' => 'wrong-password',
        ]);

        // Should show alert and not establish a doctor session.
        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testUnknownUsernameFailsLogin(): void
    {
        $output = $this->captureScript('doctor_auth.php', [
            'docsub1'   => '1',
            'username3' => 'no-such-doctor',
            'password3' => self::DOC_PASSWORD,
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testEmptyCredentialsFailLogin(): void
    {
        $output = $this->captureScript('doctor_auth.php', [
            'docsub1'   => '1',
            'username3' => '',
            'password3' => '',
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testSqlInjectionAttemptIsRejected(): void
    {
        // Classic injection probe — should be treated as a literal username
        // (which won't exist) thanks to the prepared statement.
        $output = $this->captureScript('doctor_auth.php', [
            'docsub1'   => '1',
            'username3' => "' OR '1'='1",
            'password3' => 'whatever',
        ]);

        $this->assertStringContainsString('Invalid Username or Password', $output);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }
}

<?php
// tests/MessageSearchTest.php — covers admin-only contact message lookup.

namespace Hospital\Tests;

use mysqli;

final class MessageSearchTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM contact");

        // Seed one known message.
        $stmt = $this->con->prepare(
            "INSERT INTO contact (name, email, contact, message)
             VALUES ('Jane Doe', 'jane@example.com', '5551234567', 'Hello, this is a test.')"
        );
        $stmt->execute();
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM contact");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    public function testNoPostRendersEmptyShell(): void
    {
        $output = $this->captureScript('message_search.php', [], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('Jane Doe', $output);
        $this->assertStringNotContainsString('Hello, this is a test', $output);
    }

    public function testAdminFindsExistingMessage(): void
    {
        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => '5551234567',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('Jane Doe', $output);
        $this->assertStringContainsString('jane@example.com', $output);
        $this->assertStringContainsString('5551234567', $output);
        $this->assertStringContainsString('Hello, this is a test', $output);
    }

    public function testAdminSearchForUnknownContactShowsAlert(): void
    {
        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => '0000000000',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('Jane Doe', $output);
    }

    public function testNonAdminRejected(): void
    {
        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => '5551234567',
        ], [], [
            'role' => 'patient',
        ]);

        $this->assertStringNotContainsString('Jane Doe', $output);
        $this->assertStringNotContainsString('Hello, this is a test', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => '5551234567',
        ]);

        $this->assertStringNotContainsString('Jane Doe', $output);
        $this->assertStringNotContainsString('Hello, this is a test', $output);
    }

    public function testMessageContentIsHtmlEscaped(): void
    {
        // Replace the seed with one containing HTML — should be escaped on render.
        $this->con->query("DELETE FROM contact");
        $stmt = $this->con->prepare(
            "INSERT INTO contact (name, email, contact, message)
             VALUES ('XSS Probe', 'xss@example.com', '5559999999', ?)"
        );
        $payload = '<script>alert(1)</script>';
        $stmt->bind_param('s', $payload);
        $stmt->execute();
        $stmt->close();

        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => '5559999999',
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testSqlInjectionAttemptYieldsNoResults(): void
    {
        $output = $this->captureScript('message_search.php', [
            'mes_search_submit' => '1',
            'mes_contact'       => "' OR '1'='1",
        ], [], [
            'role' => 'admin',
        ]);

        $this->assertStringContainsString('No entries found', $output);
        $this->assertStringNotContainsString('Jane Doe', $output);
    }
}

<?php
// covers the contact form handler.

namespace Hospital\Tests;

use mysqli;

final class ContactTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error, 'Test DB connection failed');
        $this->con->query("DELETE FROM contact");
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM contact");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    public function testNoPostDoesNothing(): void
    {
        $output = $this->captureScript('contact.php');

        $this->assertSame('', trim($output));
        $this->assertSame(0, $this->countContactRows());
    }

    public function testValidSubmissionInsertsRow(): void
    {
        $this->captureScript('contact.php', [
            'btnSubmit' => '1',
            'txtName'   => 'Jane Doe',
            'txtEmail'  => 'jane@example.com',
            'txtPhone'  => '5551234567',
            'txtMsg'    => 'Hello, this is a test message.',
        ]);

        $stmt = $this->con->prepare(
            "SELECT name, email, contact, message FROM contact WHERE email = ?"
        );
        $email = 'jane@example.com';
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row);
        $this->assertSame('Jane Doe', $row['name']);
        $this->assertSame('5551234567', $row['contact']);
        $this->assertSame('Hello, this is a test message.', $row['message']);
    }

    public function testMessageOverMaxLengthIsRejected(): void
    {
        $longMessage = str_repeat('a', 201);

        $output = $this->captureScript('contact.php', [
            'btnSubmit' => '1',
            'txtName'   => 'Jane Doe',
            'txtEmail'  => 'jane@example.com',
            'txtPhone'  => '5551234567',
            'txtMsg'    => $longMessage,
        ]);

        $this->assertStringContainsString('Message too long', $output);
        $this->assertSame(0, $this->countContactRows());
    }

    public function testMessageAtMaxLengthIsAccepted(): void
    {
        // Boundary: exactly 200 chars passes both PHP check and DB column.
        $maxMessage = str_repeat('b', 200);

        $this->captureScript('contact.php', [
            'btnSubmit' => '1',
            'txtName'   => 'Jane Doe',
            'txtEmail'  => 'jane@example.com',
            'txtPhone'  => '5551234567',
            'txtMsg'    => $maxMessage,
        ]);

        $this->assertSame(1, $this->countContactRows());
    }

    private function countContactRows(): int
    {
        $result = $this->con->query("SELECT COUNT(*) AS c FROM contact");
        return (int) $result->fetch_assoc()['c'];
    }
}

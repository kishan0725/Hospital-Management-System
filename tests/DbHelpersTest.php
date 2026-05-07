<?php
// tests/DbHelpersTest.php — covers display_specs() and display_docs()
// against the test database. Smoke-tests get_db_connection() too.

namespace Hospital\Tests;

use mysqli;

final class DbHelpersTest extends TestCase
{
    private mysqli $con;

    protected function setUp(): void
    {
        // Point the helper at the test DB before requiring it.
        putenv('HMS_DB_NAME=myhmsdb_test');

        require_once dirname(__DIR__) . '/db_helpers.php';

        $this->con = new mysqli('127.0.0.1', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error, 'Test DB connection failed');

        // Clean slate, then seed two known doctor rows.
        $this->con->query("DELETE FROM doctb");
        $this->con->query(
            "INSERT INTO doctb (username, password, email, spec, docFees) VALUES
             ('drhouse', 'x', 'house@example.com', 'Diagnostics', 200),
             ('drwho',   'x', 'who@example.com',   'Time Travel', 350)"
        );
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM doctb");
        $this->con->close();
        putenv('HMS_DB_NAME');  // unset
    }

    public function testGetDbConnectionReturnsMysqliInstance(): void
    {
        $con = get_db_connection();
        $this->assertInstanceOf(mysqli::class, $con);
        $con->close();
    }

    public function testDisplaySpecsRendersDistinctSpecializations(): void
    {
        ob_start();
        display_specs();
        $output = ob_get_clean();

        $this->assertStringContainsString('Diagnostics', $output);
        $this->assertStringContainsString('Time Travel', $output);
        $this->assertStringContainsString('<option', $output);
    }

    public function testDisplaySpecsEscapesHtml(): void
    {
        // Insert a row with HTML in the spec field — should be escaped.
        $this->con->query(
            "INSERT INTO doctb (username, password, email, spec, docFees) VALUES
             ('xss', 'x', 'xss@example.com', '<script>alert(1)</script>', 100)"
        );

        ob_start();
        display_specs();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testDisplayDocsRendersDoctorOptions(): void
    {
        ob_start();
        display_docs();
        $output = ob_get_clean();

        $this->assertStringContainsString('drhouse', $output);
        $this->assertStringContainsString('drwho', $output);
        $this->assertStringContainsString('data-value="200"', $output);
        $this->assertStringContainsString('data-value="350"', $output);
        $this->assertStringContainsString('data-spec="Diagnostics"', $output);
    }

    public function testDisplayDocsEscapesHtmlInUsername(): void
    {
        $this->con->query(
            "INSERT INTO doctb (username, password, email, spec, docFees) VALUES
             ('<b>evil</b>', 'x', 'evil@example.com', 'General', 100)"
        );

        ob_start();
        display_docs();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<b>evil</b>', $output);
        $this->assertStringContainsString('&lt;b&gt;evil&lt;/b&gt;', $output);
    }
}

<?php
// tests/SqlInjectionTest.php

use PHPUnit\Framework\TestCase;

class SqlInjectionTest extends TestCase
{
    // ── Test 1: malicious input is not a valid email ───────────────────────
    // We validate BEFORE it ever reaches the DB
    public function test_sql_injection_string_fails_email_validation(): void
    {
        $maliciousEmail = "' OR '1'='1";

        // filter_var catches this before it reaches mysqli_prepare
        $isValid = filter_var($maliciousEmail, FILTER_VALIDATE_EMAIL);

        $this->assertFalse($isValid);
    }

    // ── Test 2: normal email passes validation ─────────────────────────────
    public function test_valid_email_passes_validation(): void
    {
        $email   = "doctor@hospital.com";
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);

        $this->assertNotFalse($isValid);
    }

    // ── Test 3: password is hashed before storage ──────────────────────────
    public function test_password_is_hashed_not_plain_text(): void
    {
        $plainPassword  = "Secret123";
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        // Hash must not equal original
        $this->assertNotEquals($plainPassword, $hashedPassword);

        // Hash must start with bcrypt identifier
        $this->assertStringStartsWith('$2y$', $hashedPassword);
    }

    // ── Test 4: correct password verifies against its hash ────────────────
    public function test_correct_password_verifies_successfully(): void
    {
        $plainPassword  = "Secret123";
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    // ── Test 5: wrong password fails verification ──────────────────────────
    public function test_wrong_password_fails_verification(): void
    {
        $plainPassword  = "Secret123";
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        $this->assertFalse(password_verify("WrongPassword", $hashedPassword));
    }

    // ── Test 6: htmlspecialchars neutralizes XSS in output ────────────────
    public function test_xss_attempt_is_escaped_in_output(): void
    {
        // Attacker tries to inject a script tag via doctor name
        $maliciousInput = '<script>alert("hacked")</script>';
        $safeOutput     = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');

        // Script tag must be escaped, not raw
        $this->assertStringNotContainsString('<script>', $safeOutput);
        $this->assertStringContainsString('&lt;script&gt;', $safeOutput);
    }

    // ── Test 7: docFees must be numeric ───────────────────────────────────
    public function test_doc_fees_must_be_numeric(): void
    {
        $validFees   = "500";
        $invalidFees = "'; DROP TABLE doctb; --";

        $this->assertTrue(is_numeric($validFees));
        $this->assertFalse(is_numeric($invalidFees));
    }
}
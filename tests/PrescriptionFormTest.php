<?php
// tests/PrescriptionFormTest.php — covers the doctor-only prescription form.
// The marquee security feature is the IDOR check: a doctor must not be able
// to write prescriptions against an appointment owned by another doctor,
// even by submitting that appointment's ID directly.

namespace Hospital\Tests;

use mysqli;

final class PrescriptionFormTest extends TestCase
{
    private mysqli $con;
    private int $drhouseAppointmentId;
    private int $drwhoAppointmentId;

    protected function setUp(): void
    {
        putenv('HMS_DB_NAME=myhmsdb_test');

        $this->con = new mysqli('localhost', 'root', '', 'myhmsdb_test');
        $this->assertEmpty($this->con->connect_error);

        $this->con->query("DELETE FROM prestb");
        $this->con->query("DELETE FROM appointmenttb");

        $this->drhouseAppointmentId = $this->seedAppointment('drhouse', 'Jane',  'Doe');
        $this->drwhoAppointmentId   = $this->seedAppointment('drwho',   'Steve', 'Roe');
    }

    protected function tearDown(): void
    {
        $this->con->query("DELETE FROM prestb");
        $this->con->query("DELETE FROM appointmenttb");
        $this->con->close();
        putenv('HMS_DB_NAME');
    }

    private function seedAppointment(string $doctor, string $fname, string $lname): int
    {
        $stmt = $this->con->prepare(
            "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, docFees,
                                        appdate, apptime, userStatus, doctorStatus, payment)
             VALUES (1, ?, ?, 'Female', 'patient@example.com', '5551234567', ?, 200,
                     '2026-01-01', '10:00', 1, 1, 'pending')"
        );
        $stmt->bind_param('sss', $fname, $lname, $doctor);
        $stmt->execute();
        $stmt->close();
        return (int) $this->con->insert_id;
    }

    private function prescriptionCount(): int
    {
        $result = $this->con->query("SELECT COUNT(*) AS c FROM prestb");
        return (int) $result->fetch_assoc()['c'];
    }

    private function basePostFor(int $appointmentId): array
    {
        return [
            'prescribe'    => '1',
            'pid'          => 1,
            'ID'           => $appointmentId,
            'fname'        => 'Jane',
            'lname'        => 'Doe',
            'appdate'      => '2026-01-01',
            'apptime'      => '10:00',
            'disease'      => 'Cold',
            'allergy'      => 'None',
            'prescription' => 'Rest and fluids',
        ];
    }

    // --- GET: form rendering ----------------------------------------------

    public function testDoctorSeesFormWithPrefilledPatientInfo(): void
    {
        $output = $this->captureScript(
            'prescription_form.php',
            [],
            [  // GET params
                'pid'     => '1',
                'ID'      => (string) $this->drhouseAppointmentId,
                'fname'   => 'Jane',
                'lname'   => 'Doe',
                'appdate' => '2026-01-01',
                'apptime' => '10:00',
            ],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        $this->assertStringContainsString('Welcome', $output);
        $this->assertStringContainsString('drhouse', $output);
        $this->assertStringContainsString('Disease', $output);
        $this->assertStringContainsString('Prescription', $output);
        // Hidden inputs should carry the GET-supplied values forward.
        $this->assertMatchesRegularExpression('/name=["\']fname["\'][^>]*value=["\']Jane["\']/', $output);
        $this->assertMatchesRegularExpression('/name=["\']appdate["\'][^>]*value=["\']2026-01-01["\']/', $output);
    }

    public function testDoctorFormHtmlEscapesValues(): void
    {
        // Stored XSS guard: hostile fname from GET must not break out of the
        // hidden input value.
        $output = $this->captureScript(
            'prescription_form.php',
            [],
            [
                'pid'     => '1',
                'ID'      => '1',
                'fname'   => '<b>x</b>',
                'lname'   => 'Doe',
                'appdate' => '2026-01-01',
                'apptime' => '10:00',
            ],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        $this->assertStringNotContainsString('<b>x</b>', $output);
        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $output);
    }

    // --- Role gate ---------------------------------------------------------

    public function testNonDoctorRejected(): void
    {
        $output = $this->captureScript('prescription_form.php', [], [], [
            'role' => 'admin',
        ]);
        $this->assertStringNotContainsString('Welcome', $output);
        $this->assertStringNotContainsString('Disease', $output);
    }

    public function testUnauthenticatedRejected(): void
    {
        $output = $this->captureScript('prescription_form.php');
        $this->assertStringNotContainsString('Welcome', $output);
        $this->assertStringNotContainsString('Disease', $output);
    }

    public function testDoctorWithEmptyDnameRejected(): void
    {
        $output = $this->captureScript('prescription_form.php', [], [], [
            'role'  => 'doctor',
            'dname' => '',
        ]);
        $this->assertStringNotContainsString('Welcome', $output);
    }

    // --- POST: prescription insertion (with ownership check) --------------

    public function testDoctorCanPrescribeOwnAppointment(): void
    {
        $this->captureScript(
            'prescription_form.php',
            $this->basePostFor($this->drhouseAppointmentId),
            [],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        $this->assertSame(1, $this->prescriptionCount());

        $stmt = $this->con->prepare("SELECT doctor, disease, prescription FROM prestb LIMIT 1");
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertSame('drhouse', $row['doctor']);
        $this->assertSame('Cold', $row['disease']);
        $this->assertSame('Rest and fluids', $row['prescription']);
    }

    /**
     * THE MARQUEE SECURITY TEST.
     *
     * A doctor must NOT be able to write a prescription against another
     * doctor's appointment by submitting that appointment's ID. The source's
     * ownership check is what prevents this — if anyone removes it, this
     * test should fail loudly.
     */
    public function testDoctorCannotPrescribeAnotherDoctorsAppointment(): void
    {
        // drhouse tries to prescribe against drwho's appointment ID.
        $output = $this->captureScript(
            'prescription_form.php',
            $this->basePostFor($this->drwhoAppointmentId),
            [],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        // No prescription should have been written.
        $this->assertSame(0, $this->prescriptionCount(), 'IDOR: insert must be blocked');

        // The user-facing message should make the rejection visible.
        $this->assertStringContainsString('Forbidden', $output);
    }

    public function testNonExistentAppointmentIdIsRejected(): void
    {
        $post = $this->basePostFor(999999);

        $output = $this->captureScript(
            'prescription_form.php',
            $post,
            [],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        $this->assertSame(0, $this->prescriptionCount());
        $this->assertStringContainsString('Forbidden', $output);
    }

    public function testNonDoctorCannotPostPrescription(): void
    {
        // Even with valid POST data, a non-doctor session must be rejected
        // before the ownership check runs.
        $this->captureScript(
            'prescription_form.php',
            $this->basePostFor($this->drhouseAppointmentId),
            [],
            [
                'role' => 'admin',
            ]
        );

        $this->assertSame(0, $this->prescriptionCount());
    }

    public function testSqlInjectionAttemptThroughIdIsRejected(): void
    {
        $post = $this->basePostFor(1);
        $post['ID'] = "1 OR 1=1";  // would be catastrophic without prepared stmt

        $output = $this->captureScript(
            'prescription_form.php',
            $post,
            [],
            [
                'role'  => 'doctor',
                'dname' => 'drhouse',
            ]
        );

        $this->assertSame(0, $this->prescriptionCount());
    }
}

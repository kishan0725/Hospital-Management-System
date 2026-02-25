<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../config.php';



function display_docs()
{
    try {
        $con = getDB();
        $stmt = $con->prepare("SELECT username, fullname, spec, docFees FROM doctb ORDER BY spec, fullname");
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $username = htmlspecialchars($row['username']);
            $fullname = htmlspecialchars($row['fullname']);
            $spec = htmlspecialchars($row['spec']);
            $cost = isset($row['docFees']) ? $row['docFees'] : '';

            echo '<option value="' . $fullname . '" data-spec="' . $spec . '" data-value="' . $cost . '">BS. ' . $fullname . ' (' . $spec . ')</option>';
        }
    } catch (PDOException $e) {
        error_log("Display doctors error: " . $e->getMessage());
        echo '<option value="">Error loading doctors</option>';
    }
}



function display_specs()
{
    try {
        $con = getDB();
        $stmt = $con->prepare("SELECT DISTINCT spec FROM doctb ORDER BY spec");
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $spec = htmlspecialchars($row['spec']);
            echo '<option value="' . $spec . '">' . $spec . '</option>';
        }
    } catch (PDOException $e) {
        error_log("Display specs error: " . $e->getMessage());
        echo '<option value="">Error loading specializations</option>';
    }
}



function updatePaymentStatus($contact, $status)
{
    
    error_log("WARNING: updatePaymentStatus called but 'payment' field does not exist in appointmenttb table");
    return false;
}



function addDoctor($username, $password, $email, $docFees)
{
    try {
        $con = getDB();

        
        $stmt = $con->prepare("SELECT id FROM doctb WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Doctor already exists'];
        }

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO doctb (username, password, email, docFees) VALUES (:username, :password, :email, :docFees)");
        $result = $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email' => $email,
            ':docFees' => $docFees
        ]);

        return ['success' => $result, 'id' => $con->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Add doctor error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



function registerPatient($fname, $lname, $gender, $email, $contact, $password, $cpassword)
{
    try {
        if ($password !== $cpassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        $con = getDB();

        
        $stmt = $con->prepare("SELECT pid FROM patreg WHERE email = :email OR contact = :contact");
        $stmt->execute([':email' => $email, ':contact' => $contact]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Patient already registered'];
        }

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword) 
                               VALUES (:fname, :lname, :gender, :email, :contact, :password, :cpassword)");

        $result = $stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':gender' => $gender,
            ':email' => $email,
            ':contact' => $contact,
            ':password' => $hashedPassword,
            ':cpassword' => $hashedPassword
        ]);

        if ($result) {
            $pid = $con->lastInsertId();

            
            $_SESSION['pid'] = $pid;
            $_SESSION['username'] = $fname . " " . $lname;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['gender'] = $gender;
            $_SESSION['contact'] = $contact;
            $_SESSION['email'] = $email;

            return ['success' => true, 'pid' => $pid];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    } catch (PDOException $e) {
        error_log("Register patient error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



function authenticateUser($username, $password, $userType = 'auto')
{
    try {
        $con = getDB();

        if ($userType === 'auto' || $userType === 'patient') {
            
            $stmt = $con->prepare("SELECT * FROM patreg WHERE email = :username");
            $stmt->execute([':username' => $username]);

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    $_SESSION['pid'] = $row['pid'];
                    $_SESSION['username'] = $row['fname'] . " " . $row['lname'];
                    $_SESSION['fname'] = $row['fname'];
                    $_SESSION['lname'] = $row['lname'];
                    $_SESSION['gender'] = $row['gender'];
                    $_SESSION['contact'] = $row['contact'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['user_type'] = 'patient';
                    return ['success' => true, 'type' => 'patient'];
                }
            }
        }

        if ($userType === 'auto' || $userType === 'doctor') {
            
            $stmt = $con->prepare("SELECT * FROM doctb WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    $_SESSION['dname'] = $row['username'];
                    $_SESSION['user_type'] = 'doctor';
                    return ['success' => true, 'type' => 'doctor'];
                }
            }
        }

        if ($userType === 'auto' || $userType === 'admin') {
            
            $stmt = $con->prepare("SELECT * FROM admintb WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_type'] = 'admin';
                    return ['success' => true, 'type' => 'admin'];
                }
            }
        }

        return ['success' => false, 'message' => 'Invalid credentials'];
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication error'];
    }
}



function getAppointments($filter = [])
{
    try {
        $con = getDB();
        $query = "SELECT * FROM appointmenttb WHERE 1=1";
        $params = [];

        if (isset($filter['contact'])) {
            $query .= " AND contact = :contact";
            $params[':contact'] = $filter['contact'];
        }

        if (isset($filter['doctor'])) {
            $query .= " AND doctor = :doctor";
            $params[':doctor'] = $filter['doctor'];
        }

        $query .= " ORDER BY appdate DESC, apptime DESC";

        $stmt = $con->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get appointments error: " . $e->getMessage());
        return [];
    }
}



function getPatientByContact($contact)
{
    try {
        $con = getDB();
        $stmt = $con->prepare("SELECT * FROM patreg WHERE contact = :contact");
        $stmt->execute([':contact' => $contact]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get patient error: " . $e->getMessage());
        return null;
    }
}



function getDoctorByUsername($username)
{
    try {
        $con = getDB();
        $stmt = $con->prepare("SELECT * FROM doctb WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get doctor error: " . $e->getMessage());
        return null;
    }
}



function createAppointment($fname, $lname, $email, $contact, $doctor, $appdate, $apptime)
{
    try {
        $con = getDB();

        $stmt = $con->prepare("INSERT INTO appointmenttb (fname, lname, email, contact, doctor, appdate, apptime, userStatus, doctorStatus) 
                               VALUES (:fname, :lname, :email, :contact, :doctor, :appdate, :apptime, '1', '1')");

        $result = $stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':contact' => $contact,
            ':doctor' => $doctor,
            ':appdate' => $appdate,
            ':apptime' => $apptime
        ]);

        return ['success' => $result, 'id' => $con->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Create appointment error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}



function isLoggedIn()
{
    return isset($_SESSION['username']) || isset($_SESSION['dname']) || isset($_SESSION['pid']);
}



function requireLogin($userType = null)
{
    if (!isLoggedIn()) {
        header("Location: index1.php");
        exit();
    }

    if ($userType && isset($_SESSION['user_type']) && $_SESSION['user_type'] !== $userType) {
        header("Location: index1.php");
        exit();
    }
}





if (isset($_POST['update_data'])) {
    $contact = sanitize($_POST['contact']);
    $status = sanitize($_POST['status']);

    if (updatePaymentStatus($contact, $status)) {
        echo "<script>alert('Payment status updated successfully!'); window.location.href = document.referrer;</script>";
    } else {
        echo "<script>alert('Failed to update payment status!'); window.location.href = document.referrer;</script>";
    }
    exit();
}


if (isset($_POST['doc_sub'])) {
    $doctor = sanitize($_POST['username3'] ?? $_POST['doctor'] ?? '');
    $password = $_POST['dpassword'] ?? $_POST['password3'] ?? '';
    $email = sanitize($_POST['demail'] ?? '');
    $docFees = sanitize($_POST['docFees'] ?? '500');

    $result = addDoctor($doctor, $password, $email, $docFees);

    if ($result['success']) {
        echo "<script>alert('Doctor added successfully!'); window.location.href = 'dashboard/admin/admin-dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to add doctor: " . ($result['message'] ?? 'Unknown error') . "'); window.location.href = document.referrer;</script>";
    }
    exit();
}

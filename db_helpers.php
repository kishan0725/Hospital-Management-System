<?php

// ── Session Setup ──────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// ── Auth Check ─────────────────────────────────────────────────────────────
function check_login(): void
{
    if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
        header("Location: user-login.php");
        exit();
    }
}

// ── Database Connection ────────────────────────────────────────────────────
function get_db_connection(): mysqli
{
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
    if (!$con) {
        error_log("DB connection failed: " . mysqli_connect_error());
        die("A server error occurred. Please try again later.");
    }
    return $con;
}

// ── Add Doctor ─────────────────────────────────────────────────────────────

function add_doctor(
    string $username,
    string $password,
    string $email,
    string $spec,
    string $docFees
): bool {
    $con = get_db_connection();

    // FIXED: password should be hashed, never stored as plain text.
    // password_hash() is a one-way scramble — even if DB is stolen,
    // passwords are unreadable.
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepare: send query STRUCTURE first, no data yet
    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO doctb (username, password, email, spec, docFees)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return false;
    }

    // Bind: attach data SEPARATELY — "sssss" means 5 strings
    // s = string, i = integer, d = double, b = blob
    mysqli_stmt_bind_param($stmt, "sssss",
        $username,
        $hashedPassword,
        $email,
        $spec,
        $docFees
    );

    // Execute: DB processes them safely
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $success;
}

// ── Delete Doctor ──────────────────────────────────────────────────────────

function delete_doctor(string $email): bool
{
    $con = get_db_connection();

    $stmt = mysqli_prepare(
        $con,
        "DELETE FROM doctb WHERE email = ?"
    );

    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $success;
}

// ── Update Appointment Payment ─────────────────────────────────────────────
function update_payment_status(string $contact, string $status): bool
{
    $con = get_db_connection();

    $stmt = mysqli_prepare(
        $con,
        "UPDATE appointmenttb SET payment = ? WHERE contact = ?"
    );

    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ss", $status, $contact);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $success;
}

// ── Display Specializations ────────────────────────────────────────────────
// No user input here so no injection risk, but we still
// escape output with htmlspecialchars() to prevent XSS
function display_specs(): void
{
    $con    = get_db_connection();
    $result = mysqli_query($con, "SELECT DISTINCT spec FROM doctb");

    while ($row = mysqli_fetch_array($result)) {
        $spec = htmlspecialchars($row['spec'], ENT_QUOTES, 'UTF-8');
        echo '<option data-value="' . $spec . '">' . $spec . '</option>';
    }

    mysqli_close($con);
}

// ── Display Doctors ────────────────────────────────────────────────────────
function display_docs(): void
{
    $con    = get_db_connection();
    $result = mysqli_query($con, "SELECT * FROM doctb");

    while ($row = mysqli_fetch_array($result)) {
        $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
        $price    = htmlspecialchars($row['docFees'],  ENT_QUOTES, 'UTF-8');
        $spec     = htmlspecialchars($row['spec'],     ENT_QUOTES, 'UTF-8');

        echo '<option value="'  . $username . '"'
           . ' data-value="'   . $price    . '"'
           . ' data-spec="'    . $spec     . '">'
           . $username . '</option>';
    }

    mysqli_close($con);
}
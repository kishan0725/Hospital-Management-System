<?php

// FIXED: session_start() must be called before anything SESSION-related.
// It was commented out before, meaning sessions never worked at all.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // JavaScript cannot steal the session cookie
        'samesite' => 'Strict'
    ]);
    session_start();
}

/**
 * Checks if the user is logged in.
 * If not, redirects to the login page and STOPS execution.
 */
function check_login(): void
{
    // FIXED: Use isset() first — accessing $_SESSION['login'] directly
    // when it doesn't exist causes a PHP warning.
    if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
        // FIXED: Use a simple relative redirect instead of
        // building the URL manually (which can break on subfolders).
        header("Location: user-login.php");

        // FIXED: exit() MUST come after header redirect.
        // Without it, PHP keeps running the rest of the page
        // even though the browser is being redirected away.
        exit();
    }
}

/**
 * Returns the DB connection.
 * Centralizing this means you only update credentials in one place.
 */
function get_db_connection(): mysqli
{
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
    if (!$con) {
        // Don't expose raw DB errors to the browser in production
        error_log("DB connection failed: " . mysqli_connect_error());
        die("A server error occurred. Please try again later.");
    }
    return $con;
}

function display_specs(): void
{
    $con = get_db_connection();

    $result = mysqli_query($con, "SELECT DISTINCT spec FROM doctb");
    while ($row = mysqli_fetch_array($result)) {
        $spec = htmlspecialchars($row['spec']); 
        echo '<option data-value="' . $spec . '">' . $spec . '</option>';
    }
}

function display_docs(): void
{
    $con = get_db_connection();
    $result = mysqli_query($con, "SELECT * FROM doctb");
    while ($row = mysqli_fetch_array($result)) {
        $username = htmlspecialchars($row['username']); 
        $price    = htmlspecialchars($row['docFees']);
        $spec     = htmlspecialchars($row['spec']);
        echo '<option value="' . $username . '" data-value="' . $price . '" data-spec="' . $spec . '">'
           . $username . '</option>';
    }
}
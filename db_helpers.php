<?php
//  minimal helpers kept ONLY for doctor/specialization

// Session cookie hardening.
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
 * Returns a DB connection.
 * NOTE: pages do NOT have to use this — most pages connect inline.
 * Kept so display_specs() / display_docs() still work when included.
 */
function get_db_connection(): mysqli
{
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
    if (!$con) {
        error_log("DB connection failed: " . mysqli_connect_error());
        die("A server error occurred. Please try again later.");
    }
    return $con;
}

/**
 * Renders <option> tags of distinct doctor specializations.
 * Output is HTML-escaped to prevent stored XSS if a doctor record ever
 * contained HTML/JS in the spec field.
 */
function display_specs(): void
{
    $con = get_db_connection();
    $result = mysqli_query($con, "SELECT DISTINCT spec FROM doctb");
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $spec = htmlspecialchars($row['spec'] ?? '', ENT_QUOTES, 'UTF-8');
            echo '<option data-value="' . $spec . '">' . $spec . '</option>';
        }
    }
    mysqli_close($con);
}

/**
 * Renders <option> tags for every doctor, with fees + specialization
 * as data-* attributes for client-side filtering.
 * All fields are HTML-escaped.
 */
function display_docs(): void
{
    $con = get_db_connection();
    $result = mysqli_query($con, "SELECT username, docFees, spec FROM doctb");
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $username = htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8');
            $price    = htmlspecialchars($row['docFees']  ?? '', ENT_QUOTES, 'UTF-8');
            $spec     = htmlspecialchars($row['spec']     ?? '', ENT_QUOTES, 'UTF-8');
            echo '<option value="' . $username . '" data-value="' . $price . '" data-spec="' . $spec . '">'
               . $username . '</option>';
        }
    }
    mysqli_close($con);
}

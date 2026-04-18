<?php
// handles the public "contact us" form.


$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}

if (isset($_POST['btnSubmit'])) {
    $name    = $_POST['txtName']  ?? '';
    $email   = $_POST['txtEmail'] ?? '';
    $contact = $_POST['txtPhone'] ?? '';
    $message = $_POST['txtMsg']   ?? '';

    // Hard cap on message length to avoid abuse.
    if (strlen($message) > 5000) {
        echo '<script>alert("Message too long."); window.location.href = "contact.html";</script>';
        exit();
    }

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO contact (name, email, contact, message) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contact, $message);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        echo '<script type="text/javascript">';
        echo 'alert("Message sent successfully!");';
        echo 'window.location.href = "contact.html";';
        echo '</script>';
        exit();
    }
}

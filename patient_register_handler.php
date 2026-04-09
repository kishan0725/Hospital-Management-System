<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (isset($_POST['patsub1'])) {
    $fname    = $_POST['fname'];
    $lname    = $_POST['lname'];
    $gender   = $_POST['gender'];
    $email    = $_POST['email'];
    $contact  = $_POST['contact'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($password == $cpassword) {


        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // FIXED: Prepared statement — user data is bound separately
        // so it can never break out of the SQL string.
        $stmt = mysqli_prepare($con,
            "INSERT INTO patreg (fname, lname, gender, email, contact, password)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssssss",
            $fname, $lname, $gender, $email, $contact, $hashedPassword
        );
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($result) {
            // Save info in session so the dashboard knows who is logged in
            $_SESSION['username'] = $fname . " " . $lname;
            $_SESSION['fname']    = $fname;
            $_SESSION['lname']    = $lname;
            $_SESSION['gender']   = $gender;
            $_SESSION['contact']  = $contact;
            $_SESSION['email']    = $email;
            header("Location: patient_dashboard.php");
            exit();
        }
    } else {
        header("Location: error_password_mismatch.php");
        exit();
    }
}


if (isset($_POST['doc_sub'])) {
    $name = $_POST['name'];

    // FIXED: Prepared statement instead of raw concatenation
    $stmt = mysqli_prepare($con, "INSERT INTO doctb (name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        header("Location: adddoc.php");
        exit();
    }
}
?>

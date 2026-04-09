<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

// ── Auth gate (CVE-2018-9206 fix) ──────────────────────────────────────────
// The original file had zero authentication, making it a publicly accessible
// arbitrary file upload endpoint. Any visitor could upload PHP shells or other
// malicious files without logging in.
//
// This endpoint must only be reachable by logged-in doctors or admins.
// We check the session before the UploadHandler is even loaded — if the
// check fails, we return 403 and stop. Nothing in UploadHandler.php runs.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

$isDoctor = !empty($_SESSION['dname']);
$isAdmin  = !empty($_SESSION['username']);

if (!$isDoctor && !$isAdmin) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
// ── End auth gate ──────────────────────────────────────────────────────────

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$upload_handler = new UploadHandler();
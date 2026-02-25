<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');


ob_start();

session_start();
require_once '../../config.php';
require_once '../../includes/pdf_exports.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'patients':
        
        ob_end_clean();
        exportPatientsList($pdo);
        break;
    
    case 'doctors':
        ob_end_clean();
        exportDoctorsList($pdo);
        break;
    
    case 'revenue':
        ob_end_clean();
        exportRevenueReport($pdo);
        break;
    
    case 'appointments':
        ob_end_clean();
        exportAppointmentsList($pdo);
        break;
    
    default:
        header("Location: dashboard.php");
        exit();
}

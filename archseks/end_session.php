<?php
session_start();
include('config.php');

// Security: Ensure only admins can trigger this if they are logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id_number'])) {
    $id_number = mysqli_real_escape_string($conn, $_GET['id_number']);

    // 1. Check current sessions first
    $check_query = "SELECT sessions FROM users WHERE id_number = '$id_number'";
    $result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($result);

    if ($row && $row['sessions'] > 0) {
        // 2. Subtract exactly 1 session
        $update_query = "UPDATE users SET sessions = sessions - 1 WHERE id_number = '$id_number'";
        
        if (mysqli_query($conn, $update_query)) {
            // Success: Redirect back to the records or dashboard
            header("Location: view_records.php?msg=session_deducted");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
        echo "Student has 0 sessions remaining.";
    }
} else {
    header("Location: admin_dashboard.php");
}
?>
<?php
session_start();
include('config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Get student_id to deduct session
    $res = mysqli_query($conn, "SELECT student_id FROM reservations WHERE id = '$reservation_id'");
    $data = mysqli_fetch_assoc($res);
    
    if ($data) {
        $student_id = $data['student_id'];

        // 2. Update Reservation: Change status and set Time Out
        $update_res = "UPDATE reservations SET 
                       status = 'Completed', 
                       time_out = CURRENT_TIME() 
                       WHERE id = '$reservation_id'";

        if (mysqli_query($conn, $update_res)) {
            // 3. Deduct 1 Session from the student
            mysqli_query($conn, "UPDATE users SET sessions = sessions - 1 WHERE id_number = '$student_id'");
            
            // Redirect back to monitoring page
            header("Location: sit_in.php?success=loggedout");
            exit();
        }
    }
}

header("Location: sit_in.php");
exit();
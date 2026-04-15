<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $res_id = mysqli_real_escape_string($conn, $_POST['reservation_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // CRITICAL: We set is_read = 0 so the Admin sees it as a NEW notification
    $sql = "UPDATE reservations SET 
            rating = '$rating', 
            feedback_comment = '$comment',
            is_read = 0 
            WHERE id = '$res_id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: history.php?success=1");
        exit();
    }
}
?>
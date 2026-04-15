<?php
session_start();

// 1. Clear the Admin's login session
$_SESSION = array();
session_destroy();

// 2. Go back to login - NO student sessions are touched here
header("Location: login.php");
exit();
?>
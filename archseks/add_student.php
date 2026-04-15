<?php
session_start();
include('config.php');

if (isset($_POST['btn_save'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_number']);
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year = mysqli_real_escape_string($conn, $_POST['year_level']);
    
    // Default password set to '1234' for new students
    $query = "INSERT INTO users (id_number, first_name, last_name, course, year_level, role, sessions, password) 
              VALUES ('$id', '$fname', '$lname', '$course', '$year', 'student', 30, '1234')";

    if (mysqli_query($conn, $query)) {
        header("Location: manage_students.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 400px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-save { background: #007bff; color: white; border: none; width: 100%; padding: 12px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 style="margin-top:0;">Add New Student</h2>
        <form method="POST">
            <input type="text" name="id_number" placeholder="ID Number" required>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <select name="course" required>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSIS">BSIS</option>
                <option value="ACT">ACT</option>
            </select>
            <select name="year_level" required>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
            <button type="submit" name="btn_save" class="btn-save">Save Student</button>
            <a href="manage_students.php" style="display:block; text-align:center; margin-top:10px; color:gray; text-decoration:none;">Cancel</a>
        </form>
    </div>
</body>
</html>
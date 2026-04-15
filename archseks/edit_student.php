<?php
session_start();
include('config.php');

if (!isset($_GET['id'])) { header("Location: manage_students.php"); exit(); }

$original_id = mysqli_real_escape_string($conn, $_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM users WHERE id_number = '$original_id'");
$data = mysqli_fetch_assoc($res);

if (!$data) { die("Student not found."); }

if (isset($_POST['btn_update'])) {
    $new_id = mysqli_real_escape_string($conn, $_POST['id_number']);
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year = mysqli_real_escape_string($conn, $_POST['year_level']);

    // Update query: sessions is omitted because it is readonly
    $update_query = "UPDATE users SET 
                     id_number='$new_id', 
                     first_name='$fname', 
                     last_name='$lname', 
                     course='$course', 
                     year_level='$year' 
                     WHERE id_number='$original_id'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: manage_students.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student | CCS Admin</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 400px; }
        h2 { color: #4b2c82; text-align: center; margin-top: 0; }
        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .locked-field { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border: 1px solid #cbd5e1; }
        .btn-update { background: #28a745; color: white; border: none; width: 100%; padding: 14px; cursor: pointer; font-weight: bold; border-radius: 6px; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Edit Student Info</h2>
        <form method="POST">
            <label>ID Number</label>
            <input type="text" name="id_number" value="<?php echo htmlspecialchars($data['id_number']); ?>" required>

            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($data['first_name']); ?>" required>
            
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($data['last_name']); ?>" required>
            
            <label>Course</label>
            <select name="course">
                <option value="BSIT" <?php if($data['course']=="BSIT") echo "selected"; ?>>BSIT</option>
                <option value="BSCS" <?php if($data['course']=="BSCS") echo "selected"; ?>>BSCS</option>
                <option value="BSIS" <?php if($data['course']=="BSIS") echo "selected"; ?>>BSIS</option>
                <option value="ACT" <?php if($data['course']=="ACT") echo "selected"; ?>>ACT</option>
            </select>
            
            <label>Year Level</label>
            <select name="year_level" required>
                <option value="1" <?php if($data['year_level'] == "1") echo "selected"; ?>>1st Year</option>
                <option value="2" <?php if($data['year_level'] == "2") echo "selected"; ?>>2nd Year</option>
                <option value="3" <?php if($data['year_level'] == "3") echo "selected"; ?>>3rd Year</option>
                <option value="4" <?php if($data['year_level'] == "4") echo "selected"; ?>>4th Year</option>
            </select>
            
            <label>Remaining Sessions</label>
            <input type="number" value="<?php echo htmlspecialchars($data['sessions']); ?>" class="locked-field" readonly>
            
            <button type="submit" name="btn_update" class="btn-update">Update Changes</button>
            <a href="manage_students.php" style="display:block; text-align:center; margin-top:15px; color:#94a3b8; text-decoration:none;">Cancel</a>
        </form>
    </div>
</body>
</html>
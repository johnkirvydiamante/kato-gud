<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_number = '$uid'");
$user = mysqli_fetch_assoc($query);
if (!$user) { die("Error: Student data not found."); }

$currentPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.png';
$photoPath = "uploads/" . $currentPhoto;
$version = file_exists($photoPath) ? filemtime($photoPath) : time();

if (isset($_POST['update'])) {
    $new_id = mysqli_real_escape_string($conn, $_POST['id_number']);
    $first = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $level = mysqli_real_escape_string($conn, $_POST['course_level']);
    
    $photoName = $currentPhoto;
    if (!empty($_FILES['photo']['name'])) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $photoName = $new_id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/" . $photoName);
            if ($currentPhoto != 'default.png' && file_exists("uploads/" . $currentPhoto)) {
                unlink("uploads/" . $currentPhoto);
            }
        }
    }

    $sql = "UPDATE users SET id_number='$new_id', first_name='$first', middle_name='$middle', last_name='$last', email='$email', address='$address', course='$course', course_level='$level', profile_photo='$photoName' WHERE id_number='$uid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_id'] = $new_id;
        header("Location: dashboard.php?update=success");
        exit();
    } else {
        echo "<script>alert('Error updating profile: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-gold: #ffcc00;
            --text-muted: #64748b;
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f5 100%);
            min-height: 100vh;
        }
        .professional-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(75,44,130,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .uc-logo { width: 50px; height: 50px; object-fit: contain; }
        .system-title { display: flex; flex-direction: column; }
        .system-title h1 { font-size: 1rem; font-weight: 600; color: var(--ccs-purple); }
        .system-title h1 span { font-weight: 400; color: var(--text-muted); }
        .system-title .college-name { font-size: 0.7rem; color: var(--text-muted); }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .nav-links a:hover { color: var(--ccs-purple); }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 600; }
        .btn-logout {
            background: var(--ccs-gold);
            color: var(--ccs-purple-dark) !important;
            padding: 8px 20px !important;
            border-radius: 40px;
            font-weight: 700 !important;
        }
        .edit-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .edit-card {
            background: white;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 24px 32px;
            text-align: center;
        }
        .card-header h2 { font-size: 1.8rem; font-weight: 700; }
        .card-body { padding: 32px; }
        .profile-photo-section { text-align: center; margin-bottom: 32px; border-bottom: 2px solid #fff0b5; padding-bottom: 24px; }
        .photo-preview { position: relative; display: inline-block; cursor: pointer; }
        .photo-preview img {
            width: 140px; height: 140px; border-radius: 50%;
            object-fit: cover; border: 4px solid var(--ccs-gold);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .photo-overlay {
            position: absolute; bottom: 0; right: 0;
            background: var(--ccs-purple); border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid white;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        .form-group label {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: #1e293b; margin-bottom: 8px;
        }
        .input-with-icon { position: relative; }
        .input-with-icon i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); color: #9ca3af;
        }
        .input-with-icon input, .input-with-icon select, .input-with-icon textarea {
            width: 100%; padding: 12px 16px 12px 42px;
            border: 1.5px solid #e2e8f0; border-radius: 14px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
        }
        .input-with-icon textarea { min-height: 80px; resize: vertical; }
        .input-with-icon input:focus, .input-with-icon select:focus {
            outline: none; border-color: var(--ccs-purple);
            box-shadow: 0 0 0 4px rgba(75,44,130,0.08);
        }
        .btn-save {
            width: 100%; padding: 14px;
            background: var(--ccs-purple); color: white;
            border: none; border-radius: 40px;
            font-weight: 700; font-size: 1rem;
            cursor: pointer; display: flex;
            align-items: center; justify-content: center;
            gap: 10px; margin-top: 24px;
        }
        .btn-save:hover { background: #3a1f64; transform: translateY(-2px); }
        .btn-cancel {
            width: 100%; padding: 12px;
            background: transparent; border: 1.5px solid #e2e8f0;
            border-radius: 40px; font-weight: 600;
            text-align: center; text-decoration: none;
            display: inline-block; margin-top: 12px;
            color: var(--text-muted);
        }
        @media (max-width: 768px) {
            .header-container { flex-direction: column; height: auto; padding: 12px 20px; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 16px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>
<header class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" alt="University of Cebu" class="uc-logo">
            <div class="system-title">
                <h1>College of Computer Studies <span>| Sit-in Monitoring System</span></h1>
            </div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="edit_profile.php" class="active">Edit Profile</a>
            <a href="history.php">History</a>
            <a href="reservation.php">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="edit-container">
    <div class="edit-card">
        <div class="card-header"><h2><i class="fas fa-user-edit"></i> Edit Profile</h2><p>Update your personal information and profile photo</p></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-photo-section">
                    <label for="file-input" class="photo-preview">
                        <img id="preview" src="uploads/<?php echo $currentPhoto; ?>?v=<?php echo $version; ?>" onerror="this.src='uploads/default.png'">
                        <div class="photo-overlay"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" name="photo" id="file-input" style="display:none;" accept="image/*" onchange="loadFile(event)">
                </div>
                <div class="form-grid">
                    <div class="form-group full-width"><label>ID Number</label><div class="input-with-icon"><i class="fas fa-qrcode"></i><input type="text" name="id_number" value="<?php echo htmlspecialchars($user['id_number']); ?>" required></div></div>
                    <div class="form-group"><label>First Name</label><div class="input-with-icon"><i class="fas fa-user"></i><input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required></div></div>
                    <div class="form-group"><label>Middle Name</label><div class="input-with-icon"><i class="fas fa-user"></i><input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>"></div></div>
                    <div class="form-group full-width"><label>Last Name</label><div class="input-with-icon"><i class="fas fa-user"></i><input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required></div></div>
                    <div class="form-group full-width"><label>Email Address</label><div class="input-with-icon"><i class="fas fa-envelope"></i><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div></div>
                    <div class="form-group full-width"><label>Home Address</label><div class="input-with-icon"><i class="fas fa-home"></i><textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea></div></div>
                    <div class="form-group"><label>Course</label><div class="input-with-icon"><i class="fas fa-graduation-cap"></i>
    <select name="course" required>
        <option value="Information Technology" <?php echo ($user['course']=='Information Technology')?'selected':''; ?>>Information Technology</option>
        <option value="Computer Engineering" <?php echo ($user['course']=='Computer Engineering')?'selected':''; ?>>Computer Engineering</option>
        <option value="Civil Engineering" <?php echo ($user['course']=='Civil Engineering')?'selected':''; ?>>Civil Engineering</option>
        <option value="Mechanical Engineering" <?php echo ($user['course']=='Mechanical Engineering')?'selected':''; ?>>Mechanical Engineering</option>
        <option value="Electrical Engineering" <?php echo ($user['course']=='Electrical Engineering')?'selected':''; ?>>Electrical Engineering</option>
        <option value="Industrial Engineering" <?php echo ($user['course']=='Industrial Engineering')?'selected':''; ?>>Industrial Engineering</option>
        <option value="Naval Architecture and Marine Engineering" <?php echo ($user['course']=='Naval Architecture and Marine Engineering')?'selected':''; ?>>Naval Architecture and Marine Engineering</option>
        <option value="Elementary Education (BEEd)" <?php echo ($user['course']=='Elementary Education (BEEd)')?'selected':''; ?>>Elementary Education (BEEd)</option>
        <option value="Secondary Education (BSEd)" <?php echo ($user['course']=='Secondary Education (BSEd)')?'selected':''; ?>>Secondary Education (BSEd)</option>
        <option value="Criminology" <?php echo ($user['course']=='Criminology')?'selected':''; ?>>Criminology</option>
        <option value="Commerce" <?php echo ($user['course']=='Commerce')?'selected':''; ?>>Commerce</option>
        <option value="Accountancy" <?php echo ($user['course']=='Accountancy')?'selected':''; ?>>Accountancy</option>
        <option value="Hotel and Restaurant Management" <?php echo ($user['course']=='Hotel and Restaurant Management')?'selected':''; ?>>Hotel and Restaurant Management</option>
        <option value="Customs Administration" <?php echo ($user['course']=='Customs Administration')?'selected':''; ?>>Customs Administration</option>
        <option value="Computer Secretarial" <?php echo ($user['course']=='Computer Secretarial')?'selected':''; ?>>Computer Secretarial</option>
        <option value="Industrial Psychology" <?php echo ($user['course']=='Industrial Psychology')?'selected':''; ?>>Industrial Psychology</option>
        <option value="AB Political Science" <?php echo ($user['course']=='AB Political Science')?'selected':''; ?>>AB Political Science</option>
        <option value="AB English" <?php echo ($user['course']=='AB English')?'selected':''; ?>>AB English</option>
        <option value="CISCO Networking Academy (Module 1 - 4)" <?php echo ($user['course']=='CISCO Networking Academy (Module 1 - 4)')?'selected':''; ?>>CISCO Networking Academy (Module 1 - 4)</option>
        <option value="English Communication Skills" <?php echo ($user['course']=='English Communication Skills')?'selected':''; ?>>English Communication Skills</option>
        <option value="Conversational Korean" <?php echo ($user['course']=='Conversational Korean')?'selected':''; ?>>Conversational Korean</option>
    </select>
</div></div>
                    <div class="form-group"><label>Year Level</label><div class="input-with-icon"><i class="fas fa-layer-group"></i><select name="course_level" required>
                        <option value="1st Year" <?php echo ($user['course_level']=='1st Year')?'selected':''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo ($user['course_level']=='2nd Year')?'selected':''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo ($user['course_level']=='3rd Year')?'selected':''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo ($user['course_level']=='4th Year')?'selected':''; ?>>4th Year</option>
                    </select></div></div>
                </div>
                <button type="submit" name="update" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
                <a href="dashboard.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
    function loadFile(event) {
        var reader = new FileReader();
        reader.onload = function() { document.getElementById('preview').src = reader.result; };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
</body>
</html>
<?php
session_start();
include('config.php');

// 1. Security: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

// 2. Fetch FRESH student data using id_number
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_number = '$uid'");
$user = mysqli_fetch_assoc($query);

if (!$user) { 
    die("Error: Student data not found. Please contact the administrator."); 
}

// 3. Handle Profile Photo
$currentPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.png';
$photoPath = "uploads/" . $currentPhoto;
// Anti-caching version for the image
$version = file_exists($photoPath) ? filemtime($photoPath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-gold: #ffcc00;
            --ccs-gold-dark: #e6b800;
            --bg-gray: #f4f7f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; min-height: 100vh; }

        /* HEADER */
        .professional-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        .system-title h1 { font-size: 1rem; font-weight: 600; color: var(--ccs-purple); }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--ccs-purple); }
        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }

        /* MAIN LAYOUT */
        .dashboard-container {
            display: flex;
            gap: 24px;
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .info-card, .content-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            flex: 1;
        }

        .card-header {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 16px 20px;
            font-weight: 700;
        }
        .card-header i { color: var(--ccs-gold); margin-right: 8px; }

        /* STUDENT INFO */
        .card-body { padding: 24px; }
        .profile-img-container {
            width: 130px; height: 130px;
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 4px solid var(--ccs-gold);
            overflow: hidden;
            background: #f0f0f0;
        }
        .profile-img-container img { width: 100%; height: 100%; object-fit: cover; }
        .student-info-list { list-style: none; }
        .student-info-list li {
            padding: 12px 0;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        .student-info-list li i { width: 30px; color: var(--ccs-purple); }
        .student-info-list li strong { min-width: 80px; color: var(--text-dark); }

        /* ADMIN PROFILE BOX */
        .admin-profile-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .admin-avatar {
            width: 45px;
            height: 45px;
            background: var(--ccs-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .admin-status {
            font-size: 0.8rem;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ANNOUNCEMENT HISTORY */
        #live-announcement { 
            max-height: 320px; 
            overflow-y: auto; 
            padding-right: 5px;
        }
        #live-announcement::-webkit-scrollbar { width: 4px; }
        #live-announcement::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        .rules-list { list-style: none; }
        .rules-list li { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eee; font-size: 0.85rem; color: var(--text-muted); }
        .rules-list li i { color: var(--ccs-gold); }

        @media (max-width: 1024px) { .dashboard-container { flex-direction: column; } }
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
            <a href="dashboard.php" class="active">Home</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="history.php">History</a>
            <a href="reservation.php">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <div class="info-card">
        <div class="card-header"><i class="fas fa-user-graduate"></i> Student Information</div>
        <div class="card-body">
            <div class="profile-img-container">
                <img src="uploads/<?php echo $currentPhoto; ?>?v=<?php echo $version; ?>" 
                     onerror="this.src='uploads/default.png'">
            </div>
            <ul class="student-info-list">
                <li><i class="fas fa-id-card"></i> <strong>ID:</strong> <span><?php echo htmlspecialchars($user['id_number']); ?></span></li>
                <li><i class="fas fa-user"></i> <strong>Name:</strong> <span><?php echo strtoupper(htmlspecialchars($user['first_name'] . " " . $user['last_name'])); ?></span></li>
                <li><i class="fas fa-graduation-cap"></i> <strong>Course:</strong> <span><?php echo htmlspecialchars($user['course']); ?></span></li>
                <li><i class="fas fa-layer-group"></i> <strong>Year:</strong> <span><?php echo htmlspecialchars($user['course_level']); ?></span></li>
                <li><i class="fas fa-clock"></i> <strong>Session:</strong> <span><?php echo $user['sessions']; ?> remaining</span></li>
            </ul>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header"><i class="fas fa-bullhorn"></i> Announcement</div>
        <div class="card-body">
            <div class="admin-profile-box">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h4 style="font-size: 0.95rem; color: var(--ccs-purple-dark);">CCS Admin</h4>
                    <div class="admin-status">
                        <i></i>
                        
                    </div>
                </div>
            </div>

            <div id="live-announcement">
                <p style="text-align:center; color:#888; padding-top:20px;">Fetching records...</p>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header"><i class="fas fa-gavel"></i> Rules and Regulation</div>
        <div class="card-body">
            <ul class="rules-list">
                <li><i class="fas fa-volume-mute"></i> <span>Maintain silence within the laboratory.</span></li>
                <li><i class="fas fa-gamepad"></i> <span>Games are strictly prohibited.</span></li>
                <li><i class="fas fa-power-off"></i> <span>Properly shut down computers after use.</span></li>
                <li><i class="fas fa-utensils"></i> <span>No food or drinks allowed inside.</span></li>
            </ul>
        </div>
    </div>
</div>

<script>
    function updateAnnouncement() {
        fetch('fetch_announcement.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('live-announcement').innerHTML = data;
            })
            .catch(err => console.error('Error fetching announcement:', err));
    }

    // Refresh every 10 seconds for real-time feel
    setInterval(updateAnnouncement, 10000);
    window.onload = updateAnnouncement;
</script>

</body>
</html>
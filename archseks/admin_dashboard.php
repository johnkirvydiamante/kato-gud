<?php
session_start();
include('config.php');

// 1. Security: Only allow Admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- BADGE LOGIC ---
// Counts how many students are currently "Active" to display in the navigation
$badge_query = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM reservations WHERE status = 'Active'");
$badge_data = mysqli_fetch_assoc($badge_query);
$active_count = $badge_data['active_count'] ?? 0;

// --- FEEDBACK BADGE LOGIC ---
// Counts unread feedback (ratings > 0 that haven't been viewed yet)
$unread_feedback_query = mysqli_query($conn, "SELECT COUNT(*) as unread FROM reservations WHERE rating > 0 AND is_read = 0");
$unread_feedback_count = mysqli_fetch_assoc($unread_feedback_query)['unread'] ?? 0;

// --- DATABASE LOGIC ---
$search_result = null;
$sit_in_success = false;
$target_id = "";

// A. Handle Search Button (Inside the Modal)
if (isset($_POST['search_btn'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_input']);
    if (!empty($search_query)) {
        $sql = "SELECT * FROM users WHERE role = 'student' AND (id_number = '$search_query' OR first_name LIKE '%$search_query%' OR last_name LIKE '%$search_query%') LIMIT 1";
        $res = mysqli_query($conn, $sql);
        if (mysqli_num_rows($res) > 0) {
            $search_result = mysqli_fetch_assoc($res);
        } else {
            echo "<script>alert('Error: Student with ID $search_query not found.');</script>";
        }
    }
}

// B. Handle the actual "Sit In" Submission
if (isset($_POST['btn_sitin_submit'])) {
    $id_num = mysqli_real_escape_string($conn, $_POST['id_number']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $lab = mysqli_real_escape_string($conn, $_POST['lab']);
    
    // Check if student is already sat in to prevent duplicates
    $check = mysqli_query($conn, "SELECT id FROM reservations WHERE student_id = '$id_num' AND status = 'Active'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Student is already currently sitting in!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        $insert_sql = "INSERT INTO reservations (student_id, purpose, lab, status, time_in, date) 
                       VALUES ('$id_num', '$purpose', '$lab', 'Active', CURRENT_TIME(), CURRENT_DATE())";

        if (mysqli_query($conn, $insert_sql)) {
            $sit_in_success = true; // Trigger for the success toast
            $target_id = $id_num;
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// C. Handle New Announcement
if (isset($_POST['submit_announcement'])) {
    $content = mysqli_real_escape_string($conn, $_POST['announcement_text']);
    if (!empty($content)) {
        $insert_query = "INSERT INTO announcements (content) VALUES ('$content')";
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('Announcement published!'); window.location.href='admin_dashboard.php';</script>";
            exit();
        }
    }
}

// D. Dashboard Statistics
$students_registered = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$currently_in = $active_count; // Using the badge count for consistency
$total_sitin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as history FROM reservations"))['history'] ?? 0;
$announcements_list = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CCS Sit-in</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --ccs-purple: #4b2c82; 
            --ccs-purple-dark: #3a235c; 
            --ccs-gold: #ffcc00; 
            --text-dark: #1e293b; 
            --text-muted: #64748b;
            --danger: #e11d48;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --feedback-pink: #df42f5;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; color: var(--text-dark); }

        /* Navigation */
        .professional-header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 80px; }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.85rem; cursor: pointer; transition: 0.2s; display: flex; align-items: center; }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 600; }
        
        /* The Notification Badges */
        .nav-badge {
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 50px;
            font-weight: 800;
            margin-left: 6px;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(225, 29, 72, 0.3);
        }

        .badge-feedback {
            background: var(--feedback-pink);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 50px;
            font-weight: 800;
            margin-left: 6px;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(223, 66, 245, 0.3);
        }

        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }

        /* Dashboard Layout */
        .dashboard-container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
        .stats-row { display: flex; gap: 24px; margin-bottom: 32px; }
        .stat-card { flex: 1; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-header { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; padding: 20px; text-align: center; font-weight: 600; }
        .stat-body { padding: 30px; text-align: center; }
        .stat-number { font-size: 3rem; font-weight: 800; color: var(--ccs-purple); }
        
        .two-columns { display: flex; gap: 24px; }
        .card { flex: 1; background: white; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; padding: 18px 24px; font-weight: 700; }
        .card-body { padding: 24px; }

        /* Form Controls */
        .form-control { width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; margin-bottom: 15px; font-family: inherit; transition: 0.2s; }
        .form-control:focus { outline: none; border-color: var(--ccs-purple); box-shadow: 0 0 0 3px rgba(75, 44, 130, 0.1); }
        .btn-search { background: var(--ccs-purple); color: white; border: none; padding: 12px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 600; transition: 0.3s; }
        .btn-sitin { background: #dfa915; color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; width: 100%; padding: 15px; transition: 0.3s; }
        .btn-sitin:hover { background: #c89712; }

        /* Enhanced Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 500px; border-radius: 28px; overflow: hidden; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease-out; }
        .modal-header { background: var(--ccs-purple); color: white; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
        
        .search-section { background: #f8fafc; padding: 20px; border-radius: 18px; margin-bottom: 20px; border: 1px solid #edf2f7; }
        .student-info-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 20px; }
        .info-label { font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; display: block; }
        .info-value { font-size: 1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 15px; }
        .session-badge { background: #f0fdf4; color: #166534; padding: 4px 12px; border-radius: 20px; font-weight: 800; border: 1px solid #bbf7d0; font-size: 0.85rem; }

        /* Success Toast */
        .success-toast { position: fixed; top: 20px; right: 20px; background: #166534; color: white; padding: 16px 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px; z-index: 3000; box-shadow: 0 10px 15px rgba(0,0,0,0.1); animation: slideInRight 0.5s ease-out; }

        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>

<header class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" alt="Logo" style="width: 45px;">
            <h1 style="font-size: 1.1rem; color: var(--ccs-purple);">College of Computer Studies <span style="font-weight: 400; color: var(--text-muted);">| Admin</span></h1>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active">Home</a>
            <a onclick="toggleSearch()"><i class="fas fa-search"></i> Search</a>
            <a href="manage_students.php">Students</a>
            <a href="sit_in.php">
                Sit-in 
                <?php if($active_count > 0): ?>
                    <span class="nav-badge"><?php echo $active_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="view_sitin.php">View Records</a>
            <a href="feedback_report.php">
                Feedback Reports
                <?php if($unread_feedback_count > 0): ?>
                    <span class="badge-feedback"><?php echo $unread_feedback_count; ?></span>
                <?php endif; ?>
            </a> 
            <a href="logout.php" class="btn-logout" onclick="return confirm('Log out from Admin?')">Log out</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-header"><i class="fas fa-user-graduate"></i> Registered Students</div>
            <div class="stat-body"><div class="stat-number"><?php echo $students_registered; ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-header"><i class="fas fa-desktop"></i> Currently Sitting In</div>
            <div class="stat-body"><div class="stat-number"><?php echo $currently_in; ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-header"><i class="fas fa-chart-line"></i> Total History</div>
            <div class="stat-body"><div class="stat-number"><?php echo $total_sitin; ?></div></div>
        </div>
    </div>

    <div class="two-columns">
        <div class="card">
            <div class="card-header"><i class="fas fa-bullhorn"></i> Post Announcement</div>
            <div class="card-body">
                <form method="POST">
                    <textarea name="announcement_text" class="form-control" placeholder="Type your message to students here..." style="min-height:120px;" required></textarea>
                    <button type="submit" name="submit_announcement" class="btn-search">Publish Announcement</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><i class="fas fa-clock"></i> Recent Announcements</div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if(mysqli_num_rows($announcements_list) > 0): ?>
                    <?php while($ann = mysqli_fetch_assoc($announcements_list)): ?>
                        <div style="padding:15px; background:#f8fafc; border-radius:12px; margin-bottom:12px; border-left:5px solid var(--ccs-purple);">
                            <small style="color:var(--text-muted); font-weight: 600;"><?php echo date('F d, Y', strtotime($ann['created_at'])); ?></small>
                            <p style="margin-top: 5px; font-size: 0.95rem;"><?php echo htmlspecialchars($ann['content']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted);">No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="searchModal" class="modal" style="<?php echo (isset($_POST['search_btn'])) ? 'display: flex;' : 'display: none;'; ?>">
    <div class="modal-content">
        <div class="modal-header">
            
            <span style="cursor:pointer; font-size: 24px; opacity: 0.8;" onclick="toggleSearch()">&times;</span>
        </div>
        
        <div style="padding: 30px;">
            <form method="POST" class="search-section">
                <label class="info-label">Search Student ID</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search_input" class="form-control" placeholder="" style="margin-bottom:0;" required autofocus>
                    <button type="submit" name="search_btn" class="btn-search" style="width: auto; padding: 0 25px;"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <?php if ($search_result): ?>
                <form method="POST" class="student-info-card">
                    <input type="hidden" name="id_number" value="<?php echo $search_result['id_number']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <span class="info-label">Student Name</span>
                            <div class="info-value"><?php echo htmlspecialchars($search_result['first_name'].' '.$search_result['last_name']); ?></div>
                        </div>
                        <div style="text-align: right;">
                            <span class="info-label">Remaining</span>
                            <span class="session-badge"><?php echo $search_result['sessions']; ?> Sessions</span>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="info-label">Purpose</label>
                            <select name="purpose" class="form-control" required>
                                <option>Java Programming</option>
                                <option>C# Programming</option>
                                <option>Web Development</option>
                                <option>Database Systems</option>
                                <option>Capstone Project</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="info-label">Laboratory</label>
                            <select name="lab" class="form-control" required>
                                <option>Lab 524</option>
                                <option>Lab 526</option>
                                <option>Lab 542</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="btn_sitin_submit" class="btn-sitin" style="margin-top: 15px;">Confirm & Start Session</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleSearch() {
        var modal = document.getElementById("searchModal");
        if (modal.style.display === "flex") {
            modal.style.display = "none";
            if(window.location.search.includes('search_btn')) {
                window.location.href = 'admin_dashboard.php';
            }
        } else {
            modal.style.display = "flex";
        }
    }

    <?php if ($sit_in_success): ?>
        const toast = document.createElement('div');
        toast.className = 'success-toast';
        toast.innerHTML = `<i class="fas fa-check-circle"></i> Sit-in successfully started for <?php echo $target_id; ?>!`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = '0.5s';
            setTimeout(() => { window.location.href = 'admin_dashboard.php'; }, 500);
        }, 2500);
    <?php endif; ?>
</script>
</body>
</html>
<?php
session_start();
include('config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$logout_trigger = false;
$name_to_display = "";

// --- LOGOUT LOGIC (Updates status and triggers UI) ---
if (isset($_GET['logout_id'])) {
    $res_id = $_GET['logout_id'];
    
    // Get name for the modal
    $name_q = mysqli_query($conn, "SELECT u.first_name, u.last_name FROM reservations r JOIN users u ON r.student_id = u.id_number WHERE r.id = '$res_id'");
    $name_d = mysqli_fetch_assoc($name_q);
    $name_to_display = $name_d['first_name'] . " " . $name_d['last_name'];

    $update = "UPDATE reservations SET status = 'Done', time_out = CURRENT_TIME() WHERE id = '$res_id'";
    if (mysqli_query($conn, $update)) {
        $logout_trigger = true;
    }
}

// Fetch Active Sit-in Count for the Badge
$badge_query = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM reservations WHERE status = 'Active'");
$badge_data = mysqli_fetch_assoc($badge_query);
$active_count = $badge_data['active_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sit-in Monitoring | CCS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ccs-purple: #4b2c82; --ccs-purple-dark: #3a235c; --ccs-gold: #ffcc00; --bg-gray: #f4f7f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); }

        /* Your Original Header CSS */
        .professional-header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 80px; }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { text-decoration: none; color: #64748b; font-weight: 500; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 600; }
        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }
        .nav-badge { background: #e11d48; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50px; font-weight: 800; min-width: 18px; text-align: center; }

        /* Your Original Content CSS */
        .container { max-width: 1300px; margin: 40px auto; padding: 0 40px; }
        .page-header { margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .table-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; }
        th { padding: 18px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #1e293b; }
        .session-count { font-weight: 800; color: var(--ccs-purple); background: #f0ebf8; padding: 4px 10px; border-radius: 8px; }
        .status-active { color: #166534; font-weight: 700; }
        .status-completed { color: #64748b; font-weight: 600; font-style: italic; }

        /* Added Modal & Toast for the update */
        .modal { display: <?php echo $logout_trigger ? 'flex' : 'none'; ?>; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 400px; border-radius: 28px; padding: 40px; text-align: center; box-shadow: 0 20px 25px rgba(0,0,0,0.1); }
        .success-toast { position: fixed; top: 20px; right: 20px; background: #059669; color: white; padding: 16px 24px; border-radius: 12px; z-index: 3000; box-shadow: 0 10px 15px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px; font-weight: 600; transition: opacity 0.5s; }
    </style>
</head>
<body>

<header class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" style="width:45px;">
            <h1 style="font-size: 1.1rem; color: var(--ccs-purple);">College of Computer Studies <span style="font-weight: 400; color: #64748b;">| Admin</span></h1>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_dashboard.php?openSearch=true"><i class="fas fa-search"></i> Search</a>
            <a href="manage_students.php">Students</a>
            <a href="sit_in.php" class="active">
                Sit-in 
                <?php if($active_count > 0): ?>
                    <span class="nav-badge"><?php echo $active_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="view_sitin.php">View Records</a>
            <a href="feedback_report.php">Feedback Reports</a> 
            <a href="logout.php" class="btn-logout" onclick="return confirm('Log out from Admin?')">Log out</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-header">
        <h2 style="color:var(--ccs-purple);">Sit-in Monitoring</h2>
        
    </div>
    
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Sessions</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT r.*, u.first_name, u.last_name, u.sessions 
                          FROM reservations r 
                          JOIN users u ON r.student_id = u.id_number 
                          WHERE r.date = CURRENT_DATE() 
                          ORDER BY FIELD(r.status, 'Active') DESC, r.id DESC";
                $result = mysqli_query($conn, $query);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $is_active = ($row['status'] == 'Active');
                    echo "<tr>
                            <td style='font-weight:600;'>{$row['student_id']}</td>
                            <td>{$row['first_name']} {$row['last_name']}</td>
                            <td>{$row['purpose']}</td>
                            <td>{$row['lab']}</td>
                            <td><span class='session-count'>{$row['sessions']}</span></td>
                            <td>" . ($is_active ? "<span class='status-active'>● Active</span>" : "<span class='status-completed'>Logged Out</span>") . "</td>
                            <td>";
                    if($is_active) {
                        echo "<a href='sit_in.php?logout_id={$row['id']}' onclick=\"return confirm('Log out student?')\" style='color:#ef4444; font-weight:700; text-decoration:none;'><i class='fas fa-sign-out-alt'></i> Log out</a>";
                    } else {
                        echo "<span style='color:#94a3b8; font-size:0.8rem;'>Done</span>";
                    }
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="logoutModal" class="modal">
    <div class="modal-content">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: #059669; margin-bottom: 20px;"></i>
        <h2 style="color: var(--ccs-purple);">Session Ended</h2>
        <p>Logged out: <b><?php echo htmlspecialchars($name_to_display); ?></b></p>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 15px;">Redirecting to records...</p>
    </div>
</div>

<script>
    <?php if ($logout_trigger): ?>
        const toast = document.createElement('div');
        toast.className = 'success-toast';
        toast.innerHTML = `<i class="fas fa-sign-out-alt"></i> Logout successful!`;
        document.body.appendChild(toast);

        setTimeout(() => {
            document.getElementById("logoutModal").style.display = "none";
            toast.style.opacity = '0';
            setTimeout(() => { window.location.href = 'view_sitin.php'; }, 500);
        }, 1000); 
    <?php endif; ?>
</script>
</body>
</html>
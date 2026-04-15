<?php
session_start();
include('config.php');

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- BADGE LOGIC ---
$badge_query = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM reservations WHERE status = 'Active'");
$badge_data = mysqli_fetch_assoc($badge_query);
$active_count = $badge_data['active_count'] ?? 0;

$unread_feedback_query = mysqli_query($conn, "SELECT COUNT(*) as unread FROM reservations WHERE rating > 0 AND is_read = 0");
$unread_feedback_count = mysqli_fetch_assoc($unread_feedback_query)['unread'] ?? 0;

// --- UPDATED SQL LOGIC ---
// Priority 1: Active students
// Priority 2: Most recent Time Out (the person who just logged out goes to the top of the history)
$query = "SELECT r.id AS sit_id, u.id_number, CONCAT(u.first_name, ' ', u.last_name) AS full_name, 
          r.purpose, r.lab, u.sessions, r.status, r.date, r.time_in, r.time_out
          FROM reservations r
          JOIN users u ON r.student_id = u.id_number
          ORDER BY 
            CASE WHEN r.status = 'Active' THEN 1 ELSE 2 END ASC, 
            r.time_out DESC, 
            r.id DESC"; 

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Records | CCS Sit-in</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <style>
        :root { 
            --ccs-purple: #4b2c82; 
            --ccs-purple-dark: #3a235c; 
            --ccs-gold: #ffcc00; 
            --text-dark: #1e293b; 
            --text-muted: #64748b;
            --danger: #e11d48;
            --feedback-pink: #df42f5;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
        
        .nav-badge { background: var(--danger); color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 50px; font-weight: 800; margin-left: 6px; line-height: 1; }
        .badge-feedback { background: var(--feedback-pink); color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 50px; font-weight: 800; margin-left: 6px; line-height: 1; }
        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }

        .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
        .card { background: white; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; padding: 18px 24px; font-weight: 700; }
        .card-body { padding: 30px; }

        .status-badge { padding: 5px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; }
        .status-active { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
        .status-done { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        /* Datatable Styling */
        .dataTables_wrapper .dataTables_filter input { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 8px; margin-bottom: 10px; }
        table.dataTable thead th { border-bottom: 2px solid #f1f5f9 !important; }
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
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_dashboard.php"><i class="fas fa-search"></i> Search</a>
            <a href="manage_students.php">Students</a>
            <a href="sit_in.php">
                Sit-in 
                <?php if($active_count > 0): ?>
                    <span class="nav-badge"><?php echo $active_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="view_sitin.php" class="active">View Records</a>
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

<div class="container">
    <div class="card">
        <div class="card-header"><i class="fas fa-history"></i> Sit-in History</div>
        <div class="card-body">
            <table id="recordsTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Laboratory</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $is_active = ($row['status'] == 'Active');
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--ccs-purple);">#<?php echo $row['sit_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['lab']); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['time_in'])); ?></td>
                        <td>
                            <?php 
                                if ($is_active) {
                                    echo '<span style="color: #cbd5e1">Ongoing...</span>';
                                } else {
                                    echo date('h:i A', strtotime($row['time_out']));
                                }
                            ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-done'; ?>">
                                <?php echo $is_active ? 'Active' : 'Logged Out'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#recordsTable').DataTable({
            "ordering": false, // Disable DataTables sorting to keep the PHP SQL order (Active first, then latest logout)
            "pageLength": 10,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            }
        });
    });
</script>
</body>
</html>
<?php
session_start();
// 1. Set PHP Timezone
date_default_timezone_set('Asia/Manila');

include('config.php');

// 2. Set Database Timezone (Ensures MySQL 'CURRENT_DATE' matches Manila)
mysqli_query($conn, "SET time_zone = '+08:00'");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- BADGE LOGIC ---
$unread_query = mysqli_query($conn, "SELECT COUNT(*) as unread_count FROM reservations WHERE rating > 0 AND is_read = 0");
$unread_data = mysqli_fetch_assoc($unread_query);
$unread_count = $unread_data['unread_count'];

$active_query = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM reservations WHERE status = 'Active'");
$active_data = mysqli_fetch_assoc($active_query);
$active_count = $active_data['active_count'] ?? 0;

if ($unread_count > 0) {
    mysqli_query($conn, "UPDATE reservations SET is_read = 1 WHERE rating > 0 AND is_read = 0");
}

// --- DATA FETCHING ---
$stats_query = mysqli_query($conn, "SELECT 
    AVG(rating) as avg_rating, 
    COUNT(*) as total,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as s5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as s4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as s3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as s2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as s1
    FROM reservations WHERE rating > 0");
$s = mysqli_fetch_assoc($stats_query);

$feed_query = mysqli_query($conn, "SELECT r.*, u.first_name, u.last_name, u.profile_pic 
    FROM reservations r 
    LEFT JOIN users u ON r.student_id = u.id_number 
    WHERE r.rating > 0 
    ORDER BY r.date DESC, r.time_out DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Reports | CCS Admin</title>
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
            --feedback-pink: #df42f5;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: var(--text-dark); }

        .professional-header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 80px; }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.85rem; transition: 0.2s; display: flex; align-items: center; }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 600; }
        
        .nav-badge { background: var(--danger); color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 50px; font-weight: 800; margin-left: 6px; line-height: 1; }
        .badge-feedback { background: var(--feedback-pink); color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 50px; font-weight: 800; margin-left: 6px; line-height: 1; }
        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }

        .dashboard-container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
        .stats-row { display: flex; gap: 24px; margin-bottom: 32px; }
        .stat-card { flex: 1; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: all 0.3s ease; }
        
        .hover-card { cursor: pointer; border: 2px solid transparent; }
        .hover-card:hover { transform: translateY(-5px); border-color: var(--ccs-purple); box-shadow: 0 15px 35px rgba(75, 44, 130, 0.1); }

        .stat-header { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; padding: 15px; text-align: center; font-weight: 600; font-size: 0.9rem; }
        .stat-body { padding: 25px; text-align: center; }
        .stat-number { font-size: 3.5rem; font-weight: 800; color: var(--ccs-purple); line-height: 1; }

        #feedbackSection { display: none; opacity: 0; transform: translateY(10px); transition: opacity 0.4s ease, transform 0.4s ease; }
        #feedbackSection.active { display: block; opacity: 1; transform: translateY(0); }

        .feedback-container { background: white; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        .feedback-item { display: flex; gap: 20px; padding: 25px; border-bottom: 1px solid #f1f5f9; }
        .student-photo { width: 55px; height: 55px; border-radius: 12px; object-fit: cover; border: 2px solid var(--ccs-gold); background: #eee; }
        .stars { color: var(--ccs-gold); font-size: 0.9rem; }
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
            <a href="manage_students.php">Students</a>
            <a href="sit_in.php">
                Sit-in <?php if($active_count > 0): ?><span class="nav-badge"><?php echo $active_count; ?></span><?php endif; ?>
            </a>
            <a href="view_sitin.php">View Records</a>
            <a href="feedback_report.php" class="active">
                Feedback Reports <?php if($unread_count > 0): ?><span class="badge-feedback"><?php echo $unread_count; ?></span><?php endif; ?>
            </a> 
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-header"><i class="fas fa-star"></i> Avg Rating</div>
            <div class="stat-body">
                <div class="stat-number"><?php echo number_format($s['avg_rating'], 1); ?></div>
                <div style="color: var(--ccs-gold); margin-top: 5px;">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= round($s['avg_rating'])) ? '★' : '☆'; ?>
                </div>
            </div>
        </div>

        <div class="stat-card" style="flex: 1.5;">
            <div class="stat-header"><i class="fas fa-chart-bar"></i> Breakdown</div>
            <div class="stat-body" style="padding: 15px 40px;">
                <?php 
                $levels = [5 => $s['s5'], 4 => $s['s4'], 3 => $s['s3'], 2 => $s['s2'], 1 => $s['s1']];
                foreach($levels as $stars => $count): 
                    $percent = ($s['total'] > 0) ? ($count / $s['total']) * 100 : 0;
                ?>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; font-weight: 700; width: 50px;"><?php echo $stars; ?> Star</span>
                    <div style="flex-grow: 1; height: 8px; background: #eee; border-radius: 10px; overflow: hidden;">
                        <div style="height: 100%; background: var(--ccs-gold); width: <?php echo $percent; ?>%;"></div>
                    </div>
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted);"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="stat-card hover-card" id="feedbackTrigger">
            <div class="stat-header"><i class="fas fa-comments"></i> Total Feedback</div>
            <div class="stat-body">
                <div class="stat-number"><?php echo $s['total']; ?></div>
                <p style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 10px;">
                    Point cursor to preview <i class="fas fa-eye" style="margin-left: 5px; color: var(--ccs-purple);"></i>
                </p>
            </div>
        </div>
    </div>

    <div id="feedbackSection">
        <div class="feedback-container">
            <div class="stat-header" style="text-align: left; padding-left: 25px;">
                <i class="fas fa-list"></i> Students Feedback
            </div>
            <?php if (mysqli_num_rows($feed_query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($feed_query)): ?>
                    <div class="feedback-item">
                        <?php 
                            $photo = (!empty($row['profile_pic']) && file_exists($row['profile_pic'])) 
                                     ? $row['profile_pic'] 
                                     : "https://ui-avatars.com/api/?name=".urlencode($row['first_name'])."&background=4b2c82&color=fff";
                        ?>
                        <img src="<?php echo $photo; ?>" class="student-photo">
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <h4 style="color: var(--ccs-purple); font-weight: 700;"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;"><?php echo date('M d, Y', strtotime($row['date'])); ?></span>
                            </div>
                            <div class="stars">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                            </div>
                            <p style="font-style: italic; margin: 8px 0; color: #444; font-size: 0.95rem;">"<?php echo htmlspecialchars($row['feedback_comment'] ?: 'No comment provided.'); ?>"</p>
                            <span style="background: #f1f5f9; padding: 3px 10px; border-radius: 6px; font-size: 0.7rem; color: var(--ccs-purple); font-weight: 800;">
                                <?php echo $row['lab']; ?> | <?php echo $row['purpose']; ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 50px; text-align: center; color: var(--text-muted);">No feedback found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const trigger = document.getElementById('feedbackTrigger');
    const section = document.getElementById('feedbackSection');

    trigger.addEventListener('mouseenter', () => { section.classList.add('active'); });
    section.addEventListener('mouseenter', () => { section.classList.add('active'); });

    trigger.addEventListener('mouseleave', () => {
        setTimeout(() => {
            if (!section.matches(':hover')) { section.classList.remove('active'); }
        }, 100);
    });

    section.addEventListener('mouseleave', () => { section.classList.remove('active'); });
</script>

</body>
</html>
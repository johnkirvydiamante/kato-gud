<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

// Check if redirected from submit_feedback.php with success parameter
$showSuccess = isset($_GET['success']) ? true : false;

// Optimized Query to fetch history and join with users for session data
$query = mysqli_query($conn, "SELECT r.*, u.id_number, u.sessions 
                              FROM reservations r 
                              JOIN users u ON r.student_id = u.id_number 
                              WHERE u.id_number = (SELECT id_number FROM users WHERE id = '$uid' OR id_number = '$uid' LIMIT 1) 
                              ORDER BY r.date DESC, r.time_in DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-gold: #ffcc00;
            --text-muted: #64748b;
            --text-dark: #1e293b;
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; min-height: 100vh; color: var(--text-dark); }

        .professional-header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .header-container { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 80px; }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .uc-logo { width: 50px; }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 700; }
        .btn-logout { background: var(--ccs-gold); color: var(--ccs-purple-dark) !important; padding: 8px 20px !important; border-radius: 40px; font-weight: 700 !important; }

        .history-container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .history-card { background: white; border-radius: 24px; box-shadow: var(--shadow-lg); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%); color: white; padding: 30px; }
        .card-body { padding: 30px; }
        
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8fafc; color: var(--text-muted); text-transform: uppercase; font-size: 0.7rem; padding: 15px; border-bottom: 2px solid #edf2f7; text-align: left; font-weight: 800; }
        .data-table td { padding: 20px 15px; border-bottom: 1px solid #eef2f6; font-size: 0.85rem; vertical-align: middle; }

        .time-box { background: #fdfcf6; border: 1px solid #fef3c7; padding: 8px 12px; border-radius: 12px; font-size: 0.8rem; line-height: 1.5; }
        
        /* Session & Status Badges - Matched to Sit-in UI */
        .session-indicator { background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 8px; font-weight: 700; border: 1px solid #bbf7d0; font-size: 0.75rem; margin-top: 5px; display: inline-block; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
        .status-active { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
        .status-done { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .btn-feedback { padding: 8px 18px; border-radius: 20px; border: 1.5px solid #10b981; color: #10b981; font-weight: 700; cursor: pointer; background: transparent; transition: 0.2s; }
        .btn-feedback:hover { background: #10b981; color: white; }

        /* MODAL STYLES */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 450px; border-radius: 24px; padding: 35px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; text-align: center; animation: popIn 0.3s ease-out; }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin: 20px 0; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2.5rem; color: #cbd5e1; cursor: pointer; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: var(--ccs-gold); }
        
        .feedback-textarea { width: 100%; height: 100px; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; resize: none; margin-bottom: 20px; }
        .btn-submit { width: 100%; background: var(--ccs-purple); color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: var(--ccs-purple-dark); }
        
        .success-icon { font-size: 4.5rem; color: #10b981; margin-bottom: 20px; }
    </style>
</head>
<body>

<header class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" alt="Logo" class="uc-logo">
            <h1 style="font-size: 1.1rem; color: var(--ccs-purple);">College of Computer Studies| <span style="font-weight: 400; color: var(--text-muted);">Sit-In Monitoring System</span></h1>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="history.php" class="active">History</a>
            <a href="reservation.php">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="history-container">
    <div class="history-card">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> History</h2>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lab / Purpose</th>
                        <th>Date</th>
                        <th>Log Details</th>
                        <th>Sessions</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query)): 
                        $is_active = ($row['status'] == 'Active');
                        $isCompleted = ($row['status'] == 'Completed' || (!empty($row['time_out']) && $row['time_out'] != '00:00:00'));
                        $hasFeedback = (!empty($row['rating']) && $row['rating'] > 0);
                    ?>
                    <tr>
                        <td>
                            <strong style="color: var(--ccs-purple);"><?php echo htmlspecialchars($row['purpose']); ?></strong><br>
                            <small style="color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['lab']); ?></small>
                        </td>
                        <td style="font-weight: 500;"><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                        <td>
                            <div class="time-box">
                                <span style="color: var(--text-muted);">In:</span> <?php echo date('h:i A', strtotime($row['time_in'])); ?><br>
                                <span style="color: var(--text-muted);">Out:</span> <?php echo ($isCompleted || !empty($row['time_out'])) ? date('h:i A', strtotime($row['time_out'])) : '--:--'; ?>
                            </div>
                        </td>
                        <td>
                            <span class="session-indicator">
                                <i class="fas fa- hourglass-half"></i> <?php echo $row['sessions']; ?> Left
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-done'; ?>">
                                <?php echo $is_active ? 'Active' : ($row['status'] == 'Completed' ? 'Logged Out' : $row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if(($isCompleted || $row['status'] == 'Completed') && !$hasFeedback): ?>
                                <button class="btn-feedback" onclick="openFeedbackModal(<?php echo $row['id']; ?>)">Feedback</button>
                            <?php elseif($hasFeedback): ?>
                                <span style="color:#10b981; font-weight: 700;"><i class="fas fa-check-circle"></i> Rated</span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">Session Ongoing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="feedbackModal" class="modal">
    <div class="modal-content">
        <h3>Rate Your Session</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">How was your experience in the lab?</p>
        <form action="submit_feedback.php" method="POST">
            <input type="hidden" name="reservation_id" id="modal_reservation_id">
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" class="fas fa-star"></label>
                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" class="fas fa-star"></label>
                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" class="fas fa-star"></label>
                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" class="fas fa-star"></label>
                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" class="fas fa-star"></label>
            </div>
            <textarea name="comment" class="feedback-textarea" placeholder="Any comments about the equipment or environment? (Optional)"></textarea>
            <button type="submit" class="btn-submit">Submit Review</button>
            <button type="button" onclick="closeModal('feedbackModal')" style="margin-top:10px; background:none; border:none; color:var(--text-muted); cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

<div id="successModal" class="modal" <?php if($showSuccess) echo 'style="display:flex;"'; ?>>
    <div class="modal-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <h2 style="color: var(--ccs-purple);">Thank You!</h2>
        <p style="color: var(--text-muted); margin: 10px 0 25px 0;">Your feedback helps us improve the CCS laboratories.</p>
        <button onclick="closeModal('successModal')" class="btn-submit">Done</button>
    </div>
</div>

<script>
    function openFeedbackModal(id) {
        document.getElementById('modal_reservation_id').value = id;
        document.getElementById('feedbackModal').style.display = 'flex';
    }

    function closeModal(id) { 
        document.getElementById(id).style.display = 'none'; 
        if(id === 'successModal') {
            window.history.replaceState({}, document.title, "history.php");
        }
    }

    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
</script>
</body>
</html>
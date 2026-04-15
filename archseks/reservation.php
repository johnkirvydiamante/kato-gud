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

if (isset($_POST['reserve'])) {
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $lab = mysqli_real_escape_string($conn, $_POST['lab']);
    $time_in = $_POST['time_in'];
    $date = $_POST['date'];

    if ($user['session_count'] > 0) {
        mysqli_query($conn, "UPDATE users SET session_count = session_count - 1 WHERE id_number = '$uid'");
        mysqli_query($conn, "INSERT INTO reservations (student_id, purpose, lab, time_in, date) VALUES ('$uid', '$purpose', '$lab', '$time_in', '$date')");
        echo "<script>alert('Reservation Successful!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('No sessions remaining! Please contact the laboratory coordinator.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-gold: #ffcc00;
            --ccs-gold-light: #fff0b5;
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
        .reservation-container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .reservation-card {
            background: white;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 28px 32px;
            text-align: center;
        }
        .card-header h2 { font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .card-header h2 i { color: var(--ccs-gold); }
        .card-header p { font-size: 0.9rem; opacity: 0.9; margin-top: 8px; }
        .card-body { padding: 32px; }
        .sessions-banner {
            background: linear-gradient(135deg, var(--ccs-gold-light) 0%, #fff8e7 100%);
            border-radius: 20px; padding: 20px; margin-bottom: 28px;
            text-align: center; border: 1px solid rgba(255,204,0,0.3);
        }
        .sessions-count { font-size: 2.5rem; font-weight: 800; color: var(--ccs-purple); display: inline-block; margin-right: 8px; }
        .sessions-label { font-size: 1rem; color: var(--text-muted); }
        .sessions-icon { font-size: 2rem; color: var(--ccs-gold); margin-right: 12px; vertical-align: middle; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        .form-group label i { color: var(--ccs-purple); margin-right: 6px; }
        .input-with-icon { position: relative; }
        .input-with-icon i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); color: #9ca3af;
            font-size: 0.9rem;
        }
        .input-with-icon input {
            width: 100%; padding: 12px 16px 12px 42px;
            border: 1.5px solid #e2e8f0; border-radius: 14px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }
        .input-with-icon input:focus {
            outline: none; border-color: var(--ccs-purple);
            box-shadow: 0 0 0 4px rgba(75,44,130,0.08);
        }
        .input-with-icon input:focus + i { color: var(--ccs-purple); }
        .input-with-icon input[readonly] { background: #f8fafc; border-color: #e2e8f0; color: var(--text-muted); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-reserve {
            width: 100%; padding: 14px;
            background: var(--ccs-purple); color: white;
            border: none; border-radius: 40px;
            font-weight: 700; font-size: 1rem;
            cursor: pointer; display: flex;
            align-items: center; justify-content: center;
            gap: 10px; margin-top: 8px;
            transition: all 0.25s ease;
        }
        .btn-reserve:hover { background: #3a1f64; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(75,44,130,0.25); }
        .btn-reserve:hover i { transform: translateX(3px); }
        .btn-reserve:disabled { background: #9ca3af; cursor: not-allowed; transform: none; }
        .warning-text {
            font-size: 0.75rem; color: var(--text-muted);
            margin-top: 12px; text-align: center;
        }
        .warning-text i { color: var(--ccs-gold); margin-right: 4px; }
        @media (max-width: 768px) {
            .header-container { flex-direction: column; height: auto; padding: 12px 20px; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 16px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .card-header h2 { font-size: 1.4rem; }
            .card-body { padding: 24px; }
        }
        @media (max-width: 480px) {
            .uc-logo { width: 40px; height: 40px; }
            .sessions-count { font-size: 2rem; }
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .reservation-card { animation: fadeSlideUp 0.5s ease-out; }
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
            <a href="edit_profile.php">Edit Profile</a>
            <a href="history.php">History</a>
            <a href="reservation.php" class="active">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="reservation-container">
    <div class="reservation-card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-check"></i> Laboratory Reservation</h2>
        </div>
        <div class="card-body">
            <div class="sessions-banner">
                <i class="fas fa-clock sessions-icon"></i>
                <span class="sessions-count"><?php echo $user['session_count']; ?></span>
                <span class="sessions-label">Sessions Remaining</span>
                <div style="font-size: 0.75rem; margin-top: 8px; color: var(--text-muted);">
                </div>
            </div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> ID Number</label>
                        <div class="input-with-icon">
                            <i class="fas fa-qrcode"></i>
                            <input type="text" value="<?php echo htmlspecialchars($uid); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Student Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-tasks"></i> Purpose of Sit-in</label>
                    <div class="input-with-icon">
                        <i class="fas fa-pen"></i>
                        <input type="text" name="purpose" placeholder="" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-flask"></i> Laboratory</label>
                    <div class="input-with-icon">
                        <i class="fas fa-laptop"></i>
                        <input type="text" name="lab" placeholder="" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Time In</label>
                        <div class="input-with-icon">
                            <i class="fas fa-hourglass-start"></i>
                            <input type="time" name="time_in" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Sessions Status</label>
                    <div class="input-with-icon">
                        <i class="fas fa-chart-simple"></i>
                        <input type="text" value="<?php echo $user['session_count'] . ' session(s) remaining'; ?>" readonly>
                    </div>
                </div>
                
                <button type="submit" name="reserve" class="btn-reserve" <?php echo ($user['session_count'] <= 0) ? 'disabled' : ''; ?>>
                    <i class="fas fa-check-circle"></i>
                    <?php echo ($user['session_count'] > 0) ? 'Confirm Reservation' : 'No Sessions Remaining'; ?>
                </button>
                
                <div class="warning-text">
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
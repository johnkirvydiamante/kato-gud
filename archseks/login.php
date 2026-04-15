<?php
session_start();
include('config.php');

$login_success = false;
$target_location = "";

if (isset($_POST['login'])) {
    $id_number = mysqli_real_escape_string($conn, trim($_POST['id_number']));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE id_number = '$id_number'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // EMERGENCY BYPASS: Checks for Hash OR the direct word '1234'
        if (password_verify($password, $row['password']) || $password === '1234') {
            
            $_SESSION['user_id'] = $row['id_number'];
            $_SESSION['role'] = $row['role']; 

            // Trigger modal flag instead of immediate header redirect
            $login_success = true;
            $target_location = ($row['role'] === 'admin') ? "admin_dashboard.php" : "dashboard.php";
            
        } else { 
            echo "<script>alert('Invalid Password');</script>"; 
        }
    } else { 
        echo "<script>alert('User not found');</script>"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CCS Sit-in Monitoring System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* =============================================
            ROOT VARIABLES - CCS DEPARTMENT COLORS (Purple & Gold)
           ============================================= */
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-purple-light: #6b4c9e;
            --ccs-gold: #ffcc00;
            --ccs-gold-dark: #e6b800;
            --ccs-gold-light: #fff0b5;
            --bg-gray: #f4f7f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.02);
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f5 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* =============================================
            PROFESSIONAL HEADER WITH UC LOGO
           ============================================= */
        .professional-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(75, 44, 130, 0.1);
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

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .uc-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .system-title {
            display: flex;
            flex-direction: column;
        }

        .system-title h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ccs-purple);
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .system-title h1 span {
            font-weight: 400;
            color: var(--text-muted);
        }

        .system-title .college-name {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            position: relative;
            padding: 4px 0;
        }

        .nav-links a:hover { color: var(--ccs-purple); }

        .nav-links a.active {
            color: var(--ccs-purple);
            font-weight: 600;
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--ccs-gold);
            border-radius: 2px;
        }

        .auth-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-login-nav {
            padding: 8px 24px;
            background: transparent;
            border: 1.5px solid var(--ccs-purple);
            color: var(--ccs-purple);
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-login-nav:hover {
            background: var(--ccs-purple);
            color: white;
        }

        .btn-register-nav {
            padding: 8px 24px;
            background: var(--ccs-gold);
            border: none;
            color: var(--ccs-purple-dark);
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        /* =============================================
            LOGIN CONTAINER & PANELS
           ============================================= */
        .login-container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin-top: 100px;
            animation: fadeSlideUp 0.5s ease-out;
        }

        .hero-panel {
            flex: 1.1;
            background: linear-gradient(145deg, var(--ccs-purple-dark) 0%, var(--ccs-purple) 50%, #2a1945 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .logo-image-container {
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,0.95);
            border-radius: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 0 4px rgba(255,204,0,0.4);
            padding: 25px;
        }

        .logo-image-container img { width: 100%; height: auto; object-fit: contain; }

        .form-panel {
            flex: 0.9;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .form-header { margin-bottom: 36px; text-align: center; }
        .form-header h2 { font-size: 2rem; font-weight: 700; color: var(--ccs-purple); margin-bottom: 8px; }
        .form-header p { color: var(--text-muted); font-size: 0.9rem; }

        .input-group { margin-bottom: 24px; }
        .input-group label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }

        .input-with-icon { position: relative; }
        .input-with-icon i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1rem; }
        .input-with-icon input { width: 100%; padding: 14px 16px 14px 46px; border: 1.5px solid #e2e8f0; border-radius: 16px; font-size: 0.95rem; }
        .input-with-icon input:focus { outline: none; border-color: var(--ccs-purple); box-shadow: 0 0 0 4px rgba(75, 44, 130, 0.08); }

        .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 0.85rem; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-muted); }
        .forgot-link { color: var(--ccs-purple); text-decoration: none; font-weight: 500; }

        .btn-login { width: 100%; padding: 14px; background: var(--ccs-purple); color: white; border: none; border-radius: 40px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.25s ease; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-login:hover { background: #3a1f64; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(75, 44, 130, 0.25); }

        .register-link { text-align: center; font-size: 0.85rem; color: var(--text-muted); border-top: 1px solid #eef2f6; padding-top: 24px; margin-top: 8px; }
        .register-link a { color: var(--ccs-purple); text-decoration: none; font-weight: 700; }

        /* =============================================
            MODAL STYLES
           ============================================= */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center; justify-content: center;
        }

        .login-modal {
            background: white; padding: 40px; border-radius: 30px; text-align: center;
            box-shadow: var(--shadow-lg); max-width: 400px; width: 90%;
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.active { display: flex; }
        .modal-overlay.active .login-modal { transform: scale(1); }

        .modal-icon {
            width: 80px; height: 80px; background: #ecfdf5; color: #10b981;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; margin: 0 auto 20px;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-container { flex-direction: column; margin-top: 90px; }
            .hero-panel { min-height: 320px; }
            .nav-links { display: none; }
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
                <a href="login.php" class="active">Home</a>
                <a href="#">Community</a>
                <a href="#">About</a>
            </div>
            
            <div class="auth-buttons">
                <a href="login.php" class="btn-login-nav">Login</a>
                <a href="register.php" class="btn-register-nav">Register</a>
            </div>
        </div>
    </header>

    <div class="login-container">
        <div class="hero-panel">
            <div class="logo-image-container">
                <img src="logo.png" alt="CCS College Logo">
            </div>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Welcome back</h2>
                <p>Sign in to access your dashboard</p>
            </div>

            <form method="POST" action="">
                <div class="input-group">
                    <label for="id_number"><i class="fas fa-id-card" style="margin-right: 6px;"></i> ID Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user-graduate"></i>
                        <input type="text" id="id_number" name="id_number" placeholder="Enter your ID number" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password"><i class="fas fa-lock" style="margin-right: 6px;"></i> Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">
                    <span>Login to Dashboard</span> <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="register-link">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="successModal">
        <div class="login-modal">
            <div class="modal-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="color: var(--ccs-purple); margin-bottom: 10px;">Login Successful!</h2>
            <p style="color: var(--text-muted);">Welcome back! We are redirecting you to your dashboard now.</p>
            <div style="margin-top: 25px;">
                <i class="fas fa-circle-notch fa-spin" style="color: var(--ccs-gold); font-size: 1.5rem;"></i>
            </div>
        </div>
    </div>

    <script>
        // Trigger modal if PHP login was successful
        <?php if ($login_success): ?>
            document.getElementById('successModal').classList.add('active');
            setTimeout(function() {
                window.location.href = "<?php echo $target_location; ?>";
            }, 2000); 
        <?php endif; ?>
    </script>
</body>
</html>
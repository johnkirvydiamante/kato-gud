<?php
include('config.php');

if (isset($_POST['register'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_number']);
    $first = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $level = mysqli_real_escape_string($conn, $_POST['course_level']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if ID number already exists
    $check_sql = "SELECT * FROM users WHERE id_number = '$id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('ID Number already exists! Please use a different ID.');</script>";
    } else {
        $sql = "INSERT INTO users (id_number, first_name, middle_name, last_name, email, address, course, course_level, password, session_count) 
                VALUES ('$id', '$first', '$middle', '$last', '$email', '$address', '$course', '$level', '$pass', 30)";

        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?msg=success");
            exit();
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-purple-light: #6b4c9e;
            --ccs-gold: #ffcc00;
            --ccs-gold-dark: #e6b800;
            --ccs-gold-light: #fff0b5;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.02);
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            border-bottom: 1px solid rgba(75,44,130,0.1);
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

        .nav-links a:hover {
            color: var(--ccs-purple);
        }

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
        }

        .btn-login-nav {
            padding: 8px 24px;
            background: transparent;
            border: 1.5px solid var(--ccs-purple);
            color: var(--ccs-purple);
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
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
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-register-nav:hover {
            background: var(--ccs-gold-dark);
            transform: translateY(-1px);
        }

        /* =============================================
           REGISTER CONTAINER
           ============================================= */
        .register-container {
            display: flex;
            max-width: 1300px;
            width: 100%;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin-top: 100px;
        }

        /* LEFT PANEL - Purple Gradient */
        .hero-panel {
            flex: 1;
            background: linear-gradient(145deg, var(--ccs-purple-dark) 0%, var(--ccs-purple) 50%, #2a1945 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-panel::before {
            content: "";
            position: absolute;
            top: -30%;
            right: -20%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 204, 0, 0.12) 0%, rgba(255, 204, 0, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-panel::after {
            content: "";
            position: absolute;
            bottom: -20%;
            left: -15%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 204, 0, 0.08) 0%, rgba(255, 204, 0, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .logo-image-container {
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.95);
            border-radius: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 0 4px rgba(255,204,0,0.4);
            padding: 22px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .logo-image-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .hero-text {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .hero-text h2 {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .hero-text h2 span {
            color: var(--ccs-gold);
        }

        .hero-text p {
            color: rgba(255,255,255,0.85);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* RIGHT PANEL - Registration Form */
        .form-panel {
            flex: 1.2;
            padding: 48px 44px;
            background: white;
            overflow-y: auto;
            max-height: 80vh;
        }

        .form-header {
            margin-bottom: 32px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--ccs-purple);
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .input-with-icon input,
        .input-with-icon select,
        .input-with-icon textarea {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: #fefefe;
        }

        .input-with-icon textarea {
            padding: 12px 16px 12px 42px;
            resize: vertical;
            min-height: 80px;
        }

        .input-with-icon select {
            appearance: none;
            cursor: pointer;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%2364748b" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .input-with-icon input:focus,
        .input-with-icon select:focus,
        .input-with-icon textarea:focus {
            outline: none;
            border-color: var(--ccs-purple);
            box-shadow: 0 0 0 4px rgba(75, 44, 130, 0.08);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--ccs-purple);
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 24px;
            margin-bottom: 20px;
        }

        .btn-register:hover {
            background: #3a1f64;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(75, 44, 130, 0.25);
        }

        .login-link {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid #eef2f6;
            padding-top: 24px;
        }

        .login-link a {
            color: var(--ccs-purple);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: var(--ccs-gold-dark);
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .register-container {
                flex-direction: column;
                max-width: 600px;
                margin-top: 90px;
            }
            .hero-panel {
                padding: 40px 28px;
            }
            .logo-image-container {
                width: 150px;
                height: 150px;
                padding: 18px;
            }
            .hero-text h2 {
                font-size: 1.4rem;
            }
            .form-panel {
                padding: 32px 28px;
                max-height: none;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .full-width {
                grid-column: span 1;
            }
            .header-container {
                padding: 0 20px;
                height: 70px;
            }
            .nav-links {
                gap: 20px;
            }
            .nav-links a {
                font-size: 0.8rem;
            }
            .auth-buttons {
                gap: 8px;
            }
            .btn-login-nav, .btn-register-nav {
                padding: 6px 16px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .system-title h1 {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            .form-panel {
                padding: 24px 20px;
            }
            .form-header h2 {
                font-size: 1.5rem;
            }
            .header-container {
                padding: 0 16px;
            }
            .btn-login-nav, .btn-register-nav {
                padding: 5px 12px;
                font-size: 0.7rem;
            }
            .logo-image-container {
                width: 120px;
                height: 120px;
                padding: 15px;
            }
            .uc-logo {
                width: 40px;
                height: 40px;
            }
        }

        .form-panel::-webkit-scrollbar {
            width: 6px;
        }
        .form-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .form-panel::-webkit-scrollbar-thumb {
            background: var(--ccs-purple-light);
            border-radius: 10px;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .register-container {
            animation: fadeSlideUp 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- PROFESSIONAL HEADER WITH UC LOGO ONLY -->
    <header class="professional-header">
        <div class="header-container">
            <!-- Left: UC Logo and System Title -->
            <div class="logo-area">
                <img src="uc.logo.png" alt="University of Cebu" class="uc-logo">
                <div class="system-title">
                    <h1>College of Computer Studies <span>| Sit-in Monitoring System</span></h1>
                </div>
            </div>
            
            <!-- Center: Navigation Links -->
            <div class="nav-links">
                <a href="login.php">Home</a>
                <a href="#">Community</a>
                <a href="#">About</a>
            </div>
            
            <!-- Right: Login & Register Buttons -->
            <div class="auth-buttons">
                <a href="login.php" class="btn-login-nav">Login</a>
                <a href="register.php" class="btn-register-nav" style="background: var(--ccs-purple); color: white;">Register</a>
            </div>
        </div>
    </header>

    <!-- MAIN REGISTER CONTAINER - KEEPING ORIGINAL CCS LOGO -->
    <div class="register-container">
        <!-- LEFT PANEL: CCS Logo (Unchanged) -->
        <div class="hero-panel">
            <div class="logo-image-container">
                <img src="logo.png" alt="CCS College Logo">
            </div>
            <div class="hero-text">
                <h2>Join the <span>CCS</span> Community</h2>
                <p>Create your account to access the Sit-in Monitoring System</p>
            </div>
        </div>

        <!-- RIGHT PANEL: Registration Form -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Fill in your details to get started</p>
            </div>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-id-card"></i> ID Number</label>
                        <div class="input-with-icon">
                            <i class="fas fa-qrcode"></i>
                            <input type="text" name="id_number" placeholder="Enter your ID number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="first_name" placeholder="First name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="middle_name" placeholder="Middle name (optional)">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Last Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="last_name" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Home Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-home"></i>
                            <textarea name="address" placeholder="Enter your complete address"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
    <label>Course</label>
    <div class="input-with-icon">
        <i class="fas fa-graduation-cap"></i>
        <select name="course" required>
            <option value="" disabled selected>Select your course</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Computer Engineering">Computer Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Electrical Engineering">Electrical Engineering</option>
            <option value="Industrial Engineering">Industrial Engineering</option>
            <option value="Naval Architecture and Marine Engineering">Naval Architecture and Marine Engineering</option>
            <option value="Elementary Education (BEEd)">Elementary Education (BEEd)</option>
            <option value="Secondary Education (BSEd)">Secondary Education (BSEd)</option>
            <option value="Criminology">Criminology</option>
            <option value="Commerce">Commerce</option>
            <option value="Accountancy">Accountancy</option>
            <option value="Hotel and Restaurant Management">Hotel and Restaurant Management</option>
            <option value="Customs Administration">Customs Administration</option>
            <option value="Computer Secretarial">Computer Secretarial</option>
            <option value="Industrial Psychology">Industrial Psychology</option>
            <option value="AB Political Science">AB Political Science</option>
            <option value="AB English">AB English</option>
            <option value="CISCO Networking Academy (Module 1 - 4)">CISCO Networking Academy (Module 1 - 4)</option>
            <option value="English Communication Skills">English Communication Skills</option>
            <option value="Conversational Korean">Conversational Korean</option>
        </select>
    </div>
</div>

                    <div class="form-group">
                        <label>Year Level</label>
                        <div class="input-with-icon">
                            <i class="fas fa-layer-group"></i>
                            <select name="course_level" required>
                                <option value="" disabled selected>Select year level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Create a strong password" required>
                        </div>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-register">
                    <i class="fas fa-check-circle"></i> Create Account
                </button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
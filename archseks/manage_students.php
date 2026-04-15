<?php
session_start();
include('config.php');

// 1. Admin Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Delete Student Logic
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id_number = '$id'");
    header("Location: manage_students.php");
    exit();
}

// 3. Reset All Sessions Logic
if (isset($_POST['reset_all'])) {
    mysqli_query($conn, "UPDATE users SET session_count = 30 WHERE role = 'student'");
    echo "<script>alert('All sessions have been reset to 30.'); window.location.href='manage_students.php';</script>";
}

// 4. Fetch Students from Database
$query = "SELECT * FROM users WHERE role = 'student' ORDER BY last_name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Information | CCS Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08);
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
        }

        /* =============================================
           PROFESSIONAL HEADER WITH UC LOGO
           ============================================= */
        .professional-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(75, 44, 130, 0.1);
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
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            position: relative;
            padding: 4px 0;
            cursor: pointer;
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

        .btn-logout {
            background: var(--ccs-gold);
            color: var(--ccs-purple-dark) !important;
            padding: 8px 20px !important;
            border-radius: 40px;
            font-weight: 700 !important;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: var(--ccs-gold-dark);
            transform: translateY(-1px);
        }

        /* =============================================
           MANAGE STUDENTS MAIN CONTAINER
           ============================================= */
        .container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 40px;
            animation: fadeSlideUp 0.5s ease-out;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--ccs-purple);
            margin-bottom: 8px;
        }

        .page-header h1 i {
            color: var(--ccs-gold);
            margin-right: 12px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(75, 44, 130, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-add {
            background: var(--ccs-purple);
            color: white;
        }

        .btn-add:hover {
            background: #3a1f64;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(75, 44, 130, 0.2);
        }

        .btn-reset {
            background: #dc2626;
            color: white;
        }

        .btn-reset:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .stats-badge {
            background: var(--ccs-gold-light);
            padding: 8px 18px;
            border-radius: 40px;
            font-size: 0.85rem;
            color: var(--ccs-purple-dark);
            font-weight: 500;
        }

        .stats-badge i {
            margin-right: 6px;
            color: var(--ccs-gold-dark);
        }

        .stats-badge strong {
            font-weight: 800;
            color: var(--ccs-purple);
        }

        /* Table Wrapper */
        .table-wrapper {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--shadow-md);
            overflow-x: auto;
        }

        /* DataTable Customization */
        .dataTables_wrapper {
            font-family: 'Inter', sans-serif;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 10px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 40px;
            font-family: 'Inter', sans-serif;
            margin-left: 8px;
            width: 250px;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: var(--ccs-purple);
            box-shadow: 0 0 0 3px rgba(75, 44, 130, 0.1);
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 8px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            margin: 0 6px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 14px;
            border-radius: 40px;
            margin: 0 2px;
            border: 1px solid #e2e8f0;
            background: white;
            color: var(--text-muted);
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--ccs-purple);
            color: white;
            border-color: var(--ccs-purple);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--ccs-gold-light);
            border-color: var(--ccs-purple);
            color: var(--ccs-purple);
        }

        /* Table Styles */
        #studentTable {
            width: 100%;
            border-collapse: collapse;
        }

        #studentTable thead th {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 14px 16px;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: left;
        }

        #studentTable tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.85rem;
            color: var(--text-dark);
        }

        #studentTable tbody tr:hover {
            background: var(--ccs-gold-light);
        }

        .id-number {
            font-weight: 700;
            color: var(--ccs-purple);
        }

        .student-name {
            font-weight: 500;
        }

        .course-name {
            color: var(--text-muted);
        }

        /* Year Badge */
        .year-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
            border: 1px solid #e2e8f0;
        }

        .na-text {
            color: #cbd5e1;
            font-style: italic;
        }

        /* Session Badge */
        .session-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
            min-width: 50px;
        }

        .session-badge.high {
            background: #10b981;
            color: white;
        }

        .session-badge.medium {
            background: #f59e0b;
            color: white;
        }

        .session-badge.low {
            background: #ef4444;
            color: white;
        }

        /* Action Buttons in Table */
        .action-buttons-cell {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-edit {
            background: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 0 20px;
            }
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                justify-content: center;
            }
            .stats-badge {
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                height: auto;
                padding: 12px 20px;
            }
            .nav-links {
                justify-content: center;
                margin-top: 12px;
            }
            .logo-area {
                justify-content: center;
            }
            .system-title h1 {
                font-size: 0.9rem;
                text-align: center;
            }
            .page-header h1 {
                font-size: 1.4rem;
            }
            .table-wrapper {
                padding: 12px;
            }
            #studentTable thead th,
            #studentTable tbody td {
                padding: 10px 12px;
                font-size: 0.75rem;
            }
            .btn-icon {
                padding: 6px 10px;
            }
            .session-badge {
                padding: 4px 10px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .uc-logo {
                width: 40px;
                height: 40px;
            }
            .btn {
                padding: 8px 16px;
                font-size: 0.75rem;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-add, .btn-reset {
                width: 100%;
                justify-content: center;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
            }
        }

        /* Animations */
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
    </style>
</head>
<body>

<div class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" alt="University of Cebu" class="uc-logo">
            <div class="system-title">
                <h1 style="font-size: 1.1rem; color: var(--ccs-purple);">College of Computer Studies <span style="font-weight: 400; color: var(--text-muted);">| Admin</span></h1>
               
            </div>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Home</a>
            <a onclick="toggleSearch()">Search</a>
            <a href="manage_students.php" class="active">Students</a>
            <a href="sit_in.php">Sit-in</a>
            <a href="view_sitin.php">View Sit-in Records </a>
            <a href="feedback_reports.php">Feedback</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Students Information</h1>
    </div>
    
    <div class="action-bar">
        <div class="action-buttons">
            <a href="add_student.php" class="btn btn-add"><i class="fas fa-plus"></i> Add Student</a>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Reset all student sessions to 30? This action cannot be undone.');">
                <button type="submit" name="reset_all" class="btn btn-reset"><i class="fas fa-sync-alt"></i> Reset All Sessions</button>
            </form>
        </div>
        <div >
        </div>
    </div>

    <div class="table-wrapper">
        <table id="studentTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Full Name</th>
                    <th>Year Level</th>
                    <th>Course</th>
                    <th>Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Reset result pointer since we used it for total count
                if (mysqli_num_rows($result) > 0) {
                    mysqli_data_seek($result, 0);
                }
                while($row = mysqli_fetch_assoc($result)): 
                    $sessions = isset($row['session_count']) ? $row['session_count'] : 30;
                    $sessionClass = ($sessions <= 5) ? 'low' : (($sessions <= 10) ? 'medium' : 'high');
                    $year_level = $row['course_level'] ?? $row['year_level'] ?? '';
                    $clean = trim($year_level);
                    
                    if ($clean == "1st Year" || $clean == "1") {
                        $year_display = "1st Year";
                    } elseif ($clean == "2nd Year" || $clean == "2") {
                        $year_display = "2nd Year";
                    } elseif ($clean == "3rd Year" || $clean == "3") {
                        $year_display = "3rd Year";
                    } elseif ($clean == "4th Year" || $clean == "4") {
                        $year_display = "4th Year";
                    } elseif (!empty($clean)) {
                        $year_display = htmlspecialchars($clean);
                    } else { 
                        $year_display = '<span class="na-text">N/A</span>';
                    }
                ?>
                <tr>
                    <td class="id-number"><?php echo htmlspecialchars($row['id_number']); ?></td>
                    <td class="student-name"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><span class="year-badge"><?php echo $year_display; ?></span></td>
                    <td class="course-name"><?php echo htmlspecialchars($row['course']); ?></td>
                    <td class="sessions-count">
                        <span class="session-badge <?php echo $sessionClass; ?>">
                            <?php echo $sessions; ?>
                        </span>
                    </td>
                    <td class="action-buttons-cell">
                        <a href="edit_student.php?id=<?php echo $row['id_number']; ?>" class="btn-icon btn-edit" title="Edit Student">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="manage_students.php?delete=<?php echo $row['id_number']; ?>" 
                           class="btn-icon btn-delete" 
                           onclick="return confirm('Delete this student permanently? This will remove all their records.');" 
                           title="Delete Student">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#studentTable').DataTable({
            "pageLength": 10,
            "order": [[1, "asc"]],
            "language": {
                "search": "🔍",
                "searchPlaceholder": "Search student by name, ID, or course...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ students",
                "infoEmpty": "Showing 0 to 0 of 0 students",
                "zeroRecords": "No matching students found"
            },
            "columnDefs": [
                { "orderable": false, "targets": 5 }
            ]
        });
    });
</script>
</body>
</html> 
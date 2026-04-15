<?php
session_start();
include('config.php');

// Get search term from the modal input
$search = isset($_GET['search_query']) ? mysqli_real_escape_string($conn, $_GET['search_query']) : '';

// Query matches your 'users' table columns
$query = "SELECT * FROM users 
          WHERE role = 'student' 
          AND (id_number LIKE '%$search%' 
               OR first_name LIKE '%$search%' 
               OR last_name LIKE '%$search%')";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - CCS Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 40px; }
        .results-container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #004a99; margin-bottom: 20px; padding-bottom: 10px; }
        h2 { color: #004a99; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #004a99; color: white; padding: 12px; text-align: left; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; color: #333; font-size: 14px; }
        tr:hover { background: #f1f7ff; }
        .btn-view { background: #ffc107; color: black; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .back-btn { color: #004a99; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="results-container">
    <div class="header">
        <h2>Results for: "<?php echo htmlspecialchars($search); ?>"</h2>
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Course & Year</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?php echo $row['id_number']; ?></strong></td>
                        <td><?php echo $row['first_name']; ?></td>
                        <td><?php echo $row['last_name']; ?></td>
                        <td><?php echo $row['course'] . " - " . $row['course_level']; ?></td>
                        <td>
                            <a href="view_student_profile.php?id=<?php echo $row['id_number']; ?>" class="btn-view">View Profile</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #888;">No registered students found matching your search.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
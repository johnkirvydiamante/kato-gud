<?php
include('config.php');

if (isset($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    
    // We search by id_number or name
    $query = "SELECT * FROM users 
              WHERE role = 'student' 
              AND (id_number = '$search' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%') 
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Handling the photo path from your 'profile_photo' column
        $photoName = $row['profile_photo'];
        $photoPath = "uploads/" . $photoName;
        
        // If file doesn't exist, show a placeholder
        if (empty($photoName) || !file_exists($photoPath)) {
            $photoPath = "https://via.placeholder.com/100"; 
        }

        echo "
        <div style='display: flex; gap: 20px; align-items: center; padding: 20px; border: 2px solid #004a99; border-radius: 12px; background: #fff; text-align: left;'>
            <img src='{$photoPath}' style='width: 110px; height: 110px; border-radius: 10px; object-fit: cover; border: 1px solid #ccc;'>
            <div style='color: #333;'>
                <h2 style='margin: 0; color: #004a99;'>" . strtoupper($row['last_name']) . ", " . $row['first_name'] . "</h2>
                <p style='margin: 5px 0; font-size: 15px;'><strong>ID Number:</strong> " . $row['id_number'] . "</p>
                <p style='margin: 5px 0; font-size: 15px;'><strong>Course:</strong> " . $row['course'] . " " . $row['course_level'] . "</p>
                <p style='margin: 5px 0; font-size: 15px;'><strong>Address:</strong> " . $row['address'] . "</p>
                <p style='margin: 5px 0; font-size: 15px;'><strong>Email:</strong> " . $row['email'] . "</p>
            </div>
        </div>";
    } else {
        echo "<div style='padding: 20px; color: #cc0000; font-weight: bold;'>Student not found.</div>";
    }
}
?>
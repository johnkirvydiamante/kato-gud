<?php
include('config.php');
// Removed "LIMIT 1" to get everything
$query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");

if(mysqli_num_rows($query) > 0) {
    while($row = mysqli_fetch_assoc($query)) {
        echo '<div style="padding:12px; background:#f8fafc; border-radius:12px; margin-bottom:10px; border-left:4px solid #4b2c82;">';
        echo '<small style="color:#64748b; font-weight:700;">' . date('M d, Y', strtotime($row['created_at'])) . '</small>';
        echo '<p style="font-size:0.9rem; margin-top:5px; color:#1e293b;">' . htmlspecialchars($row['content']) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p style="text-align:center; color:#888;">No announcements yet.</p>';
}
?>
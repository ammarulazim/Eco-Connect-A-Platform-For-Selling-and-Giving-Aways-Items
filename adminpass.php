<?php
// Set your desired admin credentials here
//$admin_password = 'Admin123'; 
$admin_password = 'Ammarul123';

// Generate the secure PHP password hash
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

echo "<h3>Copy this hash for your SQL query:</h3>";
echo "<code style='background:#f4f4f4; padding:5px 10px; display:inline-block; border:1px solid #ccc;'>" . $hashed_password . "</code>";
?>

//http://localhost/eco_connect/adminpass.php
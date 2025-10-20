<?php
// Just a sample file
// Hello, Copy this to a file named "config.php" and update with your own credentials for XAMPP

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'your_database');

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $conn;
}
?>

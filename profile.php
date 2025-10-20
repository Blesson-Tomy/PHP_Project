<?php
// Start output buffering and session
ob_start();
session_start();

// Redirect to login if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    ob_end_clean();
    header("Location: login.php");
    exit();
}

// Get user details from session
$username = $_SESSION['username'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome to Your Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
    
    <div style="border: 1px solid #ccc; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h2>User Information</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>
    
    <a href="logout.php" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px;">Logout</a>
    
    <?php ob_end_flush(); ?>
</body>
</html>
<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Login">
    </form>

    <?php
    require_once 'config.php';

    $conn = getDBConnection();
    if(!$conn)
    {
        die("Connection failed: ". mysqli_connect_error());
    }
    else
    {
        if($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $username = $_POST["username"];
            $password = $_POST["password"];
           
            // Query without 'id' column since table doesn't have it
            $sql = "SELECT username, email, password FROM users WHERE username = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) == 1)
            {
                // Bind only username, email, and password (no id)
                mysqli_stmt_bind_result($stmt, $db_username, $db_email, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if(password_verify($password, $hashed_password))
                {
                    // Store user details in session (no user_id since table doesn't have it)
                    $_SESSION['username'] = $db_username;
                    $_SESSION['email'] = $db_email;
                    $_SESSION['logged_in'] = true;
                    
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);

                    // Redirect to dashboard
                    ob_end_clean();
                    header("Location: dashboard.php");
                    exit();
                }
                else
                {
                    echo "Invalid password.";
                }
            }
            else
            {
                echo "No user found with that username.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
    
   
    ob_end_flush();
    ?>
</body>
</html>
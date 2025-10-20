<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Now!</title>
</head>
<body>

    <form action="register.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <?php
    require_once 'config.php';

    $conn = getDBConnection();
    
    if($conn)
    {
        
        if($_SERVER["REQUEST_METHOD"] == "POST")
        {
            echo "Form submitted!<br>";
            $username = $_POST["username"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            
            echo "Username: $username, Email: $email<br>";

            $sql = "INSERT INTO users (username, email, password) VALUES (?,?,?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            if(!$stmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
            
            if(!mysqli_stmt_execute($stmt)) {
                die("Execute failed: " . mysqli_stmt_error($stmt));
            }

            if(mysqli_stmt_affected_rows($stmt) > 0)
            {
                echo "User created successfully!";
                mysqli_stmt_close($stmt);
                header("Location: login.php");
                exit();
            }
            else
            {
                echo "Error: No rows affected. " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);

    }
    }
    mysqli_close($conn);
    ?>
</body>
</html>
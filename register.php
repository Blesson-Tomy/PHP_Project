<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Now!</title>
</head>
<body>

    <h1>Register Now!</h1>
    <form action="register_handler.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>
    <?php
    $SERVERNAME="localhost";
    $USERNAME = "BLESS";
    $PASSWORD= " ";
    $DBNAME = "project";

    $conn = sqli_connect($seervername, $username, $password, $dbname);
    if(!$conn)
    {
        die("Connection failed: ". mysqli_connect_error());
    }
    else
    {
        if($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $username = $_POST["username"];
            $email = $_POST["email"];
            $password = $_POST["password"];

            $sql="INSERT INTO users values ($username,$email, $password)";
            if(mysqli_query($conn, $sql))
            {
                echo "User created successfully!";
                header("Location: login.php");
                exit();
            }
            else
            {
                echo "Error: ". $sql . "<br>" . mysqli_error($conn);
            }

    }
    }
    mysqli_close($conn);
</body>
</html>
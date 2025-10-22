<?php
ob_start();
session_start();

require_once 'config.php';

$error = '';

$conn = getDBConnection();
if(!$conn) {
    // fatal DB error — show a generic message to the user
    $error = 'Unable to connect to the database. Please try again later.';
} else {
    if($_SERVER["REQUEST_METHOD"] === 'POST') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $sql = "SELECT username, email, password FROM users WHERE username = ?";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, 's', $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) === 1) {
                    mysqli_stmt_bind_result($stmt, $db_username, $db_email, $hashed_password);
                    mysqli_stmt_fetch($stmt);

                    if(password_verify($password, $hashed_password)) {
                        // Authentication successful
                        $_SESSION['username'] = $db_username;
                        $_SESSION['email'] = $db_email;
                        $_SESSION['logged_in'] = true;

                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);

                        // Redirect to dashboard
                        ob_end_clean();
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $error = 'Invalid username or password.';
                    }
                } else {
                    $error = 'Invalid username or password.';
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }

    mysqli_close($conn);
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrap">
        <main class="card" role="main" aria-labelledby="signin-title">
            <header class="brand">
                <div class="logo">AC</div>
                <div>
                    <h1 id="signin-title">Welcome back to Alumni⚡Connect</h1>
                    <p class="lead">Sign in to access your dashboard</p>
                </div>
            </header>

            <?php if(!empty($error)): ?>
                <div class="alert error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="post" novalidate>
                <div style="margin-bottom:12px">
                    <label for="username">Username</label>
                    <input class="input" type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required autofocus>
                </div>

                <div style="margin-bottom:6px">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required>
                </div>

                <div class="actions">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)"><input type="checkbox" name="remember"> Remember me</label>
                    <button class="btn" type="submit">Sign in</button>
                </div>

                <div class="meta">Don't have an account? <a href="register.php" style="color:var(--blue-700);text-decoration:none">Create account</a></div>
            </form>
        </main>
    </div>
</body>
</html>
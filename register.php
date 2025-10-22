<?php
ob_start();
session_start();

require_once 'config.php';

$error = '';
$success = '';

$conn = getDBConnection();
if(!$conn) {
    $error = 'Unable to connect to the database. Please try again later.';
} else {
    if($_SERVER["REQUEST_METHOD"] === 'POST') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : '';

        if($username === '' || $email === '' || $password === '' || $role === '') {
            $error = 'Please complete all required fields.';
        } else {
            // Prepare insert
            $sql = "INSERT INTO users (username, email, role, password) VALUES (?,?,?,?)";
            if($stmt = mysqli_prepare($conn, $sql)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $role, $hashed_password);

                if(mysqli_stmt_execute($stmt)) {
                    if(mysqli_stmt_affected_rows($stmt) > 0) {
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);

                        // Redirect to login
                        ob_end_clean();
                        header('Location: login.php');
                        exit();
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                } else {
                    $error = 'An error occurred while creating your account.';
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
    <title>Create account — Dashboard</title>
    <link rel="stylesheet" href="stylereg.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrap">
        <main class="card" role="main" aria-labelledby="register-title">
            <header class="brand">
                <div class="logo">AC</div>
                <div>
                    <h1 id="register-title">Create your account</h1>
                    <p class="lead">Start using the dashboard — secure and simple</p>
                </div>
            </header>

            <?php if(!empty($error)): ?>
                <div class="alert error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" novalidate>
                <div class="row">
                    <label for="username">Username</label>
                    <input class="input" type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required autofocus>
                </div>

                <div class="row">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>



                <div class="row">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required>
                </div>

                <div class="row">
                    <label>Role</label>
                    <label>
                        <input type="radio" name="role" value="Student" <?php if(isset($_POST['role']) && $_POST['role'] === 'Student') echo 'checked'; ?> required>
                        Student
                    </label>
                    <label>
                        <input type="radio" name="role" value="Alumni" <?php if(isset($_POST['role']) && $_POST['role'] === 'Alumni') echo 'checked'; ?> required>
                        Alumni
                    </label>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Create account</button>
                </div>

                <div class="terms">By creating an account you agree to our <a href="#" class="link">Terms</a>.</div>
                
                <div class="meta">Already have an account? <a href="login.php" class="link">Sign in</a></div>
            </form>
        </main>
    </div>
</body>
</html>

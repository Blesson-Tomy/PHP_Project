<?php
// Start session and output buffering
ob_start();
session_start();

// Redirect if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    ob_end_clean();
    header("Location: login.php");
    exit();
}

// Include DB connection
require_once 'config.php';
$conn = getDBConnection();

// Get user details from session
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$role_session = isset($_SESSION['role']) ? $_SESSION['role'] : 'Student'; // safe default

// Initialize messages
$success = "";
$error = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])){
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $new_role = $_POST['role'];

    if(!empty($new_email) && !empty($new_role)){
        if(!empty($new_password)){
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET email=?, password=?, role=? WHERE username=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $new_email, $hashed_password, $new_role, $username);
        } else {
            $sql = "UPDATE users SET email=?, role=? WHERE username=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $new_email, $new_role, $username);
        }

        if(mysqli_stmt_execute($stmt)){
            $success = "Profile updated successfully!";
            $_SESSION['email'] = $new_email;
            $_SESSION['role'] = $new_role;
            $email = $new_email;
            $role_session = $new_role;
        } else {
            $error = "Failed to update profile.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Email and role cannot be empty.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - AlumniConnect</title>

<!-- Blue Theme CSS -->
<style>
:root{
    --bg: #f6f9fc;
    --card: #ffffff;
    --blue-400: #2b6cb0;
    --blue-600: #1e4a86;
    --blue-700: #153a66;
    --muted: #6b7280;
    --success: #1e7e34;
    --danger: #d0342c;
    --radius: 12px;
    --shadow: 0 6px 20px rgba(16,24,40,0.08);
}

*{box-sizing:border-box;}
body{margin:0;font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;background: linear-gradient(180deg, #eaf4ff 0%, var(--bg) 100%); color:#0f2b3a;}

.wrap{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;}

.card{
    width:100%; max-width:480px; background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:28px; border:1px solid rgba(15,23,42,0.04);
}

h1{font-size:22px; margin:0 0 10px; color:var(--blue-700);}
h2{font-size:18px; margin:0 0 12px; color:var(--blue-600);}
p{margin:6px 0; color:#334e6b; font-size:14px;}

label{display:block; font-size:13px; font-weight:600; margin:8px 0 4px; color:var(--blue-700);}
input, select{width:100%; padding:10px 12px; border-radius:8px; border:1px solid #e6edf5; font-size:14px; outline:none; margin-bottom:12px; transition:0.2s;}
input:focus, select:focus{border-color: var(--blue-400); box-shadow:0 6px 20px rgba(43,108,176,0.1);}

.btn{
    display:inline-flex; align-items:center; justify-content:center; gap:6px; background: linear-gradient(180deg, var(--blue-400), var(--blue-600)); color:#fff; padding:10px 14px; border-radius:10px; border:0; font-weight:600; cursor:pointer; transition:0.15s;
}
.btn:hover{transform:translateY(-2px); box-shadow:0 10px 25px rgba(11,97,214,0.14);}
.btn.ghost{background:transparent; color: var(--blue-700); border:1px solid rgba(11,97,214,0.12); box-shadow:none;}

.alert{padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:14px;}
.alert.success{background:#ecfdf5; color:var(--success); border:1px solid rgba(30,126,52,0.08);}
.alert.error{background:#fff1f0; color:var(--danger); border:1px solid rgba(208,52,44,0.08);}

@media (max-width:480px){.card{padding:20px;}}
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

        <?php if($success) echo "<div class='alert success'>{$success}</div>"; ?>
        <?php if($error) echo "<div class='alert error'>{$error}</div>"; ?>

        <h2>User Information</h2>
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($username); ?>" disabled>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label>Password <small>(Leave blank to keep current)</small></label>
            <input type="password" name="password" placeholder="New password">

            <label>Role</label>
            <select name="role" required>
                <option value="Alumni" <?php if($role_session=='Alumni') echo 'selected'; ?>>Alumni</option>
                <option value="Student" <?php if($role_session=='Student') echo 'selected'; ?>>Student</option>
            </select>

            <button type="submit" name="update_profile" class="btn">Update Profile</button>
        </form>

        <a href="logout.php" class="btn ghost" style="margin-top:12px; display:inline-block;">Logout</a>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>

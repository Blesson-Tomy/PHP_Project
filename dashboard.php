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

// Connect to the database
require_once 'config.php';
$conn = getDBConnection();

// Fetch user role from DB
$sql_role = "SELECT role FROM users WHERE username = ?";
$stmt_role = mysqli_prepare($conn, $sql_role);
mysqli_stmt_bind_param($stmt_role, "s", $username);
mysqli_stmt_execute($stmt_role);
mysqli_stmt_bind_result($stmt_role, $role);
mysqli_stmt_fetch($stmt_role);
mysqli_stmt_close($stmt_role);

// ============================
// 1️⃣ Handle New Post Submission (Alumni only)
// ============================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_post"]) && $role === 'Alumni') {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (!empty($title) && !empty($content)) {
        $sql = "INSERT INTO posts (username, title, content) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $title, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Show success message in JS alert
        echo "<script>alert('Post added successfully!'); window.location.href='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Please fill in all fields before submitting.');</script>";
    }
}

// ============================
// 2️⃣ Handle Delete Post (Alumni only)
// ============================
if (isset($_GET['delete_id']) && $role === 'Alumni') {
    $post_id = $_GET['delete_id'];

    // Only delete if the logged-in user created the post
    $sql = "DELETE FROM posts WHERE id = ? AND username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $post_id, $username);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<script>alert('Post deleted successfully.'); window.location.href='dashboard.php';</script>";
        exit();
    }

    mysqli_stmt_close($stmt);
}

// ============================
// 3️⃣ Fetch All Posts
// ============================
$sql = "SELECT id, username, title, content, created_at FROM posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Shared theme -->
    <link rel="stylesheet" href="styledash.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrap">
        <main class="card" role="main" aria-labelledby="dashboard-title">
            <header class="dashboard-header">
                <div class="dashboard-title">
                    <div class="logo">DB</div>
                    <div>
                        <h1 id="dashboard-title">Your Dashboard</h1>
                        <p class="lead">Overview of your account and posts</p>
                    </div>
                </div>

                <div class="user-block" aria-hidden="false">
                    <div style="font-weight:700"><?php echo htmlspecialchars($username); ?></div>
                    <div style="color:var(--muted);font-size:13px"><?php echo htmlspecialchars($role); ?></div>

                    <div class="header-actions" role="group" aria-label="Account actions">
                        <!-- Profile button (goes to profile.php) -->
                        <a href="profile.php" class="btn secondary" title="Profile">Profile</a>

                        <!-- Logout button -->
                        <a href="logout.php" class="btn ghost" title="Logout">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-grid">
                <!-- Left column: user info and (Alumni) post form -->
                <aside class="panel" aria-labelledby="user-info-heading">
                    <h2 id="user-info-heading" style="margin-top:0;margin-bottom:12px;font-size:16px;">User Information</h2>
                    <div class="userinfo">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>
                    </div>

                    <?php if ($role === 'Alumni'): ?>
                        <hr style="margin:14px 0;border:none;border-top:1px solid #eef6ff;">
                        <h3 style="margin:0 0 10px 0;font-size:15px">Add New Post</h3>
                        <form class="post-form" method="POST" action="">
                            <label for="title">Title</label>
                            <input id="title" type="text" name="title" required>

                            <label for="content">Content</label>
                            <textarea id="content" name="content" rows="5" required></textarea>

                            <div class="form-actions">
                                <button class="btn" type="submit" name="add_post">Add Post</button>
                                <a class="btn secondary" href="profile.php">Profile</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </aside>

                <!-- Right column: posts -->
                <section class="panel" aria-labelledby="posts-heading">
                    <h2 id="posts-heading" style="margin-top:0;margin-bottom:12px;font-size:16px;">All Posts</h2>

                    <div class="posts-list">
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <article class="post-card" aria-labelledby="post-<?php echo (int)$row['id']; ?>">
                                    <h3 id="post-<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

                                    <div class="post-meta">
                                        <div>
                                            <small>Posted by <b><?php echo htmlspecialchars($row['username']); ?></b> on <?php echo htmlspecialchars($row['created_at']); ?></small>
                                        </div>

                                        <div class="post-actions">
                                            <?php if ($role === 'Alumni' && $row['username'] === $username): ?>
                                                <a href="dashboard.php?delete_id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                                <?php
                            }
                        } else {
                            echo '<div class="empty">No posts available yet.</div>';
                        }

                        // Close connection
                        mysqli_close($conn);
                        ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?php ob_end_flush(); ?>
</body>
</html>

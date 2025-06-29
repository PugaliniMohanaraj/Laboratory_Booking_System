<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Query the users table
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Replace with password_verify() if using hashed passwords
        if ($password === $user['password']) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['user_id'];

         // Redirect based on role
if ($user['role'] === 'Instructor') {
    header("Location: dashboard_instructor.php");
    exit();
} elseif ($user['role'] === 'Lab_TO') {
    header("Location: dashboard_labto.php");
    exit();
} elseif ($user['role'] === 'Admin') {
    header("Location: index.php");
    exit();
}elseif ($user['role'] === 'Student') {
    header("Location: student.php");
    exit();
} else {
    // Optional fallback
    $error = "Unknown user role.";
}

            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
 <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <div class="login-box">
    <h2>Login</h2>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username:</label>
        <input type="text" name="username" id="username" class="form-control" required />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" name="password" id="password" class="form-control" required />
      </div>

      <button type="submit" class="btn btn-login">Login</button>
    </form>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

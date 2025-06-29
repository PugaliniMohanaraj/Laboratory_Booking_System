<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Unauthorized Access</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background-color: #121214;
      color: #f8d7da;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .unauth-container {
      background-color: #1f1f1f;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(255, 0, 0, 0.3);
      text-align: center;
    }

    .unauth-container h1 {
      color: #dc3545;
      font-size: 2.5rem;
    }

    .unauth-container p {
      color: #f8d7da;
      margin: 20px 0;
    }

    .btn-home {
      background-color: #0d6efd;
      color: white;
      border: none;
      padding: 10px 20px;
      font-weight: 600;
      border-radius: 6px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .btn-home:hover {
      background-color: #0b5ed7;
    }
  </style>
</head>
<body>
  <div class="unauth-container">
    <h1>Access Denied</h1>
    <p>Sorry <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>, you are not authorized to access this page.</p>
    <a href="index.php" class="btn-home">Back to Dashboard</a>
  </div>
</body>
</html>

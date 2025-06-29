<?php
session_start();
include 'db.php';

// Simulated login info (you may want to replace this with actual login validation)
$valid_username = "admin";
$valid_password = "12345";

// Counts from database
$labCount = $conn->query("SELECT COUNT(*) as count FROM labs")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM lab_bookings WHERE status = 'Pending'")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM lab_bookings WHERE status = 'Approved'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Lab Booking Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap CSS and Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
   <link rel="stylesheet" href="css/index.css">

 
</head>
<body>
  <div class="container bg-container position-relative">
    <a href="logout.php" class="btn btn-outline-danger logout-btn">Logout</a>

    <div class="dashboard-header">
      <h2 style="color: #03DAC5;">
        Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>
      </h2>
      <p class="text-muted">Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'N/A'); ?></p>
      <p id="dateTime"></p>
</div>

    <!-- Navigation Cards -->
    <div class="row text-center g-4 mb-4">
      <div class="col-md-6">
        <div class="card-box">
          <div class="card-icon"><i class="bi bi-pencil-square"></i></div>
          <a href="usage_log.php" class="dashboard-link">Log Lab Usage</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card-box">
          <div class="card-icon"><i class="bi bi-tools"></i></div>
          <a href="log_equipment.php" class="dashboard-link">Log Equipment Usage</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card-box">
          <div class="card-icon"><i class="bi bi-graph-up-arrow"></i></div>
          <a href="view_report.php" class="dashboard-link">View Reports</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card-box">
          <div class="card-icon"><i class="bi bi-calendar-check"></i></div>
          <a href="lab_usage_report.php" class="dashboard-link">Lab Scheduled</a>
        </div>
      </div>
    </div>
  

<!-- Footer -->
<div class="footer mt-5 py-4 px-3 text-center" >
  <p class="mb-1" style="font-size: 0.95rem;">
    &copy; <?php echo date("Y"); ?> Faculty of Engineering, University of Jaffna
  </p>
  <p style="font-size: 0.9rem;">
    Need help? <a href="mailto:2022e098@eng.jfn.ac.lk" style="color: #03DAC5; text-decoration: none;">Contact Support</a>
  </p>
</div>



  <!-- Date & Time Script -->
  <script>
    const dt = new Date();
    document.getElementById("dateTime").innerHTML = dt.toLocaleString();
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

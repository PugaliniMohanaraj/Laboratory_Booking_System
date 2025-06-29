<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lab Usage Report</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

 <link rel="stylesheet" href="css/view_report.css">
</head>
<body>


<!-- ✅ Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background-color: #1e1e23; border-bottom: 1px solid #03DAC5;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-info" href="index.php">
      <i class="bi bi-hdd-network"></i> Lab System
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="register_lab.php"><i class="bi bi-door-open-fill"></i> Register Lab</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="book_lab.php"><i class="bi bi-calendar-check-fill"></i> Book Lab</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="approve_booking.php"><i class="bi bi-shield-check"></i> Approve Booking</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="usage_log.php"><i class="bi bi-pencil-square"></i> Log Usage</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="log_equipment.php"><i class="bi bi-tools"></i> Log Equipment</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="view_report.php"><i class="bi bi-graph-up-arrow"></i> Reports</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="lab_usage_report.php"><i class="bi bi-calendar-event"></i> Schedule</a>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5 pt-4">
  <h2>Lab Usage Reports</h2>
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>Lab ID</th>
        <th>Student ID</th>
        <th>Date</th>
        <th>Activity</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT * FROM usage_logs");
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                  <td>{$row['lab_id']}</td>
                  <td>{$row['student_id']}</td>
                  <td>{$row['date']}</td>
                  <td>{$row['activity']}</td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='4'>No usage records found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

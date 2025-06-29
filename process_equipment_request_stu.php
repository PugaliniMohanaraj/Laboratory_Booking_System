<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = strtolower($_SESSION['role']);
$allowed_roles = ['admin', 'lab_to'];

if (!in_array($role, $allowed_roles)) {
    include 'unauthorized.php';
    exit();
}

$showModal = false;
$modalClass = "";
$modalTitle = "";
$modalBody = "";

// Handle approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $modalBody = "Equipment request $request_id has been <strong>approved</strong>.";
        $modalTitle = "Approved";
        $modalClass = "bg-success text-black";  // green bg, black text
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $modalBody = "Equipment request $request_id has been <strong>rejected</strong>.";
        $modalTitle = "Rejected";
        $modalClass = "bg-danger text-black";  // red bg, black text
    } else {
        $status = null;
        $modalBody = "Invalid action specified.";
        $modalTitle = "Error";
        $modalClass = "bg-danger text-black";
    }

    if ($status !== null) {
        $stmt = $conn->prepare("UPDATE lab_equipment SET status = ? WHERE equipment_id = ?");
        $stmt->bind_param("si", $status, $request_id);
        if (!$stmt->execute()) {
            $modalTitle = "Error";
            $modalClass = "bg-danger text-black";
            $modalBody = "Failed to update request #$request_id.<br>Error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }

    $showModal = true;
}


// Fetch pending requests
$requests = $conn->query("SELECT le.*, s.name AS student_name, l.lab_name 
    FROM lab_equipment le 
    JOIN students s ON le.student_id = s.stu_id 
    JOIN labs l ON le.lab_id = l.lab_id 
    WHERE le.status = 'Pending' 
    ORDER BY le.usage_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Equipment Requests</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
        background-color: #121212;
        color: #f0f0f0;
    }
    .container {
        margin-top: 80px;
    }
    .table {
        background-color: #1e1e1e;
        color: #ffffff;
    }
    .btn-approve {
        background-color: rgb(44, 132, 144);
        color: #fff;
    }
    .btn-reject {
        background-color: rgb(117, 3, 24);
        color: #fff;
        padding-left: 10px;
    }
  </style>
</head>
<body>

<div class="container">
<h2 class="text-center mb-4" style="color: #03DAC5;">Equipment Usage Requests</h2>

  <?php if ($requests && $requests->num_rows > 0): ?>
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Student</th>
          <th>Lab</th>
          <th>Equipment</th>
          <th>Date</th>
          <th>Condition</th>
          <th>Remarks</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $requests->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['equipment_id']) ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['lab_name']) ?></td>
            <td><?= htmlspecialchars($row['equipment_name']) ?></td>
            <td><?= htmlspecialchars($row['usage_date']) ?></td>
            <td><?= htmlspecialchars($row['condition']) ?></td>
            <td><?= htmlspecialchars($row['remarks']) ?></td>
            <td>
              <form method="post" style="display:inline-block;">
                <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['equipment_id']) ?>">
                <button type="submit" name="action" value="approve" class="btn btn-sm btn-approve">Approve</button>
              </form>
              <form method="post" style="display:inline-block;">
                <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['equipment_id']) ?>">
                <button type="submit" name="action" value="reject" class="btn btn-sm btn-reject">Reject</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No pending requests found.</div>
  <?php endif; ?>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
<div class="modal-header <?= $modalClass ?>">
  <h5 class="modal-title" id="statusModalLabel"><?= $modalTitle ?></h5>
  <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body text-black"><?= $modalBody ?></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background-color: #1e1e23; border-bottom: 1px solid #03DAC5;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-info" href="dashboard_labto.php">
      <i class="bi bi-hdd-network"></i> Lab System
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="approve_booking.php"><i class="bi bi-shield-check"></i> Approve Booking</a>
        </li>
         <li class="nav-item">
          <a class="nav-link active" href="process_equipment_request_stu.php"><i class="bi bi-journal-check"></i>Equipment Request</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="view_report.php"><i class="bi bi-graph-up-arrow"></i> Reports</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($showModal): ?>
<script>
  const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
  statusModal.show();
</script>
<?php endif; ?>

</body>
</html>

<?php 
session_start();
include 'db.php'; 

// Access Control: Only Admin or Instructors allowed
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Instructor')) {
    include 'unauthorized.php';
    exit();
}

$showModal = false;
$modalTitle = "";
$modalBody = "";
$modalClass = "";

if (isset($_POST['submit'])) {
    $stmt = $conn->prepare("INSERT INTO lab_bookings (lab_id, ins_id, booking_date, purpose, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iiss", $_POST['lab_id'], $_POST['ins_id'], $_POST['booking_date'], $_POST['purpose']);
    if ($stmt->execute()) {
        $modalTitle = "Booking Successful";
        $modalBody = "Your lab booking request has been submitted successfully.";
        $modalClass = "bg-success text-white";
    } else {
        $modalTitle = "Booking Failed";
        $modalBody = "Error: " . htmlspecialchars($stmt->error);
        $modalClass = "bg-danger text-white";
    }
    $showModal = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book Lab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/book_lab.css" />
</head>
<body>

<!-- ✅ Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background-color: #1e1e23; border-bottom: 1px solid #03DAC5;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-info" href="dashboard_instructor.php">
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
          <a class="nav-link active" href="book_lab.php"><i class="bi bi-calendar-check-fill"></i> Book Lab</a>
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


<!-- ✅ Main Content Wrapper -->
<div class="main-wrapper">
  <div class="container mt-4">
    <h2>Book a Lab</h2>
    <form method="post">
      <div class="mb-4">
        <label for="lab_id">Select Lab</label>
        <select id="lab_id" name="lab_id" class="form-select" required>
          <option value=""> </option>
          <?php
          $labs = $conn->query("SELECT lab_id, lab_name FROM labs WHERE availability = 'Available'");
          while ($lab = $labs->fetch_assoc()) {
              echo '<option value="' . $lab['lab_id'] . '">Lab ' . $lab['lab_id'] . ' - ' . htmlspecialchars($lab['lab_name']) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="mb-4">
        <label for="ins_id">Instructor</label>
        <select id="ins_id" name="ins_id" class="form-select" required>
          <option value=""></option>
          <?php
          $instructors = $conn->query("SELECT ins_id, ins_name FROM instructors");
          while ($ins = $instructors->fetch_assoc()) {
              echo '<option value="' . $ins['ins_id'] . '">' . htmlspecialchars($ins['ins_name']) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="mb-4">
        <label for="booking_date">Booking Date</label>
        <input type="date" id="booking_date" name="booking_date" class="form-control" required />
      </div>

      <div class="mb-4">
        <label for="purpose">Purpose</label>
        <input type="text" id="purpose" name="purpose" class="form-control" required />
      </div>

      <button type="submit" name="submit" class="btn btn-book">Book Lab</button>
    </form>
  </div>
</div>

<!-- ✅ Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header <?= $modalClass ?>">
        <h5 class="modal-title" id="feedbackModalLabel"><?= $modalTitle ?></h5>
        <button type="button" class="btn-close <?= strpos($modalClass, 'text-white') !== false ? 'btn-close-white' : '' ?>" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-dark">
        <?= $modalBody ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($showModal): ?>
<script>
  const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
  feedbackModal.show();
</script>
<?php endif; ?>

</body>
</html>

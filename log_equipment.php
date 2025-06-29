<?php
session_start();
include 'db.php';

// Access Control: Only Admin or Instructors allowed
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Student')) {
    include 'unauthorized.php';
    exit();
}
// Fetch labs
$labs = [];
$labQuery = $conn->query("SELECT lab_id, lab_name FROM labs WHERE availability = 'Available'");
while ($lab = $labQuery->fetch_assoc()) {
    $labs[] = $lab;
}

// Fetch students
$students = [];
$studentQuery = $conn->query("SELECT stu_id, name FROM students ORDER BY name");
while ($student = $studentQuery->fetch_assoc()) {
    $students[] = $student;
}

$showSuccessModal = false;
$error = "";

if (isset($_POST['submit'])) {
    $equipment_name = trim($_POST['equipment_name']);
    $lab_id = $_POST['lab_id'];
    $student_id = $_POST['student_id'];
    $usage_date = $_POST['usage_date'];
    $condition = trim($_POST['condition']);
    $remarks = trim($_POST['remarks']);
    $status = "Pending";

    if (!$equipment_name || !$lab_id || !$student_id || !$usage_date) {
        $error = "All required fields must be filled.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lab_equipment (equipment_name, lab_id, student_id, usage_date, `condition`, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissss", $equipment_name, $lab_id, $student_id, $usage_date, $condition, $remarks, $status);

        if ($stmt->execute()) {
            $showSuccessModal = true;
        } else {
            $error = "Error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipment Usage Logging</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/log_equipment.css">
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
       
        <li class="nav-item"><a class="nav-link" href="approve_booking.php"><i class="bi bi-shield-check"></i> Approve Booking</a></li>
        <li class="nav-item"><a class="nav-link active" href="log_equipment.php"><i class="bi bi-tools"></i> Log Equipment</a></li>
        <li class="nav-item"><a class="nav-link" href="view_report.php"><i class="bi bi-graph-up-arrow"></i> Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="lab_usage_report.php"><i class="bi bi-calendar-event"></i> Schedule</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5 pt-4">
  <h2>Request to Use Lab Equipment</h2>
  <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

  <form method="post" onsubmit="return confirm('Are you sure you want to send this request?');" novalidate>
    <div class="mb-3">
      <label for="equipment_name">Equipment Name *</label>
      <input type="text" id="equipment_name" name="equipment_name" class="form-select" required minlength="2">
    </div>
    <div class="mb-3">
      <label for="lab_id">Select Lab *</label>
      <select id="lab_id" name="lab_id" class="form-select" required>
        <option value="">Select a Lab</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= htmlspecialchars($lab['lab_id']) ?>">
            <?= htmlspecialchars($lab['lab_name']) ?> (ID: <?= $lab['lab_id'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="student_id">Select Student *</label>
      <select id="student_id" name="student_id" class="form-select" required>
        <option value="">Select a Student</option>
        <?php foreach ($students as $student): ?>
          <option value="<?= htmlspecialchars($student['stu_id']) ?>">
            <?= htmlspecialchars($student['name']) ?> (ID: <?= $student['stu_id'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="usage_date">Usage Date *</label>
      <input type="date" id="usage_date" name="usage_date" class="form-select" required>
    </div>
    <div class="mb-3">
      <label for="condition">Condition</label>
      <input type="text" id="condition" name="condition" class="form-select">
    </div>
    <div class="mb-3">
      <label for="remarks">Remarks</label>
      <textarea id="remarks" name="remarks" class="form-select" rows="3"></textarea>
    </div>
    <button type="submit" name="submit" class="btn btn-log">Send Request</button>
    <br><br><br>
  </form>
</div>

<!-- ✅ Success Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-success border-success">
      <div class="modal-header">
        <h5 class="modal-title" id="feedbackModalLabel"><i class="bi bi-check-circle-fill"></i> Request Sent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Equipment usage request submitted successfully.<br>
        Please wait for Lab TO approval.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Show success modal -->
<?php if ($showSuccessModal): ?>
<script>
  const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
  feedbackModal.show();
</script>
<?php endif; ?>

</body>
</html>

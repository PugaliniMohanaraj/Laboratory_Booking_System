<?php
include 'db.php';

// Fetch labs
$labs = [];
$labQuery = "SELECT lab_id, lab_name FROM labs ORDER BY lab_name";
$labResult = $conn->query($labQuery);
while ($row = $labResult->fetch_assoc()) {
    $labs[] = $row;
}

// Fetch students
$students = [];
$studentQuery = "SELECT stu_id, name FROM students ORDER BY name";
$studentResult = $conn->query($studentQuery);
while ($row = $studentResult->fetch_assoc()) {
    $students[] = $row;
}

// Modal variables
$showModal = false;
$modalTitle = '';
$modalBody = '';
$modalClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $lab_id = $_POST['lab_id'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $activity = trim($_POST['activity'] ?? '');

    if (!is_numeric($lab_id) || !is_numeric($student_id) || empty($date) || empty($activity)) {
        $modalTitle = 'Validation Error';
        $modalBody = 'All fields must be valid and filled.';
        $modalClass = 'bg-danger text-white';
        $showModal = true;
    } else {
        $checkDateStmt = $conn->prepare("SELECT COUNT(*) AS count FROM lab_bookings WHERE lab_id = ? AND booking_date = ? AND status = 'Approved'");
        $checkDateStmt->bind_param("is", $lab_id, $date);
        $checkDateStmt->execute();
        $isValidDate = $checkDateStmt->get_result()->fetch_assoc()['count'] > 0;

        if (!$isValidDate) {
            $modalTitle = 'Invalid Booking Date';
            $modalBody = 'Selected date is not an approved booking for this lab.';
            $modalClass = 'bg-danger text-white';
        } else {
            $dupStmt = $conn->prepare("SELECT * FROM usage_logs WHERE lab_id = ? AND student_id = ? AND date = ?");
            $dupStmt->bind_param("iis", $lab_id, $student_id, $date);
            $dupStmt->execute();
            $dupResult = $dupStmt->get_result();

            if ($dupResult->num_rows > 0) {
                $modalTitle = 'Duplicate Entry';
                $modalBody = 'This usage log already exists for this student on the selected date.';
                $modalClass = 'bg-warning text-dark';
            } else {
                $insertStmt = $conn->prepare("INSERT INTO usage_logs (lab_id, student_id, date, activity) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("iiss", $lab_id, $student_id, $date, $activity);

                if ($insertStmt->execute()) {
                    $modalTitle = 'Success';
                    $modalBody = 'Lab usage logged successfully.';
                    $modalClass = 'bg-success text-white';
                } else {
                    $modalTitle = 'Database Error';
                    $modalBody = 'Error: ' . htmlspecialchars($insertStmt->error);
                    $modalClass = 'bg-danger text-white';
                }
            }
        }

        $showModal = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Log Student Usage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  
 <link rel="stylesheet" href="css/usage_log.css">


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
          <a class="nav-link active" href="usage_log.php"><i class="bi bi-pencil-square"></i> Log Usage</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="log_equipment.php"><i class="bi bi-tools"></i> Log Equipment</a>
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


  <div class="container mt-5 pt-4">
    <h2>Log Student Usage</h2>
    <form method="post" novalidate>
      <div class="mb-3">
        <label for="lab_id">Lab</label>
        <select id="lab_id" name="lab_id" class="form-select" required>
          <option value="" disabled selected></option>
          <?php foreach ($labs as $lab): ?>
            <option value="<?= htmlspecialchars($lab['lab_id']) ?>"><?= htmlspecialchars($lab['lab_name']) ?> (ID: <?= $lab['lab_id'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="student_id">Student</label>
        <select id="student_id" name="student_id" class="form-select" required>
          <option value="" disabled selected></option>
          <?php foreach ($students as $student): ?>
            <option value="<?= htmlspecialchars($student['stu_id']) ?>"><?= htmlspecialchars($student['name']) ?> (ID: <?= $student['stu_id'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="date">Date</label>
        <select id="date" name="date" class="form-select" required>
          <option value="" disabled selected></option>
        </select>
      </div>

      <div class="mb-3">
        <label for="activity">Activity</label>
        <input type="text" id="activity" name="activity" class="form-control" required />
      </div>

      <button type="submit" name="submit" class="btn btn-log">Log Usage</button>
    </form>


  <!-- Feedback Modal -->
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <?php if ($showModal): ?>
  <script>
    const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    window.addEventListener('load', () => feedbackModal.show());
  </script>
  <?php endif; ?>

  <script>
    document.getElementById('lab_id').addEventListener('change', function () {
      const labId = this.value;
      const dateSelect = document.getElementById('date');
      dateSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';

      fetch('get_approved_dates.php?lab_id=' + encodeURIComponent(labId))
        .then(res => res.json())
        .then(dates => {
          dateSelect.innerHTML = '<option value="" disabled selected>-- Select Date --</option>';
          dates.forEach(date => {
            const opt = document.createElement('option');
            opt.value = date;
            opt.textContent = date;
            dateSelect.appendChild(opt);
          });
        })
        .catch(() => {
          dateSelect.innerHTML = '<option value="" disabled>Error loading dates</option>';
        });
    });
  </script>
</body>
</html>

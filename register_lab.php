<?php
session_start();
include 'db.php';

// Access Control: Only Admin or Instructors allowed
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Instructor')) {
    include 'unauthorized.php';
    exit();
}

// Modal feedback variables
$showModal = false;
$modalTitle = "";
$modalClass = "";
$modalBody = "";

// Handle form submission
if (isset($_POST['submit'])) {
    if (empty(trim($_POST['lab_name'])) || empty(trim($_POST['lab_code'])) || empty(trim($_POST['location']))) {
        $modalTitle = "Missing Required Fields";
        $modalClass = "bg-danger text-white";
        $missingFields = [];
        if (empty(trim($_POST['lab_name']))) $missingFields[] = "Lab Name";
        if (empty(trim($_POST['lab_code']))) $missingFields[] = "Lab Code";
        if (empty(trim($_POST['location']))) $missingFields[] = "Location";
        $modalBody = "Please fill in the following required fields: <strong>" . implode(', ', $missingFields) . "</strong>.";
        $showModal = true;
    }
    elseif (!is_numeric($_POST['capacity']) && $_POST['capacity'] !== '') {
        $modalTitle = "Invalid Input";
        $modalClass = "bg-danger text-white";
        $modalBody = "Capacity must be a positive number or left empty.";
        $showModal = true;
    } elseif (is_numeric($_POST['capacity']) && $_POST['capacity'] < 0) {
        $modalTitle = "Invalid Input";
        $modalClass = "bg-danger text-white";
        $modalBody = "Capacity must be a positive number.";
        $showModal = true;
    } else {
        $status = !empty($_POST['status']) ? $_POST['status'] : 'Active';

        $stmt = $conn->prepare("INSERT INTO labs 
            (lab_name, lab_code, description, location, lab_type, capacity, availability, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $capacity = $_POST['capacity'] === '' ? null : $_POST['capacity'];

        $stmt->bind_param(
            "sssssiss",
            $_POST['lab_name'],
            $_POST['lab_code'],
            $_POST['description'],
            $_POST['location'],
            $_POST['lab_type'],
            $capacity,
            $_POST['availability'],
            $status
        );

        if ($stmt->execute()) {
            $modalTitle = "Success";
            $modalClass = "bg-success text-white";
            $modalBody = "Lab registered successfully.";
        } else {
            $modalTitle = "Database Error";
            $modalClass = "bg-danger text-white";
            $modalBody = "Error: " . htmlspecialchars($stmt->error);
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
  <title>Register Lab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/register_lab.css">
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
          <a class="nav-link active" href="register_lab.php"><i class="bi bi-door-open-fill"></i> Register Lab</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="book_lab.php"><i class="bi bi-calendar-check-fill"></i> Book Lab</a>
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
  <h2 class="mb-4">Register New Lab</h2>
  <form method="post" novalidate>
    <div class="mb-4">
      <label for="lab_name">Lab Name <span class="text-danger">*</span></label>
      <input type="text" id="lab_name" name="lab_name" class="form-control" required />
    </div>
    <div class="mb-4">
      <label for="lab_code">Lab Code <span class="text-danger">*</span></label>
      <input type="text" id="lab_code" name="lab_code" class="form-control" required />
    </div>
    <div class="mb-4">
      <label for="description">Lab Description</label>
      <textarea id="description" name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-4">
      <label for="location">Location <span class="text-danger">*</span></label>
      <input type="text" id="location" name="location" class="form-control" required />
    </div>
    <div class="mb-4">
      <label for="lab_type">Type</label>
      <select id="lab_type" name="lab_type" class="form-select">
        <option value=""></option>
        <option value="Computer">Computer</option>
        <option value="Mechanical">Mechanical</option>
        <option value="Electrical">Electrical and Electronics</option>
        <option value="Interdisciplinary">Interdisciplinary</option>
      </select>
    </div>
    <div class="mb-4">
      <label for="capacity">Capacity</label>
      <input type="number" id="capacity" name="capacity" class="form-control" min="0" step="1" />
    </div>
    <div class="mb-4">
      <label for="availability">Availability</label>
      <select id="availability" name="availability" class="form-select">
        <option value="Available" selected>Available</option>
        <option value="Unavailable">Unavailable</option>
      </select>
    </div>
    <div class="mb-4">
      <label for="status">Status</label>
      <select id="status" name="status" class="form-select">
        <option value="Active" selected>Active</option>
        <option value="Inactive">Inactive</option>
      </select>
    </div>
    <button type="submit" name="submit" class="btn btn-register btn-primary">Register</button>
  </form>
</div>

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
  window.addEventListener('load', () => {
    feedbackModal.show();
  });
</script>
<?php endif; ?>

<script>
  document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['lab_name', 'lab_code', 'location'];
    let valid = true;
    let missing = [];

    requiredFields.forEach(id => {
      const field = document.getElementById(id);
      if (!field.value.trim()) {
        valid = false;
        missing.push(id.replace('_', ' '));
      }
    });

    if (!valid) {
      e.preventDefault();
      const modalTitle = document.getElementById('feedbackModalLabel');
      const modalBody = document.querySelector('#feedbackModal .modal-body');
      const modalHeader = document.querySelector('#feedbackModal .modal-header');

      modalTitle.textContent = "Missing Required Fields";
      modalBody.innerHTML = "Please fill all required fields: <strong>" + missing.join(', ') + "</strong>.";
      modalHeader.className = "modal-header bg-danger text-white";

      const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
      feedbackModal.show();
    }
  });
</script>
</body>
</html>

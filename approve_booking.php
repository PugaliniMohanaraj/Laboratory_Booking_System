<?php
session_start();
include 'db.php';

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// Fetch pending bookings
$pendingBookings = [];
$query = "SELECT booking_id, lab_id, booking_date, status FROM lab_bookings WHERE status = 'Pending' ORDER BY booking_date ASC";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $pendingBookings[] = $row;
    }
}

// Modal feedback variables
$showModal = false;
$modalTitle = "";
$modalBody = "";
$modalClass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'], $_POST['booking_id'], $_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    $stmt = $conn->prepare("SELECT lab_id, booking_date, ins_id FROM lab_bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $lab_id = $data['lab_id'];
        $booking_date = $data['booking_date'];
        $ins_id = $data['ins_id'];

        if ($action == 'Approved') {
            $conflict = $conn->prepare("SELECT booking_id FROM lab_bookings WHERE lab_id=? AND booking_date=? AND status='Approved' AND booking_id != ?");
            $conflict->bind_param("isi", $lab_id, $booking_date, $booking_id);
            $conflict->execute();
            $conflict_res = $conflict->get_result();
            if ($conflict_res->num_rows > 0) {
                $showModal = true;
                $modalTitle = "Booking Conflict";
                $modalBody = "Lab is already booked on this date.";
                $modalClass = "bg-danger text-white";
            } else {
                $update = $conn->prepare("UPDATE lab_bookings SET status=? WHERE booking_id=?");
                $update->bind_param("si", $action, $booking_id);
                if ($update->execute()) {
                    $showModal = true;
                    $modalTitle = "Booking Approved";
                    $modalBody = "Booking has been approved successfully.";
                    $modalClass = "bg-success text-white";

                    // Send email
                    $email_stmt = $conn->prepare("SELECT ins_email FROM instructors WHERE ins_id = ?");
                    $email_stmt->bind_param("i", $ins_id);
                    $email_stmt->execute();
                    $email_res = $email_stmt->get_result();
                    if ($email_res->num_rows > 0) {
                        $ins_email = $email_res->fetch_assoc()['ins_email'];
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'pugalini.2002@gmail.com';
                            $mail->Password = 'xohx eclz tyji lcqd'; // Use App Password
                            $mail->SMTPSecure = 'tls';
                            $mail->Port = 587;

                            $mail->setFrom('pugalini.2002@gmail.com', 'Lab Management System');
                            $mail->addAddress($ins_email);
                            $mail->isHTML(true);
                            $mail->Subject = 'Lab Booking Approved';
                            $mail->Body = "
                                Dear Instructor,<br><br>
                                Your lab booking for <strong>Lab ID: $lab_id</strong> on <strong>$booking_date</strong> has been approved.<br><br>
                                Regards,<br>Lab Management System
                            ";
                            $mail->send();
                            $modalBody .= "<br><br>📧 Email sent to <strong>$ins_email</strong>.";
                        } catch (Exception $e) {
                            $modalBody .= "<br><br>⚠️ Email failed to send: <code>{$mail->ErrorInfo}</code>";
                            $modalClass = "bg-warning text-dark";
                        }
                    }
                } else {
                    $showModal = true;
                    $modalTitle = "Update Failed";
                    $modalBody = "Failed to update booking.";
                    $modalClass = "bg-danger text-white";
                }
            }
        } else {
            $update = $conn->prepare("UPDATE lab_bookings SET status=? WHERE booking_id=?");
            $update->bind_param("si", $action, $booking_id);
            if ($update->execute()) {
                $showModal = true;
                $modalTitle = "Booking Rejected";
                $modalBody = "Booking has been rejected.";
                $modalClass = "bg-warning";
            }
        }
    } else {
        $showModal = true;
        $modalTitle = "Invalid Booking";
        $modalBody = "Selected booking ID does not exist.";
        $modalClass = "bg-danger text-white";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approve Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <link rel="stylesheet" href="css/approve_booking.css">
</head>
<body style="background-color: #121214; color: #e1e1e1;">

<!-- ✅ Navigation Bar -->
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
          <a class="nav-link active" href="approve_booking.php"><i class="bi bi-shield-check"></i> Approve Booking</a>
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

<div class="container mt-5 p-4 rounded" style="background-color: #1e1e23; max-width: 600px;">
  <h2 class="text-center mb-4" style="color: #03DAC5;">Approve Bookings</h2>
  <form method="post">
    <div class="mb-3">
      <label for="booking_id" class="form-label">Select Booking</label>
      <select name="booking_id" class="form-select" required>
        <option value="" disabled selected>Select Booking</option>
        <?php foreach ($pendingBookings as $booking): ?>
          <option value="<?= htmlspecialchars($booking['booking_id']) ?>">
            Booking <?= htmlspecialchars($booking['booking_id']) ?> | Lab <?= htmlspecialchars($booking['lab_id']) ?> | <?= htmlspecialchars($booking['booking_date']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="action" class="form-label">Action</label>
      <select name="action" class="form-select" required>
        <option value="Approved">Approve</option>
        <option value="Rejected">Reject</option>
      </select>
    </div>

    <button type="submit" name="submit" class="btn btn-info w-100">Update Booking</button>
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
      <div class="modal-body text-dark"><?= $modalBody ?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($showModal): ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
  });
</script>
<?php endif; ?>

</body>
</html>

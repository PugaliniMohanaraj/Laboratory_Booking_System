<?php
session_start();
include 'db.php';

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

$pendingBookings = [];
$query = "SELECT booking_id, lab_id, booking_date FROM lab_bookings WHERE status = 'Pending' ORDER BY booking_date ASC";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $pendingBookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Approve Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
  <h2>Approve/Reject Lab Bookings</h2>
  <form method="post">
    <label>Choose a Booking:</label>
    <select name="booking_id" class="form-select" required>
      <option value="">-- Select --</option>
      <?php foreach ($pendingBookings as $b): ?>
        <option value="<?= $b['booking_id'] ?>">Booking #<?= $b['booking_id'] ?> | Lab <?= $b['lab_id'] ?> | <?= $b['booking_date'] ?></option>
      <?php endforeach; ?>
    </select><br>

    <label>Action:</label>
    <select name="action" class="form-select" required>
      <option value="Approved">Approve</option>
      <option value="Rejected">Reject</option>
    </select><br>

    <button name="submit" class="btn btn-primary">Update Status</button>
  </form>

<?php
if (isset($_POST['submit'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    // Fetch booking info
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
            // Check for conflicts
            $conflict = $conn->prepare("SELECT booking_id FROM lab_bookings WHERE lab_id=? AND booking_date=? AND status='Approved' AND booking_id != ?");
            $conflict->bind_param("isi", $lab_id, $booking_date, $booking_id);
            $conflict->execute();
            $conflict_res = $conflict->get_result();
            if ($conflict_res->num_rows > 0) {
                echo "<div class='alert alert-danger mt-3'>Conflict! Lab already booked on this date.</div>";
                exit();
            }
        }

        // Update booking status
        $update = $conn->prepare("UPDATE lab_bookings SET status=? WHERE booking_id=?");
        $update->bind_param("si", $action, $booking_id);
        if ($update->execute()) {
            echo "<div class='alert alert-success mt-3'>Booking $action successfully.</div>";

            // If approved, send email
            if ($action == 'Approved') {
                $email_stmt = $conn->prepare("SELECT ins_email FROM instructors WHERE ins_id = ?");
                $email_stmt->bind_param("i", $ins_id);
                $email_stmt->execute();
                $email_res = $email_stmt->get_result();
                if ($email_res->num_rows > 0) {
                    $ins_email = $email_res->fetch_assoc()['ins_email'];

                    // Send email using PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'pugalini.2002@gmail.com'; // 🔁 Replace
                        $mail->Password = 'xohx eclz tyji lcqd';    // 🔁 Replace
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('your_email@gmail.com', 'Lab Management System');
                        $mail->addAddress($ins_email);
                        $mail->isHTML(true);
                        $mail->Subject = 'Lab Booking Approved';
                        $mail->Body = "
                            Dear Instructor,<br><br>
                            Your lab booking for <strong>Lab ID: $lab_id</strong> on <strong>$booking_date</strong> has been approved.<br><br>
                            Regards,<br>Lab Management System
                        ";
                        $mail->send();
                        echo "<div class='alert alert-info'>Email sent to instructor: $ins_email</div>";
                    } catch (Exception $e) {
                        echo "<div class='alert alert-warning'>Booking updated, but email failed: {$mail->ErrorInfo}</div>";
                    }
                }
            }
        } else {
            echo "<div class='alert alert-danger mt-3'>Update failed: " . htmlspecialchars($update->error) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger mt-3'>Invalid Booking ID</div>";
    }
}
?>

</div>
</body>
</html>

<?php
include 'db.php';

if (isset($_GET['lab_id']) && is_numeric($_GET['lab_id'])) {
    $lab_id = intval($_GET['lab_id']);
    $stmt = $conn->prepare("SELECT booking_date FROM lab_bookings WHERE lab_id = ? AND status = 'Approved' ORDER BY booking_date ASC");
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['booking_date'];
    }

    header('Content-Type: application/json');
    echo json_encode($dates);
} else {
    echo json_encode([]);
}
?>

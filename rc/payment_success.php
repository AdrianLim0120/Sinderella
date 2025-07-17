<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['status_id']) && $_GET['billcode']) {
    $status = $_GET['status_id']; // 1 = successful
    $booking_id = $_GET['order_id'];

    require_once '../db_connect.php'; //
    if ($status == 1) {
        $conn->query("UPDATE bookings SET booking_status='confirm' WHERE booking_id=$booking_id");
        $conn->query("UPDATE booking_payments SET payment_status='confirmed' WHERE booking_id=$booking_id");
        // echo "<h2>🎉 Payment Successful! Your booking is confirmed.</h2>";
        echo "<script>
            alert('🎉 Payment Successful! Your booking is confirmed.');
            window.location.href = 'my_booking.php';
        </script>";
    } else {
        // echo "<h2>❌ Payment Failed or Cancelled.</h2>";
        echo "<script>
            alert('❌ Payment Failed or Cancelled.');
            window.location.href = 'my_booking.php';
        </script>";
    }
    // $conn->close();
} else {
    echo "<h2>Invalid access.</h2>";
}

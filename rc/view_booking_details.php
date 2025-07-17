<?php
session_start();
if (!isset($_SESSION['cust_id'])) {
    header("Location: ../login_cust.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: my_booking.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Database connection
require_once '../db_connect.php';

// Fetch booking details
$stmt = $conn->prepare("SELECT b.booking_date, b.booking_from_time, b.booking_to_time, b.full_address, s.sind_name, sp.service_name, sp.service_price, b.booking_status, b.service_id, b.sind_id, b.cust_id
                        FROM bookings b
                        JOIN sinderellas s ON b.sind_id = s.sind_id
                        JOIN service_pricing sp ON b.service_id = sp.service_id
                        WHERE b.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$stmt->bind_result($booking_date, $booking_from_time, $booking_to_time, $full_address, $sinderella_name, $service_name, $service_price, $booking_status, $service_id, $sinderella_id, $cust_id);
$stmt->fetch();
$stmt->close();

if (empty($full_address)) {
    $full_address = "N/A";
}

// Fetch add-ons
$addon_details = [];
$total_addon_price = 0;
$total_addon_duration = 0;
$stmt = $conn->prepare("SELECT ao.ao_desc, ao.ao_price, ao.ao_duration
                        FROM booking_addons ba
                        JOIN addon ao ON ba.ao_id = ao.ao_id
                        WHERE ba.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$stmt->bind_result($ao_desc, $ao_price, $ao_duration);
while ($stmt->fetch()) {
    $addon_details[] = ['desc' => $ao_desc, 'price' => $ao_price, 'duration' => $ao_duration];
    $total_addon_price += $ao_price;
    $total_addon_duration += $ao_duration;
}
$stmt->close();

$total_price = $total_addon_price + $service_price;

// $conn->close();

function formatTime($time) {
    $date = new DateTime($time);
    return $date->format('h:i A');
}

function formatDate($date) {
    $date = new DateTime($date);
    return $date->format('Y-m-d (l)');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Customer - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
    <style>
        .details-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .details-container h2 {
            margin-top: 0;
        }
        .details-container label {
            display: block;
            margin-top: 10px;
        }
        .details-container button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .details-container button:hover {
            background-color: #0056b3;
        }
        .details-container td {
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../includes/menu/menu_cust.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_cust.php'; ?>
            <div class="details-container">
                <h2>Booking Details</h2>
                <table>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td>: <?php echo htmlspecialchars(formatDate($booking_date)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Time</strong></td>
                        <td>: <?php echo htmlspecialchars(formatTime($booking_from_time)) . ' - ' . htmlspecialchars(formatTime($booking_to_time)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Address</strong></td>
                        <td>: <?php echo htmlspecialchars($full_address); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Sinderella</strong></td>
                        <td>: <?php echo htmlspecialchars($sinderella_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Service</strong></td>
                        <td>: <?php echo htmlspecialchars($service_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Add-ons</strong></td>
                        <td>
                            <ul>
                            <?php if (empty($addon_details)): ?>
                                <li>N/A</li>
                            <?php else: ?>
                                <?php foreach ($addon_details as $addon): ?>
                                    <li><?php echo htmlspecialchars($addon['desc']); ?> (RM <?php echo number_format($addon['price'], 2); ?>)</li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td>: RM <?php echo number_format($total_price, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>: <?php echo htmlspecialchars($booking_status); ?></td>
                    </tr>
                </table>

                <?php if ($booking_status == 'pending'): ?>
                    <form id="confirmationForm" method="POST" action="confirm_booking.php">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
                        <input type="hidden" name="booking_date" value="<?php echo htmlspecialchars($booking_date); ?>">
                        <input type="hidden" name="sinderella" value="<?php echo htmlspecialchars($sinderella_id); ?>">
                        <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($booking_from_time); ?>">
                        <input type="hidden" name="end_time" value="<?php echo htmlspecialchars($booking_to_time); ?>">
                        <input type="hidden" name="service" value="<?php echo htmlspecialchars($service_id); ?>">
                        <input type="hidden" name="full_address" value="<?php echo htmlspecialchars($full_address); ?>">
                        <?php foreach ($addon_details as $addon): ?>
                            <input type="hidden" name="addons[]" value="<?php echo htmlspecialchars($addon['desc']); ?>">
                        <?php endforeach; ?>
                        <button type="submit">Pay Now</button>
                        <button type="button" onclick="cancelBooking(<?php echo $booking_id; ?>)">Cancel Booking</button><br>
                    </form>
                <?php endif; ?>

                <button type="button" onclick="window.location.href='my_booking.php?search_date=&search_status=<?php echo urlencode($booking_status); ?>'">Back</button>
                <!-- <button type="button" onclick="window.location.href='my_booking.php?search_date=&search_status=' + booking_status">Back</button> -->
            </div>
        </div>
    </div>
    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = 'cancel_booking.php?booking_id=' + bookingId;
            }
        }
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['sind_id'])) {
    header("Location: ../login_sind.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: view_bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Database connection
require_once '../db_connect.php';

// Fetch booking details
$stmt = $conn->prepare("SELECT b.booking_date, b.booking_from_time, b.booking_to_time, c.cust_name, b.full_address, sp.service_name, sp.service_price, b.booking_status
                        FROM bookings b
                        JOIN customers c ON b.cust_id = c.cust_id
                        JOIN service_pricing sp ON b.service_id = sp.service_id
                        WHERE b.booking_id = ? AND b.sind_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['sind_id']);
$stmt->execute();
$stmt->bind_result($booking_date, $booking_from_time, $booking_to_time, $cust_name, $full_address, $service_name, $service_price, $booking_status);
$stmt->fetch();
$stmt->close();

if (empty($full_address)) {
    $full_address = "N/A";
}

// Fetch add-ons
$addon_details = [];
$total_addon_price = 0;
$stmt = $conn->prepare("SELECT ao.ao_desc, ao.ao_price
                        FROM booking_addons ba
                        JOIN addon ao ON ba.ao_id = ao.ao_id
                        WHERE ba.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$stmt->bind_result($ao_desc, $ao_price);
while ($stmt->fetch()) {
    $addon_details[] = ['desc' => $ao_desc, 'price' => $ao_price];
    $total_addon_price += $ao_price;
}
$stmt->close();

$total_price = $service_price + $total_addon_price;

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
    <title>Booking Details - Sinderella - Sinderella</title>
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
        <?php include '../includes/menu/menu_sind.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_sind.php'; ?>
            <div class="details-container">
                <h2>Booking Details</h2>
                <table>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td><?php echo htmlspecialchars(formatDate($booking_date)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Time:</strong></td>
                        <td><?php echo htmlspecialchars(formatTime($booking_from_time)) . ' - ' . htmlspecialchars(formatTime($booking_to_time)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td><?php echo htmlspecialchars($full_address); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Customer:</strong></td>
                        <td><?php echo htmlspecialchars($cust_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Service:</strong></td>
                        <td><?php echo htmlspecialchars($service_name); ?> (RM <?php echo number_format($service_price, 2); ?>)</td>
                    </tr>
                    <tr>
                        <td><strong>Add-ons:</strong></td>
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
                        <td><strong>Total:</strong></td>
                        <td>RM <?php echo number_format($total_price, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><?php echo htmlspecialchars($booking_status); ?></td>
                    </tr>
                </table>
                <button type="button" onclick="window.location.href='view_bookings.php'">Back</button>
            </div>
        </div>
    </div>
</body>
</html>
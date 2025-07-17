<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['cust_id'])) {
    header("Location: ../login_cust.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['agree'])) {
    header("Location: add_booking.php");
    exit();
}

$cust_id = $_SESSION['cust_id'];
$booking_date = $_POST['booking_date'];
$sinderella_id = $_POST['sinderella'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$service_id = $_POST['service'];
$addons = isset($_POST['addons']) ? $_POST['addons'] : [];
$full_address = $_POST['full_address'];

// Database connection
require_once '../db_connect.php';

// Insert booking
// $stmt = $conn->prepare("INSERT INTO bookings (cust_id, sind_id, booking_date, booking_from_time, booking_to_time, service_id, booked_at, booking_status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'pending')");
// $stmt->bind_param("iissss", $cust_id, $sinderella_id, $booking_date, $start_time, $end_time, $service_id);
$stmt = $conn->prepare("INSERT INTO bookings (cust_id, sind_id, booking_date, booking_from_time, booking_to_time, service_id, full_address, booked_at, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')");
$stmt->bind_param("iisssss", $cust_id, $sinderella_id, $booking_date, $start_time, $end_time, $service_id, $full_address);
$stmt->execute();
$booking_id = $stmt->insert_id;
$stmt->close();

// Insert booking add-ons
if (!empty($addons)) {
    $stmt = $conn->prepare("INSERT INTO booking_addons (booking_id, ao_id) VALUES (?, ?)");
    foreach ($addons as $addon_id) {
        $stmt->bind_param("ii", $booking_id, $addon_id);
        $stmt->execute();
    }
    $stmt->close();
}

// === STEP 1: Calculate total ===
$total_amount = 0;

// Fetch service price
$stmt = $conn->prepare("SELECT service_price FROM service_pricing WHERE service_id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$stmt->bind_result($service_price);
$stmt->fetch();
$stmt->close();
$total_amount += $service_price;

// Fetch add-on prices
if (!empty($addons)) {
    $addon_ids = implode(',', array_fill(0, count($addons), '?'));
    $stmt = $conn->prepare("SELECT ao_price FROM addon WHERE ao_id IN ($addon_ids)");
    $stmt->bind_param(str_repeat('i', count($addons)), ...$addons);
    $stmt->execute();
    $stmt->bind_result($ao_price);
    while ($stmt->fetch()) $total_amount += $ao_price;
    $stmt->close();
}

// === STEP 2: Convert RM to sen ===
// $amount_in_sen = $total_amount * 100;
$amount_in_sen = (int) round($total_amount * 100);

// === STEP 3: Create ToyyibPay Bill ===
$bill_data = [
    'userSecretKey' => 'p8bt5ekz-a7xz-xtwn-xo7u-yprm4n2gv7gn',
    'categoryCode' => 'pyoc7tn6',
    'billName' => 'Sinderella Booking',
    'billDescription' => 'Booking ID #' . $booking_id,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $amount_in_sen,
    'billReturnUrl' => 'http://localhost/Sinderella_FYP/rc/payment_success.php',
    'billCallbackUrl' => 'http://localhost/Sinderella_FYP/rc/payment_callback.php',
    // 'billReturnUrl' => 'http://sinderella.free.nf/rc/payment_success.php',
    // 'billCallbackUrl' => 'http://sinderella.free.nf/rc/payment_callback.php',
    'billExternalReferenceNo' => $booking_id,
    'billTo' => 'Customer ' . $cust_id,
    'billEmail' => 'test@example.com',
    'billPhone' => '0100000000'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createBill');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bill_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$resp_data = json_decode($response, true);
$bill_code = $resp_data[0]['BillCode'] ?? null;

if ($bill_code) {
    // Save to DB
    $stmt = $conn->prepare("INSERT INTO booking_payments (booking_id, bill_code, payment_amount, payment_status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("isi", $booking_id, $bill_code, $total_amount);
    // $stmt = $conn->prepare("UPDATE bookings SET bill_code=?, payment_status='pending' WHERE booking_id=?");
    // $stmt->bind_param("si", $bill_code, $booking_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to ToyyibPay
    header("Location: https://dev.toyyibpay.com/$bill_code");
    exit();
} else {
    echo "Failed to create bill. Response: " . htmlspecialchars($response);
    exit();
}

// $conn->close();

exit();
?>
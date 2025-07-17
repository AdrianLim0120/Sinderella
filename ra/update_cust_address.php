<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

// Get and validate input
if (!isset($_GET['address_id']) || !isset($_GET['cust_id'])) {
    header("Location: view_customers.php");
    exit();
}

$cust_id = $_GET['cust_id'];
$address_id = $_GET['address_id'];

// Fetch address info
require_once '../db_connect.php';

$stmt = $conn->prepare("SELECT cust_address, cust_postcode, cust_area, cust_state FROM cust_addresses WHERE cust_address_id = ? AND cust_id = ?");
$stmt->bind_param("ii", $address_id, $cust_id);
$stmt->execute();
$stmt->bind_result($cust_address, $cust_postcode, $cust_area, $cust_state);
if (!$stmt->fetch()) {
    $stmt->close();
    // $conn->close();
    header("Location: edit_customer.php?cust_id=$cust_id");
    exit();
}
$stmt->close();
// $conn->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'];
    $postcode = $_POST['postcode'];
    $area = $_POST['area'];
    $state = $_POST['state'];

    // Reconnect to update
    require_once '../db_connect.php'; //
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (!ctype_digit($postcode) || strlen($postcode) != 5) {
        echo "<script>document.getElementById('error-message').innerText = 'Invalid postcode.';</script>";
        exit();
    }

    if (empty($area) || empty($state)) {
        echo "<script>document.getElementById('error-message').innerText = 'Invalid postcode. Area and state not found.';</script>";
        exit();
    }

    $address = ucwords(strtolower($address));
    $area = ucwords(strtolower($area));
    $state = ucwords(strtolower($state));

    $stmt = $conn->prepare("UPDATE cust_addresses SET cust_address = ?, cust_postcode = ?, cust_area = ?, cust_state = ? WHERE cust_address_id = ? AND cust_id = ?");
    if (!$stmt) {
        echo "<script>document.getElementById('error-message').innerText = 'Database error: {$conn->error}';</script>";
        exit();
    }
    $stmt->bind_param("ssssii", $address, $postcode, $area, $state, $address_id, $cust_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        header("Location: edit_customer.php?cust_id=$cust_id&success=1");
        exit();
    } else {
        echo "<script>document.getElementById('error-message').innerText = 'Failed to update address.';</script>";
    }
    $stmt->close();
    // $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer Address - Admin - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/loginstyles.css">
    <script src="../includes/js/address_info.js" defer></script>
</head>
<body>
<div class="login-container">
    <div class="login-left">
        <img src="../img/sinderella_logo.png" alt="Sinderella">
        <p style="font-size:1rem;"><a href="edit_customer.php?cust_id=<?php echo $cust_id; ?>">&lt; Back to Customer</a></p>
    </div>
    <div class="login-right">
        <form id="addressForm" action="update_cust_address.php?cust_id=<?php echo $cust_id; ?>&address_id=<?php echo $address_id; ?>" method="POST">
            <h2>Update Address</h2>

            <label for="address">Address:</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($cust_address); ?></textarea>

            <label for="postcode">Postcode:</label>
            <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($cust_postcode); ?>" required>

            <label for="area">Area:</label>
            <input type="text" id="area" name="area" value="<?php echo htmlspecialchars($cust_area); ?>" readonly>

            <label for="state">State:</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($cust_state); ?>" readonly>

            <button type="submit">Save</button>
            <p id="error-message"></p>
        </form>
    </div>
</div>
</body>
</html>

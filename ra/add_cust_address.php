<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

if (!isset($_GET['cust_id'])) {
    header("Location: view_customers.php");
    exit();
}

$cust_id = $_GET['cust_id'];
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $postcode = $_POST['postcode'];
    $area = $_POST['area'];
    $state = $_POST['state'];

    // Database connection
    require_once '../db_connect.php'; //
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        echo "<script>document.getElementById('error-message').innerText = 'Unable to connect to the database.';</script>";
        exit();
    }

    // Validate postcode
    if (!ctype_digit($postcode) || strlen($postcode) != 5) {
        echo "<script>document.getElementById('error-message').innerText = 'Invalid postcode.';</script>";
        exit();
    }

    if (empty($area) || empty($state)) {
        echo "<script>document.getElementById('error-message').innerText = 'Invalid postcode. Area and state not found.';</script>";
        exit();
    }

    // Format data
    $address = ucwords(strtolower($address));
    $area = ucwords(strtolower($area));
    $state = ucwords(strtolower($state));

    // Insert address
    $stmt = $conn->prepare("INSERT INTO cust_addresses (cust_id, cust_address, cust_postcode, cust_area, cust_state) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "<script>document.getElementById('error-message').innerText = 'Database error: {$conn->error}';</script>";
        exit();
    }

    $stmt->bind_param("issss", $cust_id, $address, $postcode, $area, $state);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        header("Location: edit_customer.php?cust_id=" . $cust_id);
        exit();
    } else {
        echo "<script>document.getElementById('error-message').innerText = 'Failed to add address.';</script>";
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
    <title>Add Address - Admin - Sinderella</title>
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
            <form action="add_cust_address.php?cust_id=<?php echo $cust_id; ?>" method="POST" id="addressForm">
                <h2>Add New Address</h2>
                <label for="address">Address:</label>
                <textarea id="address" name="address" required></textarea>

                <label for="postcode">Postcode:</label>
                <input type="text" id="postcode" name="postcode" required>

                <label for="area">Area:</label>
                <input type="text" id="area" name="area" readonly>

                <label for="state">State:</label>
                <input type="text" id="state" name="state" readonly>

                <button type="submit">Add Address</button>
                <p id="error-message"><?php echo $error_message; ?></p>
            </form>
        </div>
    </div>
</body>
</html>

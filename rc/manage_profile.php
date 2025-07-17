<?php
session_start();
if (!isset($_SESSION['cust_id'])) {
    header("Location: ../login_cust.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$cust_id = $_SESSION['cust_id'];

// Retrieve customer details
$stmt = $conn->prepare("SELECT cust_name, cust_phno FROM customers WHERE cust_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($cust_name, $cust_phno);
$stmt->fetch();
$stmt->close();

// Retrieve customer addresses
$addresses = [];
$stmt = $conn->prepare("SELECT cust_address_id, cust_address, cust_postcode, cust_area, cust_state FROM cust_addresses WHERE cust_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($cust_address_id, $cust_address, $cust_postcode, $cust_area, $cust_state);
while ($stmt->fetch()) {
    $addresses[] = [
        'id' => $cust_address_id, // This line fixes the problem
        'address' => $cust_address,
        'postcode' => $cust_postcode,
        'area' => $cust_area,
        'state' => $cust_state
    ];
}
$stmt->close();
// $conn->close();

$cust_id_formatted = str_pad($cust_id, 4, '0', STR_PAD_LEFT);
$cust_phno_formatted = preg_replace("/(\d{3})(\d{3})(\d{4})/", "$1-$2 $3", $cust_phno);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
</head>
<body>
    <div class="main-container">
        <?php include '../includes/menu/menu_cust.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_cust.php'; ?>
            <div class="profile-container">
                <h2>Manage Profile</h2>
                <table>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>: <?php echo htmlspecialchars($cust_name); ?> <!-- [ID: <?php echo $cust_id_formatted; ?>] --></td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number</strong></td>
                        <td>: <?php echo htmlspecialchars($cust_phno_formatted); ?></td>
                    </tr>
                </table>
                <button onclick="location.href='reset_pwd.php'">Reset Password</button>
                <br><br>
                <h3>Addresses</h3>
                <?php if (!empty($addresses)): ?>
                    <form id="deleteForm" method="POST" action="delete_address.php" style="display: none;">
                        <input type="hidden" name="delete_address_id" id="delete_address_id">
                    </form>

                    <table>
                        <?php foreach ($addresses as $index => $address): ?>
                            <tr>
                                <td><strong>Address <?php echo $index + 1; ?></strong></td>
                                <td>: <?php echo htmlspecialchars($address['address']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Postcode</strong></td>
                                <td>: <?php echo htmlspecialchars($address['postcode']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Area</strong></td>
                                <td>: <?php echo htmlspecialchars($address['area']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>State</strong></td>
                                <td>: <?php echo htmlspecialchars($address['state']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button onclick="location.href='update_address.php?address_id=<?php echo $address['id']; ?>'">Update Address</button>
                                    <button onclick="confirmDelete(<?php echo $address['id']; ?>)" style="background-color: red; color: white;">Delete Address</button>
                                </td>
                            </tr>
                            <tr><td colspan="2"><hr></td></tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No addresses found. <a href="add_address.php">Add an address</a>.</p>
                <?php endif; ?>

                <button onclick="location.href='add_address.php'">Add Address</button>
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(addressId) {
            if (confirm("Are you sure you want to delete this address? This action cannot be undone.")) {
                document.getElementById('delete_address_id').value = addressId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
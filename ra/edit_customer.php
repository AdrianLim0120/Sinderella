<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

require_once '../db_connect.php';

$cust_id = isset($_GET['cust_id']) ? $_GET['cust_id'] : 0;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cust_name = ucwords(strtolower(trim($_POST['cust_name'])));
    $cust_phno = $_POST['cust_phno'];
    $cust_status = $_POST['cust_status'];
    $cust_labels = isset($_POST['cust_labels']) ? $_POST['cust_labels'] : [];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE cust_phno = ? AND cust_id != ? AND cust_status = 'active'");
    $stmt->bind_param("si", $cust_phno, $cust_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error_message = 'The phone number is already used by another active customer.';
    } else {
        $stmt = $conn->prepare("UPDATE customers SET cust_name = ?, cust_phno = ?, cust_status = ? WHERE cust_id = ?");
        $stmt->bind_param("sssi", $cust_name, $cust_phno, $cust_status, $cust_id);
        if (!$stmt->execute()) {
            die("Error updating record: " . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM cust_id_label WHERE cust_id = ?");
        $stmt->bind_param("i", $cust_id);
        $stmt->execute();
        $stmt->close();

        foreach ($cust_labels as $label_id) {
            $stmt = $conn->prepare("INSERT INTO cust_id_label (cust_id, clbl_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $cust_id, $label_id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: view_customers.php");
        exit();
    }
}

$stmt = $conn->prepare("SELECT c.cust_name, c.cust_phno, c.cust_status FROM customers c WHERE c.cust_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($cust_name, $cust_phno, $cust_status);
$stmt->fetch();
$stmt->close();

$labels = $conn->query("SELECT clbl_id, clbl_name FROM cust_label WHERE clbl_status = 'Active'");

$selected_labels = [];
$stmt = $conn->prepare("SELECT clbl_id FROM cust_id_label WHERE cust_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($clbl_id);
while ($stmt->fetch()) {
    $selected_labels[] = $clbl_id;
}
$stmt->close();

$addresses = [];
$stmt = $conn->prepare("SELECT cust_address_id, cust_address, cust_postcode, cust_area, cust_state FROM cust_addresses WHERE cust_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($address_id, $cust_address, $cust_postcode, $cust_area, $cust_state);
while ($stmt->fetch()) {
    $addresses[] = [
        'id' => $address_id,
        'address' => $cust_address,
        'postcode' => $cust_postcode,
        'area' => $cust_area,
        'state' => $cust_state
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - Admin - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
    <style>
        .profile-container {
            /* display: flex; */
        }
        .profile-container .left, .profile-container .right {
            flex: 1;
            padding: 20px;
        }
        .profile-container label {
            display: block;
            margin-top: 10px;
        }
        .profile-container input[type="text"],
        .profile-container input[type="number"],
        .profile-container select,
        .profile-container textarea {
            width: calc(100% - 10px);
            padding: 5px;
            margin-right: 10px;
        }
        .profile-container button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .profile-container button:hover {
            background-color: #0056b3;
        }
        /* .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        } */
        .error-message {
            color: red;
            margin-top: 10px;
        }
        .profile-photo-container {
            text-align: center;
        }
        .invalid-postcode {
            color: red;
            margin-top: 5px;
        }
    </style>
    <script>
        function confirmDelete(addressId) {
            if (confirm("Are you sure you want to delete this address? This action cannot be undone.")) {
                document.getElementById('delete_address_id').value = addressId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</head>
<body>
<div class="main-container">
    <?php include '../includes/menu/menu_adm.php'; ?>
    <div class="content-container">
        <?php include '../includes/header_adm.php'; ?>
        <div class="profile-container">
            <h2>Edit Customer</h2>
            <form method="POST" action="">
                <label for="cust_name">Name</label>
                <input type="text" id="cust_name" name="cust_name" value="<?php echo htmlspecialchars($cust_name); ?>" required>

                <label for="cust_phno">Phone Number</label>
                <input type="text" id="cust_phno" name="cust_phno" value="<?php echo htmlspecialchars($cust_phno); ?>" required>

                <label for="cust_status">Status</label>
                <select id="cust_status" name="cust_status" required>
                    <option value="active" <?php if ($cust_status == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($cust_status == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>

                <label for="cust_labels">Labels</label><br>
                <?php while ($label = $labels->fetch_assoc()): ?>
                    <input type="checkbox" name="cust_labels[]" value="<?php echo $label['clbl_id']; ?>" <?php if (in_array($label['clbl_id'], $selected_labels)) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($label['clbl_name']); ?>
                <?php endwhile; ?>

                <div class="button-container">
                    <button type="submit">Save Changes</button>
                    <button type="button" onclick="window.location.href='view_customers.php'">Back</button>
                </div>
            </form>

            <br>
            <h3>Addresses</h3>
            <?php if (!empty($addresses)): ?>
                <form id="deleteForm" method="POST" action="delete_cust_address.php" style="display: none;">
                    <input type="hidden" name="delete_address_id" id="delete_address_id">
                    <input type="hidden" name="cust_id" value="<?php echo $cust_id; ?>">
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
                                <button onclick="location.href='update_cust_address.php?address_id=<?php echo $address['id']; ?>&cust_id=<?php echo $cust_id; ?>'">Update Address</button>
                                <button onclick="confirmDelete(<?php echo $address['id']; ?>)" style="background-color: red; color: white;">Delete Address</button>
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No addresses found. <a href="add_cust_address.php?cust_id=<?php echo $cust_id; ?>">Add an address</a>.</p>
            <?php endif; ?>
            <button onclick="location.href='add_cust_address.php?cust_id=<?php echo $cust_id; ?>'">Add Address</button>
        </div>
    </div>
</div>
</body>
</html>
<?php
// // $conn->close();
?>

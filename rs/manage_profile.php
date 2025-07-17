<?php
session_start();
if (!isset($_SESSION['sind_id'])) {
    header("Location: ../login_sind.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$sind_id = $_SESSION['sind_id'];
$stmt = $conn->prepare("SELECT sind_name, sind_phno, sind_address, sind_postcode, sind_area, sind_state, sind_profile_path, sind_upline_id FROM sinderellas WHERE sind_id = ?");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($sind_name, $sind_phno, $sind_address, $sind_postcode, $sind_area, $sind_state, $sind_profile_path, $sind_upline_id);
$stmt->fetch();
$stmt->close();

$upline_name = "N/A";
if ($sind_upline_id === null || $sind_upline_id === '' || $sind_upline_id === false) {
    $upline_name = "N/A";
} elseif ($sind_upline_id == 0) {
    $upline_name = "Sinderella";
} else {
    $stmt = $conn->prepare("SELECT sind_name FROM sinderellas WHERE sind_id = ?");
    $stmt->bind_param("i", $sind_upline_id);
    $stmt->execute();
    $stmt->bind_result($found_upline_name);
    if ($stmt->fetch()) {
        $upline_name = $found_upline_name;
    }
    $stmt->close();
}

// Retrieve service areas
$service_areas = [];
$stmt = $conn->prepare("SELECT area, state FROM sind_service_area WHERE sind_id = ?");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($area, $state);
while ($stmt->fetch()) {
    $service_areas[] = ['area' => $area, 'state' => $state];
}
$stmt->close();
// $conn->close();

$sind_phno_formatted = preg_replace("/(\d{3})(\d{3})(\d{4})/", "$1-$2 $3", $sind_phno);
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
        <?php include '../includes/menu/menu_sind.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_sind.php'; ?>
            <div class="profile-container">
                <h2>Manage Profile</h2>
                <div style="display: flex;">
                    <div style="flex: 1;">
                        <table>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_name); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone Number</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_phno_formatted); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Upline</strong></td>
                                <td>: <?php echo htmlspecialchars($upline_name); ?></td>
                            </tr>
                        </table>
                        <button onclick="location.href='reset_pwd.php'">Reset Password</button>
                        <br><br>
                        <table>
                            <tr>
                                <td><strong>Address</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_address); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Postcode</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_postcode); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Area</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_area); ?></td>
                            </tr>
                            <tr>
                                <td><strong>State</strong></td>
                                <td>: <?php echo htmlspecialchars($sind_state); ?></td>
                            </tr>
                        </table>
                        <button onclick="location.href='update_address.php'">Update Address</button>
                        <br><br>
                        <h3>Service Areas</h3>
                        <ul>
                            <?php foreach ($service_areas as $service_area) { ?>
                                <li><?php echo htmlspecialchars($service_area['area']) . ', ' . htmlspecialchars($service_area['state']); ?></li>
                            <?php } ?>
                        </ul>
                        <button onclick="location.href='update_service_area.php'">Update Service Area</button>
                    </div>
                    <div style="flex: 1; text-align: center;">
                        <img src="<?php echo htmlspecialchars($sind_profile_path); ?>" alt="Profile Photo" style="max-width: 200px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
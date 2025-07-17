<?php

$phone = $_GET['phone'] ?? '';
$error_message = '';
$uplines = [];

if (!$phone) {
    $error_message = "Invalid access. Phone number missing.";
} else {
    // DB connection
    require_once '../db_connect.php'; //
    if ($conn->connect_error) {
        $error_message = "Connection failed: " . $conn->connect_error;
    } else {
        // Get all sind_id who registered this phone as downline
        $stmt = $conn->prepare("SELECT sind_id FROM sind_downline WHERE dwln_phno = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $upline_ids = [];
        while ($row = $result->fetch_assoc()) {
            $upline_ids[] = $row['sind_id'];
        }
        $stmt->close();

        if (empty($upline_ids)) {
            $error_message = "Phone number is not registered by an introducer. <br>Please contact your introducer or customer service.";
        } else {
            // Get full names of all uplines
            $in = implode(',', array_fill(0, count($upline_ids), '?'));
            $types = str_repeat('i', count($upline_ids));
            $stmt = $conn->prepare("SELECT sind_id, sind_name FROM sinderellas WHERE sind_id IN ($in)");
            $stmt->bind_param($types, ...$upline_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $uplines[] = $row;
            }
            $stmt->close();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_upline'])) {
    $selected_upline = $_POST['selected_upline'];
    $phone = $_POST['phone'];
    require_once '../db_connect.php'; //
    if ($conn->connect_error) {
        $error_message = "Connection failed: " . $conn->connect_error;
    } else {
        // Update sind_upline_id for this Sinderella
        $stmt = $conn->prepare("UPDATE sinderellas SET sind_upline_id = ? WHERE sind_phno = ?");
        $stmt->bind_param("is", $selected_upline, $phone);
        if ($stmt->execute()) {
            // Proceed to next page (e.g., identity verification)
            header("Location: address_info.php?phone=$phone");
            exit();
        } else {
            $error_message = "Failed to update upline. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Upline - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/loginstyles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="../img/sinderella_logo.png" alt="Sinderella">
            <p>Love cleaning? Love flexibility & freedom?</p>
        </div>
        <div class="login-right">
            <form method="POST" action="select_upline.php?phone=<?php echo htmlspecialchars($phone); ?>">
                <h2>Select Your Upline</h2>
                <?php if ($error_message): ?>
                    <p id="error-message" style="color:red;"><?php echo $error_message; ?></p>
                <?php elseif (!empty($uplines)): ?>
                    <?php foreach ($uplines as $upline): ?>
                        <label style="display:block; margin-bottom:8px;">
                            <input type="radio" name="selected_upline" value="<?php echo $upline['sind_id']; ?>" required>
                            <?php echo htmlspecialchars($upline['sind_name']); ?>
                        </label>
                    <?php endforeach; ?>
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    <button type="submit">Confirm Upline</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
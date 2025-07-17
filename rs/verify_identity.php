<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ic_number = $_POST['ic_number'];
        $phone = $_POST['phone']; 

        // Database connection
        require_once '../db_connect.php'; //

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Validate IC number
        if (!ctype_digit($ic_number) || strlen($ic_number) != 12) {
            echo "<script>document.getElementById('error-message').innerText = 'IC number must be a 12-digit numeric value.';</script>";
            exit();
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT sind_id FROM sinderellas WHERE sind_phno = ? AND (sind_icno IS NULL OR sind_icno='')");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            echo "<script>document.getElementById('error-message').innerText = 'User does not exist.';</script>";
            $stmt->close();
            // $conn->close();
            exit();
        }

        $stmt->bind_result($sind_id);
        $stmt->fetch();
        $stmt->close();

        // Handle IC photo upload
        $target_dir_ic = "../img/ic_photo/";
        $target_file_ic = $target_dir_ic . str_pad($sind_id, 4, '0', STR_PAD_LEFT) . ".jpg";
        if (!move_uploaded_file($_FILES["ic_photo"]["tmp_name"], $target_file_ic)) {
            echo "<script>document.getElementById('error-message').innerText = 'Failed to upload IC photo.';</script>";
            exit();
        }

        // Handle profile photo upload
        $target_dir_profile = "../img/profile_photo/";
        $target_file_profile = $target_dir_profile . str_pad($sind_id, 4, '0', STR_PAD_LEFT) . ".jpg";
        if (!move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file_profile)) {
            echo "<script>document.getElementById('error-message').innerText = 'Failed to upload profile photo.';</script>";
            exit();
        }

        // Update database with IC number and photo paths
        $stmt = $conn->prepare("UPDATE sinderellas SET sind_icno = ?, sind_icphoto_path = ?, sind_profile_path = ? WHERE sind_id = ?");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("sssi", $ic_number, $target_file_ic, $target_file_profile, $sind_id);
        $stmt->execute();
        $stmt->close();
        // $conn->close();

        echo "<script>alert(\"Identity submitted successfully.\\nYou may now log in to your account.\"); window.location.href = '../login_sind.php';</script>";    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Identity - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/loginstyles.css">
    <script src="../includes/js/verify_identity.js" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="../img/sinderella_logo.png" alt="Sinderella">
            <p>Love cleaning? Love flexibility & freedom?</p>
        </div>
        <div class="login-right">
            <form id="verifyIdentityForm" action="verify_identity.php" method="POST" enctype="multipart/form-data">
                <h2>Verify Identity</h2>
                <label for="ic_number">IC Number:</label>
                <input type="text" id="ic_number" name="ic_number" placeholder="Enter 12-digit IC number" required>
                <label for="ic_photo">Upload IC Photo:</label>
                <input type="file" id="ic_photo" name="ic_photo" accept="image/*" required>
                <label for="profile_photo">Upload Profile Photo:</label>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                <input type="hidden" id="phone" name="phone" value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>">
                <button type="submit">Submit</button>
                <p id="error-message"></p>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = ucwords(strtolower(trim($_POST['name'])));
        $phone = $_POST['phone'];
        $verification_code = $_POST['verification_code'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Database connection
        require_once '../db_connect.php'; //

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Sanitize phone number
        $phone = preg_replace('/[\s-]/', '', $phone);

        // Check if phone number is numeric
        if (!ctype_digit($phone)) {
            echo "<script>document.getElementById('error-message').innerText = 'Phone number must be numeric only.';</script>";
            exit();
        }

        // Check if phone number is already used by an active customer
        $stmt = $conn->prepare("SELECT cust_id FROM customers WHERE cust_phno = ? AND cust_status = 'active'");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>document.getElementById('error-message').innerText = 'Phone number is already in use by an active customer.';</script>";
            $stmt->close();
            // $conn->close();
            exit();
        }

        $stmt->close();

        // Check if passwords match
        if ($password !== $confirm_password) {
            echo "<script>document.getElementById('error-message').innerText = 'Password and confirm password must match.';</script>";
            exit();
        }

        // Check if verification code is valid
        $stmt = $conn->prepare("SELECT ver_code FROM verification_codes WHERE user_phno = ? AND ver_code = ? AND expires_at > NOW() AND used = 0");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("si", $phone, $verification_code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            echo "<script>document.getElementById('error-message').innerText = 'Invalid or expired verification code.';</script>";
            $stmt->close();
            // $conn->close();
            exit();
        }

        // Mark verification code as used
        $stmt->close();
        $stmt = $conn->prepare("UPDATE verification_codes SET used = 1 WHERE user_phno = ? AND ver_code = ?");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("si", $phone, $verification_code);
        $stmt->execute();
        $stmt->close();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new customer into the database
        $stmt = $conn->prepare("INSERT INTO customers (cust_name, cust_phno, cust_pwd, cust_status) VALUES (?, ?, ?, 'active')");
        if (!$stmt) {
            echo "<script>document.getElementById('error-message').innerText = 'Database error: " . $conn->error . "';</script>";
            exit();
        }
        $stmt->bind_param("sss", $name, $phone, $hashed_password);
        $stmt->execute();
        $stmt->close();
        // $conn->close();

        // Redirect to address information page
        header("Location: address_info.php?phone=$phone");
        exit();
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/loginstyles.css">
    <script src="../includes/js/signup.js" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="../img/sinderella_logo.png" alt="Sinderella">
            <p>Do you need a helping hand in cleaning?</p>
            <p style="font-size:1rem;"><a href="../index.php">< Back to Home</a></p>
        </div>
        <div class="login-right">
            <form id="signupForm" action="signup.php" method="POST">
                <h2>Customer Sign Up</h2>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Exp: Tan Xiao Hua" required>
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" placeholder="Exp: 0123456789" required>
                <button type="button" id="getCodeButton">Get Code</button>
                <label for="verification_code">Verification Code:</label>
                <input type="text" id="verification_code" name="verification_code" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="submit">Sign Up</button>
                <p id="error-message"></p>
                <p><a href="../login_cust.php">Already have an account? Sign In</a></p>
            </form>
        </div>
    </div>
</body>
</html>
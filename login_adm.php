<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Database connection
    require_once 'db_connect.php'; //

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if user exists and retrieve status
    $stmt = $conn->prepare("SELECT adm_id, adm_pwd, adm_status FROM admins WHERE adm_phno = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($adm_id, $hashed_password, $adm_status);
        $stmt->fetch();

        if ($adm_status === 'active') {
            if (password_verify($password, $hashed_password)) {
                // Correct password, set session variables and update last login date
                $_SESSION['adm_id'] = $adm_id;

                $update_stmt = $conn->prepare("UPDATE admins SET last_login_date = NOW() WHERE adm_id = ?");
                $update_stmt->bind_param("i", $adm_id);
                $update_stmt->execute();
                $update_stmt->close();

                header("Location: ra/manage_profile.php");
                exit();
            } else {
                // Wrong password
                $error_message = "Wrong password";
            }
        } elseif ($adm_status === 'inactive') {
            // Account is inactive
            $error_message = "Your account has been deactivated. <br>Please contact the system administrator for more info.";
        } else {
            // Unknown status
            $error_message = "Unable to log in. Please try again later.";
        }
    } else {
        // User not found
        $error_message = "User not found";
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
    <title>Login - Sinderella</title>
    <link rel="icon" href="img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="includes/css/loginstyles.css">
    <script src="includes/js/scripts.js" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="img/sinderella_logo.png" alt="Sinderella">
            <p style="font-size:1rem;"><a href="index.php">< Back to Home</a></p>
        </div>
        <div class="login-right">
            <form id="loginForm" action="login_adm.php" method="POST">
                <h2>Admin Login</h2>
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" placeholder="Exp: 0123456789" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="" required>
                <button type="submit">Sign In</button>
                <p id="error-message"><?php if (isset($error_message)) echo $error_message; ?></p>
                <p><a href="ra/forgot_pwd.php">Forgot Password?</a></p>
            </form>
        </div>
    </div>
</body>
</html>
<?php
// Start the session if not already started

    session_start();

// Redirect if OTP is not found in session
if (!isset($_SESSION["OTP"])) {
    echo "OTP not found!";
    exit();
}

$error_message = ""; // Initialize error message variable

// OTP verification logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["otp_submit"])) {
    $entered_otp = $_POST["otp"];

    if ($entered_otp == $_SESSION["OTP"]) {
        // OTP verification successful
        header("Location: ../Authentication/add_user.php"); // Redirect to addUser.php for user registration
        exit();
    } else {
        // OTP verification failed
        $error_message = "OTP verification failed. Please enter the correct OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
    <div class="otp-verification">
        <div class="right-side">
            <a href="../index.php" class="logo-link"><img src="../assets/images/icons/logo.png" alt="" class="logo"></a>
            <h2>OTP Verification</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form class="otp-form" method="POST">
                <span id="otp">Enter OTP</span>
                <input type="text" id="otp" name="otp" placeholder="OTP" class="input-field" required>
                <input type="hidden" name="otp_submit" value="1">
                <button type="submit" class="submit-btn">Verify</button>
            </form>
            <div class="resend-link"><a href="../Authentication/resend.php">Didn't receive a code? Resend again</a></div>
        </div>
    </div>
</body>
</html>

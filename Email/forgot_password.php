<?php 
session_start();
include '../messages/notifications.php';

list($error, $notification) = flashNotification();

// Establish connection to the database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $oldPassword = $_POST['old-password'];
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];
    $email = $_SESSION['reset_email'];

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
       
        exit;
    }

    // Fetch the current password from Customer table
    $sqlGetCustomerPassword = "SELECT Password FROM Customer WHERE Email = :email";
    $stmtGetCustomerPassword = oci_parse($conn, $sqlGetCustomerPassword);
    oci_bind_by_name($stmtGetCustomerPassword, ':email', $email);
    oci_execute($stmtGetCustomerPassword);
    $customer = oci_fetch_assoc($stmtGetCustomerPassword);
    oci_free_statement($stmtGetCustomerPassword);

    // Fetch the current password from Trader table
    $sqlGetTraderPassword = "SELECT Password FROM Trader WHERE Email = :email";
    $stmtGetTraderPassword = oci_parse($conn, $sqlGetTraderPassword);
    oci_bind_by_name($stmtGetTraderPassword, ':email', $email);
    oci_execute($stmtGetTraderPassword);
    $trader = oci_fetch_assoc($stmtGetTraderPassword);
    oci_free_statement($stmtGetTraderPassword);

    // Check if the old password matches the current password
    if (($customer && $customer['PASSWORD'] == $oldPassword) || ($trader && $trader['PASSWORD'] == $oldPassword)) {
        // Update the password for the customer
        if ($customer) {
            $sqlUpdatePasswordCustomer = "UPDATE Customer SET Password = :password WHERE Email = :email";
            $stmtUpdatePasswordCustomer = oci_parse($conn, $sqlUpdatePasswordCustomer);
            oci_bind_by_name($stmtUpdatePasswordCustomer, ':password', $newPassword);
            oci_bind_by_name($stmtUpdatePasswordCustomer, ':email', $email);
            $updateCustomer = oci_execute($stmtUpdatePasswordCustomer);
            oci_free_statement($stmtUpdatePasswordCustomer);
        }

        // Update the password for the trader
        if ($trader) {
            $sqlUpdatePasswordTrader = "UPDATE Trader SET Password = :password WHERE Email = :email";
            $stmtUpdatePasswordTrader = oci_parse($conn, $sqlUpdatePasswordTrader);
            oci_bind_by_name($stmtUpdatePasswordTrader, ':password', $newPassword);
            oci_bind_by_name($stmtUpdatePasswordTrader, ':email', $email);
            $updateTrader = oci_execute($stmtUpdatePasswordTrader);
            oci_free_statement($stmtUpdatePasswordTrader);
        }

        if ($updateCustomer || $updateTrader) {
            oci_commit($conn);
            $_SESSION['notification'] = "Password updated successfully.";
            unset($_SESSION['reset_email']);
        } else {
            $_SESSION['error'] = "Error updating password.";
        }
    } else {
        $_SESSION['error'] = "Old password does not match.";
        header('Location: forgot_password.php');
    }
    header('Location: ../Login/customer_signin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
    <div class="password-reset">
        <div class="right-side">
            <a href="../index.php" class="logo-link">
                <img src="../assets/images/icons/logo.png" alt="" class="logo">
            </a>
            <h2>Reset Password</h2>
            <?php if($error): ?>
                    <div class="error-message" role="alert">
                    <?php  echo $error; ?>
                    </div>
                    <?php endif; ?>
            <form class="otp-form" id="reset-password-form" method="POST">
                <div class="input-group">
                    <label for="old-password">Enter Old Password</label>
                    <input type="password" id="old-password" name="old-password" placeholder="Old Password" class="input-field" required>
                </div>
                <div class="input-group">
                    <label for="new-password">Enter New Password</label>
                    <input type="password" id="new-password" name="new-password" placeholder="New Password" class="input-field" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" class="input-field" required>
                </div>
                <input type="hidden" name="submit" value="1">
                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>

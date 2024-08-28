<?php
require_once '../middlewares/checkAuthentication.php';
checkIfUserIsLoggedIn();
include '../messages/notifications.php';

list($error, $notification) = flashNotification();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    header("Location: change_password.php");
    exit();
} 


if (isset($_POST['submit'])) {
    $customerId = $_SESSION['user']['CUSTOMER_ID'];
    $old_password = $_POST['opassword'];
    $new_password = $_POST['npassword'];
    $confirm_password = $_POST['cpassword'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: change_password.php");
        exit();
    }

    $query = "SELECT PASSWORD FROM CUSTOMER WHERE CUSTOMER_ID = '$customerId'";
    $statement = oci_parse($conn, $query);
    oci_execute($statement);
    $fetch_password = oci_fetch_assoc($statement);

    if ($fetch_password['PASSWORD'] !== $old_password) {
        $_SESSION['error'] = "Old password is incorrect";
        header("Location: change_password.php");
        exit();
    }

    $query = "UPDATE CUSTOMER SET PASSWORD = '$new_password' WHERE CUSTOMER_ID = '$customerId'";
    $statement = oci_parse($conn, $query);
    $result = oci_execute($statement);

    if ($result) {
        oci_commit($conn);
        $_SESSION['notification'] = "Password changed successfully";
        header("Location: customer_profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating password!";
        header("Location: change_password.php");
        exit();
    }
}

oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../messages/notification.css">
</head>
<body>
    <div class="change-password">
        <div class="right-side">
            <h2>Change Password</h2>
            <?php if (isset($_SESSION['notification'])): ?>
                    <div class="notification-message" role="alert">
                        <?php echo $_SESSION['notification']; unset($_SESSION['notification']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
            <form class="change-form" id="chnage-password-form"  method="POST">
                <div class="input-group">
                    <label for="old-password">Enter Old Password</label>
                    <input type="password" id="old-password" name="opassword" placeholder="Old Password" class="input-field" required>
                </div>
                <div class="input-group">
                    <label for="new-password">Enter New Password</label>
                    <input type="password" id="new-password" name="npassword" placeholder="New Password" class="input-field" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="cpassword" placeholder="Confirm Password" class="input-field" required>
                </div>
                <button type="submit" name="submit" class="submit-btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>

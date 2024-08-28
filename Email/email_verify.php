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
    $email = $_POST['email'];

    // Check if the email exists in the Customer table
    $sqlCustomer = "SELECT CUSTOMER_ID FROM Customer WHERE Email = :email";
    $stmtCustomer = oci_parse($conn, $sqlCustomer);
    oci_bind_by_name($stmtCustomer, ':email', $email);
    oci_execute($stmtCustomer);

    $customer = oci_fetch_assoc($stmtCustomer);
    oci_free_statement($stmtCustomer);

    // Check if the email exists in the Trader table
    $sqlTrader = "SELECT TRADER_ID FROM Trader WHERE Email = :email";
    $stmtTrader = oci_parse($conn, $sqlTrader);
    oci_bind_by_name($stmtTrader, ':email', $email);
    oci_execute($stmtTrader);

    $trader = oci_fetch_assoc($stmtTrader);
    oci_free_statement($stmtTrader);

    if ($customer || $trader) {
        // Store the email in session
        $_SESSION['reset_email'] = $email;

        // Redirect to the reset password page
        header("Location: forgot_password.php");
        exit;
    } else {
        $_SESSION['error'] = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
       .email-verification {
  margin: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 100px;
  flex-direction: column; 
}

.email-verification .right-side {
  width: 90%;
  max-width: 500px; 
  padding: 40px;
  background-color: #fcfafa;
  border-radius: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.email-verification h2 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 20px;
  text-align: center;
}

.email-verification .email-form{
  margin-top: 20px;
}
.email-verification #email{
  font-weight: bold;
}

.email-verification .input-field {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background-color: #f3f4f6;
}

.email-verification .submit-btn {
  width: 100%;
  background-color: orange;
  color: #ffffff;
  padding: 10px;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-bottom: 15px;
}

.email-verification .submit-btn:hover {
  background-color: crimson;
}


.email-verification .logo-link {
  display: flex;
  justify-content: center; 
  align-items: center; 
  margin-bottom: 20px;
}

.email-verification .logo {
  width: 150px;
  height: auto;
  margin-bottom: 0.5rem;
}
    </style>
</head>
<body>
    <div class="email-verification">
        <div class="right-side">
            <a href="../index.php" class="logo-link"><img src="../assets/images/icons/logo.png" alt="" class="logo"></a>
            <h2>Email Verification</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form class="email-form" method="POST">
                <span id="email">Enter your email</span>
                <input type="email" id="email" name="email" placeholder="email" class="input-field" required>
                <input type="hidden" name="submit" value="1">
                <button type="submit" class="submit-btn">Verify</button>
            </form>
        </div>
    </div>
</body>
</html>



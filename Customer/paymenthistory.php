<?php
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit();
} 

if (isset($_SESSION['user']['EMAIL'])) {
    $userEmail = $_SESSION['user']['EMAIL'];
} else {
    $_SESSION['error'] = "User not found";
    header("Location: ../Login/customer_signin.php");
    exit();
}

$query = "SELECT p.Payment_ID, p.Amount, p.Payment_Method, p.Payment_Date, 
o.Order_ID, o.Order_Date, o.Order_Status, o.Total_Amount
FROM Payment p
JOIN Order_Detail o ON p.Payment_ID = o.Payment_ID
WHERE p.Customer_ID = (SELECT Customer_ID 
                FROM Customer 
                WHERE Email = :email)
ORDER BY p.Payment_Date";
$statement = oci_parse($conn, $query);
oci_bind_by_name($statement, ":email", $userEmail);
oci_execute($statement);

$payments = array();

while ($row = oci_fetch_assoc($statement)) {
    $payments[] = $row;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="./paymenthistory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .payment-info{
            display: flex;
            justify-content: space-between;
            padding: 20px 50px;
        }
    </style>
</head>
<body>
    <header><?php include('../includes/head.php');?></header>
    <div class="container flex">
        <div class="sidebar">
            <a href="./customer_profile.php">My Profile</a>
            <a href="./customer_order.php">My Orders</a>
            <a href="./Wishlist.php">My Wishlist</a>
            <a href="./paymenthistory.php">Payment History</a>
        </div>
        <div id="payments" class="page-header w-full px-8">
            <h1 class="text-2xl font-bold mb-4 text-center">Payment History</h1>
            <p class="text-gray-600 mb-8 text-center">View your past payments and payment details.</p>
            <div class="profile-content cart-items">
                <?php foreach ($payments as $payment): ?>
                    <!-- Payment History Items -->
                    <div class="payment-item bg-white shadow-md rounded-md p-6 mb-6 flex items-center justify-between">
                        <div class="payment-details flex-1">
                        <h5 class="text-lg font-semibold">Payment Details</h5>
                                <div class="payment-info">
                                    
                                    <p class="text-gray-600">Payment Date: <?php echo $payment['PAYMENT_DATE']; ?></p>
                                    <p class="text-gray-600">Total Amount: $<?php echo $payment['AMOUNT']; ?></p>
                                    <p class="text-gray-600">Payment Method: <?php echo $payment['PAYMENT_METHOD']; ?></p>
                                    <p class="text-gray-600"><span class="font-semibold">Transaction ID:</span> #<?php echo $payment['PAYMENT_ID']; ?></p>
                                    <p class="text-gray-600"><span class="font-semibold">Order ID:</span> #<?php echo $payment['ORDER_ID']; ?></p>
                                </div>
                            
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div><?php include('../includes/footer.php');?></div>
</body>
</html>

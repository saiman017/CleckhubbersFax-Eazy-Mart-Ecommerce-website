<?php
require_once '../middlewares/checkAuthentication.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../messages/notifications.php';

list($error, $notification) = flashNotification();
// Check if the user is logged in
checkIfUserIsLoggedIn();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}else{
    print "Connected Orcale";

}

// Cart Total amount
$customerID = $_SESSION['user']['CUSTOMER_ID'];
// Fetch total amount separately
$totalAmountSql = "SELECT * FROM CART WHERE CUSTOMER_ID = :customerID";
$totalStmt = oci_parse($conn, $totalAmountSql);
oci_bind_by_name($totalStmt, ":customerID", $customerID);
oci_execute($totalStmt);
$totalRow = oci_fetch_assoc($totalStmt);
$totalAmount = $totalRow["TOTAL_AMOUNT"];



// Product information
$sql = "SELECT p.PRODUCT_NAME, ci.QUANTITY, p.PRICE
FROM PRODUCT p
INNER JOIN CART_ITEM ci on ci.PRODUCT_ID = p.PRODUCT_ID
INNER JOIN CART c on c.CART_ID = ci.CART_ID
WHERE c.CUSTOMER_ID = :customerID";
$statement = oci_parse($conn, $sql);
oci_bind_by_name($statement, ":customerID", $customerID);
oci_execute($statement);

$order_product = array();
while ($odr = oci_fetch_assoc($statement)) {
    $order_product[] = $odr;
}

// Customer detail
$customersql = "SELECT FIRST_NAME || ' ' || LAST_NAME AS CUSTOMER_NAME, EMAIL FROM CUSTOMER WHERE CUSTOMER_ID = :customerID";
$statement2 = oci_parse($conn, $customersql);
oci_bind_by_name($statement2, ":customerID", $customerID);
oci_execute($statement2);
$result = oci_fetch_assoc($statement2);
if (!$result) {
    $_SESSION['error'] = "Customer id not found";
}

$_SESSION['TOTAL_AMOUNT'] = $totalAmount;
$_SESSION['CART_ID'] = $totalRow['CART_ID'];
$_SESSION['CUSTOMER_ID'] = $customerID;

// if(isset($_GET['Total_amount'])){
//     $amountPaid = $_GET['Total_amount'];

// }else{
//     $_SESSION['error'] = "Payment not done";
// }

// $cartid = $_SESSION['CART_ID'];
// $customerID = $_SESSION['user']['CUSTOMER_ID'];

// // insert payment
// $sql = "INSERT INTO PAYMENT (AMOUNT,PAYMENT_METHOD,PAYMENT_DATE,CUSTOMER_ID) VALUES ('$amountPaid','Paypal',SYSDATE,'$customerID')";
// $statement = oci_parse($conn,$sql);
// $result = oci_execute($statement);
// if(!$result){
//     $_SESSION['error'] = "payment error";
// }

// $payments = oci_fetch_assoc($statement);
// // FETCH PAYMENT ID
// $paymentid = $payments['PAYMENT_ID'];



// //let's find out available colection slot

// function getAvailableCollectionSlots($conn, $order_date) {
//     $slots = [];
//     $days = ['Wednesday', 'Thursday', 'Friday'];
//     $times = ['10-13', '13-16', '16-19'];
//     $min_date = clone $order_date;
//     $min_date->modify('+24 hours');

//     foreach ($days as $day) {
//         $slot_date = clone $order_date;
//         $slot_date->modify('next ' . $day);
//         if ($slot_date < $min_date) {
//             continue;
//         }
//         foreach ($times as $time) {
//             // Check if the slot is already filled
//             $slot_query = "SELECT COUNT(*) AS order_count
//                            FROM Collection_Slot
//                            WHERE Collection_Date = TO_DATE(:slot_date, 'YYYY-MM-DD')
//                            AND Collection_Time = :collection_time";
//             $stmt = oci_parse($conn, $slot_query);
//             $slot_date_str = $slot_date->format('Y-m-d');
//             oci_bind_by_name($stmt, ':slot_date', $slot_date_str);
//             oci_bind_by_name($stmt, ':collection_time', $time);
//             oci_execute($stmt);
//             $row = oci_fetch_assoc($stmt);

//             if ($row['ORDER_COUNT'] < 20) {  // Only include slots with less than 20 orders
//                 $slots[] = [
//                     'day' => $day,
//                     'date' => $slot_date->format('Y-m-d'),
//                     'time' => $time
//                 ];

//             }
//         }
//     }
//     return $slots;
// }


// //let's insert data into database for order


// function insertOrder($conn, $order_date, $order_amount, $customer_id, $cartID) {
//     $sql = "INSERT INTO Order_Detail (Order_Date, Order_Amount, Customer_ID, Product_ID) 
//             VALUES (TO_DATE(:order_date, 'YYYY-MM-DD HH24:MI:SS'), :order_amount, :customer_id, :product_id)
//             RETURNING Order_ID INTO :order_id";
//     $stmt = oci_parse($conn, $sql);
//     $order_date_str = $order_date->format('Y-m-d H:i:s');
//     oci_bind_by_name($stmt, ':order_date', $order_date_str);
//     oci_bind_by_name($stmt, ':order_amount', $order_amount);
//     oci_bind_by_name($stmt, ':customer_id', $customer_id);

//     oci_bind_by_name($stmt, ':order_id', $order_id, 10);
//     oci_execute($stmt);
//     oci_commit($conn);
//     return $order_id;
// }


// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $order_id = $_POST['order_id'];
//     $collection_slot = $_POST['collection_slot'];
//     list($collection_date, $collection_time) = explode(' ', $collection_slot);

//     // Get the day of the week from the collection date
//     $collection_day = date('l', strtotime($collection_date));

//     insertCollectionSlot($conn, $collection_day, $collection_date, $collection_time, $order_id);

//     echo "Collection slot assigned successfully!";
// }
// //Let's insert data into collection slot

// function insertCollectionSlot($conn, $collection_day, $collection_date, $collection_time, $order_id) {
//     $sql = "INSERT INTO Collection_Slot (Collection_Day, Collection_Date, Collection_Time, Order_ID) 
//             VALUES (:collection_day, TO_DATE(:collection_date, 'YYYY-MM-DD'), :collection_time, :order_id)";
//     $stmt = oci_parse($conn, $sql);
//     oci_bind_by_name($stmt, ':collection_day', $collection_day);
//     oci_bind_by_name($stmt, ':collection_date', $collection_date);
//     oci_bind_by_name($stmt, ':collection_time', $collection_time);
//     oci_bind_by_name($stmt, ':order_id', $order_id);
//     oci_execute($stmt);
//     oci_commit($conn);
    
// }


// try {
//     // Example usage
// $order_date = new DateTime(); // Captures the current date and time
// $order_id = insertOrder($conn, $order_date, $totalAmount, $customerID, $cartID); // Example order details
// $collection_slots = getAvailableCollectionSlots($conn, $order_date);

// oci_close($conn);
// } catch (\Throwable $th) {
//     $_SESSION['error'] = $th;
//     exit();
// }

oci_close($conn);
?>










<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./checkout.css"> 
    <link rel="stylesheet" href="../messages/notification.css">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
</head>
<body>
<div><?php include('../includes/head.php'); ?></div>
<div class="checkoutcontainer">
    <h1>Checkout</h1>
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
   
    <div class="left-container">
        <!-- Customer Details -->
        <div class="details-box">
            <h2>Customer Details</h2>
            <label for="full-name">Full Name</label>
            <input type="text" id="full-name" name="full-name" value="<?php echo $result['CUSTOMER_NAME'] ?>" readonly>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $result['EMAIL'] ?>" readonly>
        </div>
        <!-- Collection Slot -->
        <!-- <form action="process_order.php" method="POST"> 
            <div class="collection-slot">
                <h2>Collection Slot</h2>
                <label for="collection-slot">Select a Collection Slot</label>
                <select id="collection-slot" name="collection-slot" required>
                    <option value="" disabled selected>Select a collection slot</option>
                    <?php foreach ($collection_slots as $slot): ?>
                        <option value="<?php echo $slot['date'] . ' ' . $slot['time']; ?>"><?php echo $slot['day'] . ' ' . $slot['time']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form> -->
    

        <!-- Order Summary -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php foreach ($order_product as $row): ?>
                <div class="summary-item">
                    <span><?php echo $row['PRODUCT_NAME']; ?>:</span>
                    <span>$<?php echo $row['PRICE'] * $row['QUANTITY']; ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-item total">
                <span>Total:</span>
                <span>$<?php echo $totalRow['TOTAL_AMOUNT']; ?></span>
            </div>
        </div>
        
        <!-- Payment Option -->
        <div class="payment-details">
            <h2>Payment Option</h2>
            <div class="paypal-option">
                <input type="radio" id="paypal" name="payment-method" required value="paypal">
                <label for="paypal">
                    <img src="../assets/images/paypal.jpeg" alt="PayPal" class="paypallogo">
                </label>
            </div>
           
        </div>
        
        <a href="./collectionslot.php"><button type="submit">Place Order</button></a>
        
        
        <!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="buy" name="buy">
            <input type="hidden" name="business" value="sb-xqzgl30555991@business.example.com">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="amount" value="<?php echo $totalRow['TOTAL_AMOUNT']; ?>">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="return" value="http://localhost/order/addToorder.php?action=success&Total_amount=<?php echo $totalRow['TOTAL_AMOUNT'] ?>">
            <button type="submit">Place Order</button>
        </form> -->
    </div>
    
    </div>
<div><?php include('../includes/footer.php'); ?></div>
</body>
</html>
<?php
///////////////////////////SAIMAN KO CODE //////////////////////////////////

session_start();
include '../messages/notifications.php';

list($error,$notification)=flashNotification();
// Establish connection to the database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
}


// if(isset($_POST['submit']))
// {
//     $collection_day = $_POST['collection-day'];
//     $collection_time = $_POST['collection-time'];
//     $payment_method = $_POST[''];






// if(isset($_GET['Total_amount'])){
//     $amountPaid = $_GET['Total_amount'];

// }else{
//     $_SESSION['error'] = "Payment not done";
// }

// retrive cart id
$customerID = $_SESSION['user']['CUSTOMER_ID'];
$sql2= "SELECT * from CART WHERE CUSTOMER_ID = '$customerID'";
$statementcart = oci_parse($conn,$sql2);
$result = oci_execute($statementcart);
if(!$result){
    $_SESSION['errror'] = "Error in cart";
}
$row_cart = oci_fetch_assoc($statementcart);
$cartID = $row_cart['CART_ID'];
$amountPaid = $row_cart['TOTAL_AMOUNT'];

 echo $cartID;

// insert payment
$sqlpay = "INSERT INTO PAYMENT (AMOUNT,PAYMENT_METHOD,PAYMENT_DATE,CUSTOMER_ID) VALUES ('$amountPaid','Paypal',SYSDATE,'$customerID')";
$statement3 = oci_parse($conn,$sqlpay);
$result = oci_execute($statement3);
if(!$result){
    $_SESSION['error'] = "payment error";
}

$row_pay = oci_fetch_assoc($statement3);
// FETCH PAYMENT ID
$paymentId = $row_pay['PAYMENT_ID'];
 echo $paymentId;


//let's insert data into database for order
echo $amountPaid;



function insertOrder($conn, $order_date, $amountPaid, $customerID, $cartid,$collectionID) {
    $sql = "INSERT INTO Order_Detail (Order_Date, TOTAL_AMOUNT, Customer_ID, CART_ID,PAYMENT_ID,COLLECTION_ID) 
            VALUES (TO_DATE(:order_date, 'YYYY-MM-DD HH24:MI:SS'), :amount, :customer_id, :cartid,:paymentID,:collectionID)
            RETURNING Order_ID INTO :order_id";
    $stmt = oci_parse($conn, $sql);
    $order_date_str = $order_date->format('Y-m-d H:i:s');
    oci_bind_by_name($stmt, ':order_date', $order_date_str);
    oci_bind_by_name($stmt, ':amount', $amountPaid);
    oci_bind_by_name($stmt, ':customer_id', $customer_id);
    oci_bind_by_name($stmt, ':cartid', $product_id);
    oci_bind_by_name($stmt, ':collectionID', $collectionID);
    oci_bind_by_name($stmt, ':paymentID', $paymentId);
    oci_bind_by_name($stmt, ':order_id', $order_id);
    oci_execute($stmt);
    oci_commit($conn);
    return $order_id;
}

function insertCollectionSlot($conn, $collection_day, $collection_date, $collection_time, $customerID) {
    $sql = "INSERT INTO Collection_Slot (Collection_Day, Collection_Date, Collection_Time, CUSTOMER_REF) 
            VALUES (:collection_day, TO_DATE(:collection_date, 'YYYY-MM-DD'), :collection_time, :customer_ref)";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':collection_day', $collection_day);
    oci_bind_by_name($stmt, ':collection_date', $collection_date);
    oci_bind_by_name($stmt, ':collection_time', $collection_time);
    oci_bind_by_name($stmt, ':customer_ref', $customerID);
    oci_execute($stmt);
    oci_commit($conn);
}


//Let's insert data into collection slot

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $collection_slot = $_POST['collection_slot'];
    list($collection_date, $collection_time) = explode(' ', $collection_slot);

    // Get the day of the week from the collection date
    $collection_day = date('l', strtotime($collection_date));

    insertCollectionSlot($conn, $collection_day, $collection_date, $collection_time, $customerID);

    echo "Collection slot assigned successfully!";
}else{
    $_SESSION['error'] = "collected slot error";
}


try {
    // Example usage
$order_date = new DateTime(); // Captures the current date and time

$order_id = insertOrder($conn, $order_date, $totalAmount, $customerID, $cartID,$collection); // Example order details ?????????????????


oci_close($conn);
} catch (\Throwable $th) {
    $_SESSION['error'] = $th;
    oci_close($conn);
    exit();
}





?>
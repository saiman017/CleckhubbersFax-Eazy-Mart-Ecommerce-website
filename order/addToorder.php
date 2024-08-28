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

if (isset($_COOKIE['selectedSlot'])) {
    $selectedSlot = $_COOKIE['selectedSlot'];
    echo "Selected slot: " . $selectedSlot;
} else {
    echo "No selectedSlot data found in the cookie";
}

$customerID = $_SESSION['user']['CUSTOMER_ID'];

// Check if collection slot is provided
if ($selectedSlot) {
    list($collection_date, $collection_time) = explode(' ', $selectedSlot);

    // Get the day of the week from the collection date
    $collection_day = date('l', strtotime($collection_date));

    // Insert the collection slot
    $sqlInsertCollectionSlot = "INSERT INTO Collection_Slot (Collection_Day, Collection_Date, Collection_Time) 
                                VALUES (:collection_day, TO_DATE(:collection_date, 'YYYY-MM-DD'), :collection_time) 
                                RETURNING Collection_ID INTO :collection_id";
    $stmtInsertCollectionSlot = oci_parse($conn, $sqlInsertCollectionSlot);
    $collectionID = 0;
    oci_bind_by_name($stmtInsertCollectionSlot, ':collection_day', $collection_day);
    oci_bind_by_name($stmtInsertCollectionSlot, ':collection_date', $collection_date);
    oci_bind_by_name($stmtInsertCollectionSlot, ':collection_time', $collection_time);
    oci_bind_by_name($stmtInsertCollectionSlot, ':collection_id', $collectionID, -1, SQLT_INT);

    if (oci_execute($stmtInsertCollectionSlot)) {
        oci_commit($conn);
        echo "Collection slot assigned successfully!";
    } else {
        $e = oci_error($stmtInsertCollectionSlot);
        echo "Error inserting collection slot: " . $e['message'];
    }
    oci_free_statement($stmtInsertCollectionSlot);
} else {
    $_SESSION['error'] = "Collection slot not provided";
}

// Retrieve cart ID and amount paid
$sql2 = "SELECT * FROM CART WHERE CUSTOMER_ID = :customer_id";
$statementcart = oci_parse($conn, $sql2);
oci_bind_by_name($statementcart, ':customer_id', $customerID);
$result = oci_execute($statementcart);

if (!$result) {
    $_SESSION['error'] = "Error retrieving cart";
    exit;
}

$row_cart = oci_fetch_assoc($statementcart);
$cartID = $row_cart['CART_ID'];
$amountPaid = $row_cart['TOTAL_AMOUNT'];
oci_free_statement($statementcart);

// Insert payment
$sqlpay = "INSERT INTO PAYMENT (AMOUNT, PAYMENT_METHOD, PAYMENT_DATE, CUSTOMER_ID) 
           VALUES (:amount, 'Paypal', SYSDATE, :customer_id) 
           RETURNING PAYMENT_ID INTO :payment_id";
$statementpayment = oci_parse($conn, $sqlpay);
$paymentID = 0;
oci_bind_by_name($statementpayment, ":amount", $amountPaid);
oci_bind_by_name($statementpayment, ":customer_id", $customerID);
oci_bind_by_name($statementpayment, ":payment_id", $paymentID, -1, SQLT_INT);

if (oci_execute($statementpayment)) {
    oci_commit($conn);
    echo "Payment inserted successfully. Payment ID: " . $paymentID;
} else {
    $e = oci_error($statementpayment);
    echo "Error inserting payment: " . $e['message'];
    exit;
}
oci_free_statement($statementpayment);

// Insert order
function insertOrder($conn, $order_date, $amountPaid, $customerID, $cartID, $paymentID, $collectionID) {
    $sql = "INSERT INTO Order_Detail (Order_Date, TOTAL_AMOUNT, Customer_ID, CART_ID, PAYMENT_ID, COLLECTION_ID) 
            VALUES (TO_DATE(:order_date, 'YYYY-MM-DD HH24:MI:SS'), :amount, :customer_id, :cart_id, :payment_id, :collection_id)
            RETURNING Order_ID INTO :order_id";
    $stmt = oci_parse($conn, $sql);
    $order_date_str = $order_date->format('Y-m-d H:i:s');
    oci_bind_by_name($stmt, ':order_date', $order_date_str);
    oci_bind_by_name($stmt, ':amount', $amountPaid);
    oci_bind_by_name($stmt, ':customer_id', $customerID);
    oci_bind_by_name($stmt, ':cart_id', $cartID);
    oci_bind_by_name($stmt, ':payment_id', $paymentID);
    oci_bind_by_name($stmt, ':collection_id', $collectionID);
    $orderID = 0;
    oci_bind_by_name($stmt, ':order_id', $orderID, -1, SQLT_INT);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        return $orderID;
    } else {
        $e = oci_error($stmt);
        echo "Error inserting order: " . $e['message'];
        exit;
    }
    oci_free_statement($stmt);
}

try {
    $order_date = new DateTime(); // Captures the current date and time
    $orderID = insertOrder($conn, $order_date, $amountPaid, $customerID, $cartID, $paymentID, $collectionID);

    if ($orderID) {
        echo "Order inserted into database successfully. Order ID: " . $orderID;

        // Retrieve the cart items
        $sqlCartItems = "SELECT Product_ID, Quantity FROM Cart_Item WHERE Cart_ID = :cart_id";
        $stmtCartItems = oci_parse($conn, $sqlCartItems);
        oci_bind_by_name($stmtCartItems, ':cart_id', $cartID);
        oci_execute($stmtCartItems);
        
    // Insert cart items into Order_Product table
    while ($row = oci_fetch_assoc($stmtCartItems)) {
        $productID = $row['PRODUCT_ID'];
        $quantity = $row['QUANTITY'];

        // Insert cart item into Order_Product table
        $sqlInsertOrderProduct = "INSERT INTO Order_Product (Product_ID, Order_ID, Quantity) 
                                VALUES (:product_id, :order_id, :quantity)";
        $stmtInsertOrderProduct = oci_parse($conn, $sqlInsertOrderProduct);
        oci_bind_by_name($stmtInsertOrderProduct, ':product_id', $productID);
        oci_bind_by_name($stmtInsertOrderProduct, ':order_id', $orderID);
        oci_bind_by_name($stmtInsertOrderProduct, ':quantity', $quantity);

        if (oci_execute($stmtInsertOrderProduct)) {
            oci_commit($conn);
            echo "Cart item inserted into Order_Product table successfully.";
        } else {
            $e = oci_error($stmtInsertOrderProduct);
            echo "Error inserting cart item into Order_Product table: " . $e['message'];
        }
        oci_free_statement($stmtInsertOrderProduct);
    }

        // Update the product stock available
        
    }

    header('Location: ../Customer/customer_order.php');
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
} finally {


    while ($row = oci_fetch_assoc($stmtCartItems)) {
        $productID = $row['PRODUCT_ID'];
        $quantity = $row['QUANTITY'];

        $sqlUpdateProductStock = "UPDATE Product SET Stock_Available = Stock_Available - :quantity WHERE Product_ID = :product_id";
        $stmtUpdateProductStock = oci_parse($conn, $sqlUpdateProductStock);
        oci_bind_by_name($stmtUpdateProductStock, ':quantity', $quantity);
        oci_bind_by_name($stmtUpdateProductStock, ':product_id', $productID);

        if (oci_execute($stmtUpdateProductStock)) {
            oci_commit($conn);
            echo "Product stock updated successfully for product ID: " . $productID;
        } else {
            $e = oci_error($stmtUpdateProductStock);
            echo "Error updating product stock: " . $e['message'];
        }
        oci_free_statement($stmtUpdateProductStock);
    }
    oci_free_statement($stmtCartItems);
    // Clear cart items
    $sqlClearCartItems = "DELETE FROM Cart_Item WHERE Cart_ID = :cart_id";
    $stmtClearCartItems = oci_parse($conn, $sqlClearCartItems);
    oci_bind_by_name($stmtClearCartItems, ':cart_id', $cartID);

    if (oci_execute($stmtClearCartItems)) {
        oci_commit($conn);
        echo "Cart items cleared successfully.";
    } else {
        $e = oci_error($stmtClearCartItems);
        echo "Error clearing cart items: " . $e['message'];
    }
    oci_free_statement($stmtClearCartItems);
    oci_close($conn);
}
?>

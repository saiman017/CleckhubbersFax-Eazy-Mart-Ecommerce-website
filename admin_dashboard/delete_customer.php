<?php
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();



if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_GET['id'])) {
    $customerID = intval($_GET['id']); // Convert to integer
}

// Connect to the Oracle database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $e = oci_error();
    $_SESSION['error'] = "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    header("Location: ../admin_dashboard/manage_customer.php");
    exit;
}

try {
    // Start a transaction
    oci_execute(oci_parse($conn, 'BEGIN'), OCI_NO_AUTO_COMMIT);

    // Check for Wishlist records
    $sqlCheckWishlist = "SELECT COUNT(*) AS WISH_COUNT FROM Wishlist WHERE Customer_ID = :customer_id";
    $stidCheckWishlist = oci_parse($conn, $sqlCheckWishlist);
    oci_bind_by_name($stidCheckWishlist, ':customer_id', $customerID);
    oci_execute($stidCheckWishlist);
    $rowWishlist = oci_fetch_assoc($stidCheckWishlist);
    if ($rowWishlist !== false && $rowWishlist['WISH_COUNT'] > 0) {
        // Delete records from the Wishlist table
        $sqlWishlist = "DELETE FROM Wishlist WHERE Customer_ID = :customer_id";
        $stidWishlist = oci_parse($conn, $sqlWishlist);
        oci_bind_by_name($stidWishlist, ':customer_id', $customerID);
        if (!oci_execute($stidWishlist, OCI_NO_AUTO_COMMIT)) {
            throw new Exception("Error deleting from Wishlist");
        }
    }

    // Check for Order_Detail records
    $sqlCheckOrderDetail = "SELECT COUNT(*) AS ORDER_COUNT FROM Order_Detail WHERE Customer_ID = :customer_id";
    $stidCheckOrderDetail = oci_parse($conn, $sqlCheckOrderDetail);
    oci_bind_by_name($stidCheckOrderDetail, ':customer_id', $customerID);
    oci_execute($stidCheckOrderDetail);
    $rowOrderDetail = oci_fetch_assoc($stidCheckOrderDetail);
    if ($rowOrderDetail !== false && $rowOrderDetail['ORDER_COUNT'] > 0) {
        // Delete records from the Order_Detail table
        $sqlOrderDetail = "DELETE FROM Order_Detail WHERE Customer_ID = :customer_id";
        $stidOrderDetail = oci_parse($conn, $sqlOrderDetail);
        oci_bind_by_name($stidOrderDetail, ':customer_id', $customerID);
        if (!oci_execute($stidOrderDetail, OCI_NO_AUTO_COMMIT)) {
            throw new Exception("Error deleting from Order_Detail");
        }
    }

    // Delete the customer record from the Customer table
    $sqlCustomer = "DELETE FROM Customer WHERE Customer_ID = :customer_id";
    $stidCustomer = oci_parse($conn, $sqlCustomer);
    oci_bind_by_name($stidCustomer, ':customer_id', $customerID);
    if (!oci_execute($stidCustomer, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Customer");
    }

    // Commit the transaction
    oci_commit($conn);

    $_SESSION['notification'] =  "Customer and related records deleted successfully!";
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    oci_rollback($conn);
    $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
} finally {
    // Free the statement identifiers if they are initialized
    if (isset($stidCheckWishlist)) oci_free_statement($stidCheckWishlist);
    if (isset($stidWishlist)) oci_free_statement($stidWishlist);
    if (isset($stidCheckOrderDetail)) oci_free_statement($stidCheckOrderDetail);
    if (isset($stidOrderDetail)) oci_free_statement($stidOrderDetail);
    if (isset($stidCustomer)) oci_free_statement($stidCustomer);

    // Close the Oracle connection
    oci_close($conn);

    // Redirect back to admin dashboard
    header("Location: ../admin_dashboard/manage_customer.php");
    exit;
}
?>

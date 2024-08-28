<?php

require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['id'])) {
    $productID = intval($_GET['id']); // Convert to integer
}

// Connect to the Oracle database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $e = oci_error();
    $_SESSION['error'] = "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    header("Location: ../admin_dashboard/manageReview.php");
    exit;
}

try {
    // Start a transaction
    oci_execute(oci_parse($conn, 'BEGIN'), OCI_NO_AUTO_COMMIT);

    // Delete from Wishlist
    $sqlWishlist = "DELETE FROM Wishlist WHERE Product_ID = :product_id";
    $stidWishlist = oci_parse($conn, $sqlWishlist);
    oci_bind_by_name($stidWishlist, ':product_id', $productID);
    if (!oci_execute($stidWishlist, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Wishlist");
    }

    // Delete from Review
    $sqlReview = "DELETE FROM Review WHERE Product_ID = :product_id";
    $stidReview = oci_parse($conn, $sqlReview);
    oci_bind_by_name($stidReview, ':product_id', $productID);
    if (!oci_execute($stidReview, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Review");
    }

    // Delete from Cart_Item
    $sqlCartItem = "DELETE FROM Cart_Item WHERE Product_ID = :product_id";
    $stidCartItem = oci_parse($conn, $sqlCartItem);
    oci_bind_by_name($stidCartItem, ':product_id', $productID);
    if (!oci_execute($stidCartItem, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Cart_Item");
    }

    // Delete from Order_Product
    $sqlOrderProduct = "DELETE FROM Order_Product WHERE Product_ID = :product_id";
    $stidOrderProduct = oci_parse($conn, $sqlOrderProduct);
    oci_bind_by_name($stidOrderProduct, ':product_id', $productID);
    if (!oci_execute($stidOrderProduct, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Order_Product");
    }

    // Delete from Report
    $sqlReport = "DELETE FROM Report WHERE Product_ID = :product_id";
    $stidReport = oci_parse($conn, $sqlReport);
    oci_bind_by_name($stidReport, ':product_id', $productID);
    if (!oci_execute($stidReport, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Report");
    }

    // Finally, delete the product record from the Product table
    $sqlProduct = "DELETE FROM Product WHERE Product_ID = :product_id";
    $stidProduct = oci_parse($conn, $sqlProduct);
    oci_bind_by_name($stidProduct, ':product_id', $productID);
    if (!oci_execute($stidProduct, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Product");
    }

    // Commit the transaction
    oci_commit($conn);

    $_SESSION['success'] = "Product and related records deleted successfully!";
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    oci_rollback($conn);
    $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
} finally {
    // Free the statement identifiers if they are initialized
    if (isset($stidWishlist)) oci_free_statement($stidWishlist);
    if (isset($stidReview)) oci_free_statement($stidReview);
    if (isset($stidCartItem)) oci_free_statement($stidCartItem);
    if (isset($stidOrderProduct)) oci_free_statement($stidOrderProduct);
    if (isset($stidReport)) oci_free_statement($stidReport);
    if (isset($stidProduct)) oci_free_statement($stidProduct);

    // Close the Oracle connection
    oci_close($conn);

    // Redirect back to admin dashboard
    header("Location: ../admin_dashboard/manageReview.php");
    exit;
}
?>

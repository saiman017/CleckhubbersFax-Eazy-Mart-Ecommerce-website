<?php
// session_start();

// Include authentication middleware
require_once '../middlewares/checkAuthentication.php';
include '../messages/notifications.php';
// Check if the user is logged in
checkIfUserIsLoggedIn();


list($error,$notification)=flashNotification();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header("Location: error.php");
    exit();
}

if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    
    // Delete product from database
    $query = "DELETE FROM Product WHERE PRODUCT_ID = :product_id";
    $statement = oci_parse($conn, $query);
    oci_bind_by_name($statement, ':product_id', $productId);
    $result = oci_execute($statement);
    
    if ($result) {
        oci_commit($conn);
        $_SESSION['notification'] = "Product deleted successfully.";
        header("Location: view_product_detail.php");
        exit();
    } else {
        $error = oci_error($statement);
        $_SESSION['error'] = $error['message'];
        header("Location: view_product_detail.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Product ID not provided.";
    header("Location: view_product_detail.php");
    exit();
}



oci_close($conn);
?>

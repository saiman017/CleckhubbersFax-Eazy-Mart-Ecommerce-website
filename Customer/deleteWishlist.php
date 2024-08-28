<?php
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

if (isset($_GET['wishlistid'])) {
    $wishlistId = $_GET['wishlistid'];
    $customerId = $_SESSION['user']['CUSTOMER_ID'];

    // First, ensure that the wishlist item belongs to the logged-in user
    $queryCheck = "SELECT Customer_ID FROM Wishlist WHERE Wishlist_ID = :wishlistId";
    $stmtCheck = oci_parse($conn, $queryCheck);
    oci_bind_by_name($stmtCheck, ":wishlistId", $wishlistId);
    oci_execute($stmtCheck);

    $row = oci_fetch_assoc($stmtCheck);
    if ($row && $row['CUSTOMER_ID'] == $customerId) {
        // The item belongs to the user, proceed with deletion
        $queryDelete = "DELETE FROM Wishlist WHERE Wishlist_ID = :wishlistId";
        $stmtDelete = oci_parse($conn, $queryDelete);
        oci_bind_by_name($stmtDelete, ":wishlistId", $wishlistId);
        $result = oci_execute($stmtDelete);

        if ($result) {
            $_SESSION['notification'] = "Item removed from wishlist successfully.";
            header("Location: wishlist.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to remove item from wishlist.";
            header("Location: wishlist.php");
            exit();
        }
    } else {
        // Item not found or does not belong to user
        $_SESSION['error'] = "You do not have permission to delete this item or it does not exist.";
        header("Location: wishlist.php");
        exit();
    }
} else {
    // Wishlist ID not provided
    exit("Wishlist ID is not provided");
}

oci_close($conn);
?>

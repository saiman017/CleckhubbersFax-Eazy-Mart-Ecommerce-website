
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}


if (isset($_GET['id'])) {
    $productID = $_GET['id'];
} else {
    $_SESSION['error']="Product ID is not provided";
}


$customerid = $_SESSION['user']['CUSTOMER_ID'];
$queryCheck = "SELECT * FROM Wishlist WHERE Product_ID = '$productID' AND Customer_ID = '$customerid'";
$statement_check = oci_parse($conn, $queryCheck);
oci_execute($statement_check);
if (oci_fetch_assoc($statement_check)) {
    $_SESSION['error'] =  "Product is already in the wishlist";
    header('Location: ../Customer/Wishlist.php');
    exit();
} else {
    $queryInsert = "INSERT INTO Wishlist (Wishlist_Date, Product_ID, Customer_ID) 
                    VALUES (SYSDATE, '$productID', '$customerid')";
    $statement_insert = oci_parse($conn, $queryInsert);
    $result = oci_execute($statement_insert);
    if ($result) {
        $_SESSION['notification'] =  "Product added to wishlist successfully";
        
    } else {
        $_SESSION['error'] = 'Failed to add product to wishlist';
    }
    header("Location: wishlist.php");
    exit();
}

oci_close($conn);
?>

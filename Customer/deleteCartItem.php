<?php
require_once '../middlewares/checkAuthentication.php';
checkIfUserIsLoggedIn();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

if (isset($_POST['deleteCartItem'])) {
    $cartItemId = $_POST['cartItemId'];
    $query = "DELETE FROM Cart_Item WHERE Cart_Item_ID = '$cartItemId'";
    $statement = oci_parse($conn, $query);
    $result = oci_execute($statement);
    if (!$result) {
        $_SESSION['error'] = "Failed to delete the cart item.";
        exit();
    }

    // Redirect back to the cart page or refresh the current page
    header("Location: Customer_cart.php");
    exit();
}

oci_close($conn);
?>

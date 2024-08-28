<?php

// Check if the user is logged in
require_once '../middlewares/checkAuthentication.php';
checkIfUserIsLoggedIn();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header("Location: error.php");
    exit();
}

$customerId = $_SESSION['user']['CUSTOMER_ID'];

if (isset($_GET['id'])) {

    if (isset($_GET['quantity'])) {
        $quantity = $_GET['quantity'];
    } else {
        $quantity = 1;
    }
    $productID = $_GET['id'];

    // Sanitize input
    $customerId = htmlspecialchars($customerId);
    $productID = htmlspecialchars($productID);

    // Retrieve cart ID using customer ID
    $query = "SELECT * FROM CART WHERE CUSTOMER_ID = :customerId";
    $statement = oci_parse($conn, $query);
    oci_bind_by_name($statement, ':customerId', $customerId);
    oci_execute($statement);
    $row = oci_fetch_assoc($statement);

    if ($row) {
        $cartId = $row['CART_ID'];

        // Check if the number of cart items is less than 10
        $queryCheckItemCount = "SELECT COUNT(*) AS ITEM_COUNT FROM CART_ITEM WHERE CART_ID = :cartId";
        $statementCheckItemCount = oci_parse($conn, $queryCheckItemCount);
        oci_bind_by_name($statementCheckItemCount, ':cartId', $cartId);
        oci_execute($statementCheckItemCount);
        $rowCount = oci_fetch_assoc($statementCheckItemCount);

        if ($rowCount['ITEM_COUNT'] < 10) {
            // Check if the product is already in the cart
            $queryCheckProduct = "SELECT * FROM CART_ITEM WHERE CART_ID = :cartId AND PRODUCT_ID = :productId";
            $statementCheckProduct = oci_parse($conn, $queryCheckProduct);
            oci_bind_by_name($statementCheckProduct, ':cartId', $cartId);
            oci_bind_by_name($statementCheckProduct, ':productId', $productID);
            oci_execute($statementCheckProduct);
            $rowProduct = oci_fetch_assoc($statementCheckProduct);

            // Retrieve product's maximum order quantity
            $queryMaxOrder = "SELECT MAX_ORDER FROM PRODUCT WHERE PRODUCT_ID = :productId";
            $statementMaxOrder = oci_parse($conn, $queryMaxOrder);
            oci_bind_by_name($statementMaxOrder, ':productId', $productID);
            oci_execute($statementMaxOrder);
            $rowMaxOrder = oci_fetch_assoc($statementMaxOrder);
            $maxOrder = $rowMaxOrder['MAX_ORDER'];

            if ($rowProduct) {
                // Product exists in cart, update the quantity
                $newQuantity = $rowProduct['QUANTITY'] + $quantity;
                if ($newQuantity > $maxOrder) {
                    $newQuantity = $maxOrder;
                    $_SESSION['notification'] = "Product quantity updated to the maximum limit. Can't add more items.";
                } else {
                    $_SESSION['notification'] = "Product quantity updated successfully.";
                }

                $queryUpdateQuantity = "UPDATE CART_ITEM SET QUANTITY = :newQuantity WHERE CART_ID = :cartId AND PRODUCT_ID = :productId";
                $statementUpdateQuantity = oci_parse($conn, $queryUpdateQuantity);
                oci_bind_by_name($statementUpdateQuantity, ':newQuantity', $newQuantity);
                oci_bind_by_name($statementUpdateQuantity, ':cartId', $cartId);
                oci_bind_by_name($statementUpdateQuantity, ':productId', $productID);
                $success = oci_execute($statementUpdateQuantity);
            } else {
                // Product does not exist in cart, insert new row
                $newQuantity = $quantity;
                if ($newQuantity > $maxOrder) {
                    $newQuantity = $maxOrder;
                    $_SESSION['notification'] = "Product quantity added to the cart at the maximum limit. Can't add more items.";
                } else {
                    $_SESSION['notification'] = "Product added to cart successfully.";
                }

                $queryAddToCart = "INSERT INTO CART_ITEM (PRODUCT_ID, CART_ID, QUANTITY) VALUES (:productId, :cartId, :newQuantity)";
                $statementAddToCart = oci_parse($conn, $queryAddToCart);
                oci_bind_by_name($statementAddToCart, ':productId', $productID);
                oci_bind_by_name($statementAddToCart, ':cartId', $cartId);
                oci_bind_by_name($statementAddToCart, ':newQuantity', $newQuantity);
                $success = oci_execute($statementAddToCart);
            }

            if (!$success) {
                $_SESSION['error'] = "Failed to add product.";
            }
        } else {
            $_SESSION['error'] = "You can't add more than 10 items to your cart.";
        }
    } else {
        $_SESSION['error'] = "Cart not found for this customer.";
    }

    header("Location: Customer_cart.php");
    exit();
} else {
    $_SESSION['error'] = "Product id not found";
    exit();
}

oci_close($conn);
?>
<?php
require_once '../middlewares/checkAuthentication.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../messages/notifications.php';

list($error,$notification)=flashNotification();
// Check if the user is logged in
checkIfUserIsLoggedIn();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header("Location: error.php");
    exit();
}

$customerid = $_SESSION['user']['CUSTOMER_ID'];
$query1 = "SELECT 
            *
            FROM 
            Wishlist w
            JOIN 
            Product p ON w.Product_ID = p.Product_ID 
            WHERE CUSTOMER_ID='$customerid'";
$statement2 = oci_parse($conn, $query1);
oci_execute($statement2);

$wishlists = array();
while ($row = oci_fetch_assoc($statement2)) {
    $wishlists[] = $row;
}

oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../includes/sidebar.css">
    <link rel="stylesheet" href="../messages/notification.css">
</head>
<style>
    /* Sidebar Styles */
.sidebar {
    color: #000;
    padding: 20px;
    width: 250px;
    height: 80vh;
    position: relative;
    background-color: #fcfafa;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.sidebar a {
    color: #000;
    display: block;
    padding: 10px;
    text-decoration: none;
    transition: background 0.3s;
}

.sidebar a:hover {
    background-color: #666;
}

/* General Styles */
.container {
    display: flex;
}

.page-header {
    width: calc(100% - 250px); /* Subtract sidebar width from total width */
    padding: 0 20px;
}

.wishlist-content {
    margin-top: 20px;
}

/* Wishlist Item Styles */
.wishlist-item {
    background-color: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-radius: 0.375rem; /* 6px */
    padding: 1.5rem; /* 24px */
    margin-bottom: 1.5rem; /* 24px */
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wishlist-item img {
    width: 6rem; /* 96px */
    height: 6rem; /* 96px */
    border-radius: 0.375rem; /* 6px */
    margin-right: 1.5rem; /* 24px */
}

.wishlist-details {
    flex: 1;
}
.wishlist-item a{
    text-decoration: none;
    color: #000;
}

.wishlist-details {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wishlist-details .flex div {
    width: 25%; /* 1/4 of the container */
}

.wishlist-details p {
    margin: 0;
}

.wishlist-details p span {
    font-weight: 600;
}



</style>
<body>
    <header><?php include('../includes/head.php');?></header>
    <div class="container flex">
        <div class="sidebar">
            <a href="./customer_profile.php">My Profile</a>
            <a href="./customer_order.php">My Orders</a>
            <a href="./Wishlist.php">My Wishlist</a>
            <a href="./paymenthistory.php">Payment History</a>
        </div>

        <div id="wishlist" class="page-header w-full px-8">
       
            <h1 class="text-2xl font-bold mb-4 text-center">Wishlist</h1>
            <p class="text-gray-600 mb-8 text-center">Items you have added to your wishlist.</p>
            <?php if($error): ?>
                <div class="error-message" role="alert">
                <?php  echo $error; ?>
                </div>
                <?php endif; ?>
            <div class="wishlist-content">
            <?php foreach ($wishlists as $wishlist) { ?>
                
                <div class="wishlist-item bg-white shadow-md rounded-md p-6 mb-6 flex items-center justify-between">
                    <a href="../product/product_detail.php?productId=<?php echo $wishlist['PRODUCT_ID']; ?>">
                <?php if (!empty($wishlist['PRODUCT_IMAGE'])): ?>
                                <img src="../trader/upload/<?php echo $wishlist['PRODUCT_IMAGE']; ?>" alt="Product Image">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                    <div class="wishlist-details flex-1">
                        
                            <p class="text-gray-600">Product Name: <?php echo $wishlist['PRODUCT_NAME']; ?></p><br>
                            <p class="text-gray-600"><span class="font-semibold">Price: $</span> <?php echo $wishlist['PRICE']; ?></p>
                            <button class="text-gray-600 border border-gray-400 px-4 py-2 rounded-md hover:bg-gray-200"><a href="deleteWishlist.php?wishlistid=<?php echo $wishlist['WISHLIST_ID']; ?>">Remove</a></button></a>
                            <p class="text-gray-600"><span class="font-semibold"> Added date:</span><?php echo $wishlist['WISHLIST_DATE']; ?></p>
                            
                        </div>
                    </div>
                    </a>
                </div>
                
                <?php } ?>
            </div>
        </div>
    </div>
    <footer><?php include('../includes/footer.php');?></footer>
</body>
</html>

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

// Fetch order details
$customerID = $_SESSION['user']['CUSTOMER_ID'];
$sql = "SELECT * FROM Order_Detail WHERE Customer_ID = '$customerID' ORDER BY Order_Date DESC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

// Array to store ordered products
$orderedProducts = array();

while ($row = oci_fetch_assoc($stmt)) {
    $orderID = $row['ORDER_ID'];
    $orderDate = $row['ORDER_DATE'];
    $totalAmount = $row['TOTAL_AMOUNT'];
    $orderStatus = $row['ORDER_STATUS'];

    // Fetch ordered products for each order
    $sqlProducts = "SELECT p.Product_Name, p.Price, oi.Quantity 
                    FROM Order_PRODUCT oi 
                    JOIN Product p ON oi.Product_ID = p.Product_ID 
                    WHERE oi.Order_ID = '$orderID'";
    $stmtProducts = oci_parse($conn, $sqlProducts);
    oci_execute($stmtProducts);

    // Array to store products of each order
    $products = array();

    while ($productRow = oci_fetch_assoc($stmtProducts)) {
        $productName = $productRow['PRODUCT_NAME'];
        $price = $productRow['PRICE'];
        $quantity = $productRow['QUANTITY'];

        // Add product to the products array
        $products[] = array('name' => $productName, 'price' => $price, 'quantity' => $quantity);
    }

    // Add order details and products to the orderedProducts array
    $orderedProducts[] = array(
        'orderID' => $orderID,
        'orderDate' => $orderDate,
        'totalAmount' => $totalAmount,
        'orderStatus' => $orderStatus,
        'products' => $products
    );
}


oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="./customerorder.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>
    .product-header{
        display: flex;
        justify-content : space-between;
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
        <div id="orders" class="page-header w-full px-8">
            <h1 class="text-2xl font-bold mb-4 text-center">Order History</h1>
            <p class="text-gray-600 mb-8 text-center">View your past orders and order details.</p>
            <div class="profile-content cart-items">
                <!-- Order History Items -->
                <?php foreach ($orderedProducts as $order) { ?>
                    <div class="order-item bg-white shadow-md rounded-md p-6 mb-6 flex items-center justify-between">
                        <div class="order-details flex-1">
                            <div class="flex flex-col">
                                <div class="product-info">
                                    <h2 class="text-lg font-semibold">Order Details</h2>
                                    <p class="text-gray-600">Order Date: <?php echo $order['orderDate']; ?></p>
                                    <p class="text-gray-600">Total Amount: $<?php echo $order['totalAmount']; ?></p>
                                    <p class="text-gray-600">Order Status: <?php echo $order['orderStatus']; ?></p>
                                    <p class="text-gray-600"><span class="font-semibold">Order ID:</span> #<?php echo $order['orderID']; ?></p>
                                </div>
                            </div>
                        </div>
                        <button class="view-product-btn" data-products='<?php echo json_encode($order['products']); ?>'>View Product</button>
                    </div>  
                <?php } ?>
            </div>
        </div>
    </div>
    <div><?php include('../includes/footer.php');?></div>

<!-- Popup -->
<div id="popup" class="popup">
    <div class="popup-content">
        <span class="close">&times;</span>
        <h2>Product Summary</h2>
        <div class="product-list" id="product-list">
            <div class="product-header">
                <p><strong>Product Name</strong></p>
                <p><strong>Quantity</strong></p>
                <p><strong>Total Price</strong></p>
            </div>
        </div>
    </div>
</div>

<script>
var viewProductBtns = document.querySelectorAll('.view-product-btn');
var popup = document.getElementById('popup');
var productList = document.getElementById('product-list');
var closeBtn = document.querySelector('.close');

viewProductBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
        var products = JSON.parse(this.getAttribute('data-products'));
        showProductSummary(products);
    });
});

function showProductSummary(products) {
    productList.innerHTML = `
        <div class="product-header">
            <p><strong>Product Name</strong></p>
            <p><strong>Quantity</strong></p>
            <p><strong>Total Price</strong></p>
        </div>
    `;

   

    products.forEach(function(product) {
        var totalPrice = product.quantity * product.price;
        var productDiv = document.createElement('div');
        productDiv.classList.add('product');
        productDiv.innerHTML = `
            <p>${product.name}</p>
            <p>${product.quantity}</p>
            <p>${totalPrice}</p>
        `;
        productList.appendChild(productDiv);
    });

    popup.style.display = 'block';
}

closeBtn.addEventListener('click', function() {
    popup.style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target == popup) {
        popup.style.display = 'none';
    }
});
</script>


</body>
</html>
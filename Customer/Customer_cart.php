<?php
require_once '../middlewares/checkAuthentication.php';

checkIfUserIsLoggedIn();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

$customerid = $_SESSION['user']['CUSTOMER_ID'];

// Fetch Cart Items and Calculate Total Amount
$query = "SELECT 
    c.Cart_ID,
    ci.Quantity AS Cart_Item_Quantity,
    p.Price
FROM Cart c 
INNER JOIN Cart_Item ci ON c.Cart_ID = ci.Cart_ID 
INNER JOIN Product p ON ci.Product_ID = p.Product_ID 
WHERE c.Customer_ID = :customerid";

$statement = oci_parse($conn, $query);
oci_bind_by_name($statement, ":customerid", $customerid);
oci_execute($statement);

$totalAmount = 0;

while ($row = oci_fetch_assoc($statement)) {
    $totalAmount += $row['PRICE'] * $row['CART_ITEM_QUANTITY'];
}

// Update Total Amount in Cart
$updateQuery = "UPDATE Cart SET Total_Amount = '$totalAmount' WHERE Customer_ID = '$customerid'";
$updateStatement = oci_parse($conn, $updateQuery);
oci_execute($updateStatement);

$query = "SELECT 
    c.Cart_ID,
    c.Customer_ID,
    ci.Cart_Item_ID,
    ci.Quantity AS Cart_Item_Quantity,
    p.Product_ID,
    p.Product_Name,
    p.Price,
    p.Stock_Available,
    p.Product_Status,
    p.Min_Order,
    p.Max_Order,
    p.Allergy,
    p.Description,
    p.Product_Image,
    cat.Category_ID,
    cat.Category_Type
FROM Cart c 
INNER JOIN Cart_Item ci ON c.Cart_ID = ci.Cart_ID 
INNER JOIN Product p ON ci.Product_ID = p.Product_ID 
INNER JOIN Category cat ON p.Category_ID = cat.Category_ID
WHERE c.Customer_ID = '$customerid'";

$statement = oci_parse($conn, $query);
oci_execute($statement);

$carts = array();

while ($row = oci_fetch_assoc($statement)) {
    $carts[] = $row;
}

if (isset($_SESSION['user']['CUSTOMER_ID']) ){
    $customerID = $_SESSION['user']['CUSTOMER_ID'];
    $cartitemQuery = "SELECT 
                        ca.Cart_ID,
                        COUNT(ci.Cart_Item_ID) AS ITEM_COUNT
                    FROM 
                        Cart ca
                    LEFT JOIN 
                        Cart_Item ci ON ca.Cart_ID = ci.Cart_ID
                    WHERE 
                        ca.CUSTOMER_ID = :customerID
                    GROUP BY 
                        ca.Cart_ID";

    $statement2 = oci_parse($conn, $cartitemQuery);
    oci_bind_by_name($statement2, ":customerID", $customerID);
    oci_execute($statement2);

    $fetch = oci_fetch_assoc($statement2);   
    
    $_SESSION['Cart_Item'] = $fetch['ITEM_COUNT'];
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <!-- Include external CSS files -->
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../messages/notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./cart.css">
</head>
<body>
    <header><?php include('../includes/head.php');?></header>
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification-message" role="alert">
            <?php echo $_SESSION['notification']; unset($_SESSION['notification']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <div class="cart-header">
        <h1>Shopping Cart</h1>
        <p>View and manage items in your shopping cart.</p>
        
        <div id="cart" class="cart-container">
            <div class="cart-item-container">
                <!-- Cart Items -->
                <a href="#">
                <?php foreach ($carts as $cart) { ?>
                    <form method="post" action="deleteCartItem.php">
                        <div class="cart-item">
                            <?php if (!empty($cart['PRODUCT_IMAGE'])): ?>
                                <img src="../trader/upload/<?php echo $cart['PRODUCT_IMAGE']; ?>" alt="Product Image">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                            <div class="cart-item-detail">
                                <h6>Product Name</h6><p class="cart-item-name"><?php echo $cart['PRODUCT_NAME']?></p>
                            </div>
                            <div class="cart-item-detail">
                                <h6>Category</h6><p class="cart-item-category"><?php echo $cart['CATEGORY_TYPE']?></p>
                            </div>
                            <div class="cart-item-detail">
                                <h6>Price</h6><p class="cart-item-price"> $ <?php echo $cart['CART_ITEM_QUANTITY']*$cart['PRICE']?></p>
                            </div>
                            <div class="cart-item-actions">
                                <h6>Quantity</h6><input type="number" name="quantity" class="cart-item-quantity" value="<?php echo $cart['CART_ITEM_QUANTITY']; ?>" min="1">
                            </div>
                            <div class="cart-item-actions">
                                <input type="hidden" name="cartItemId" value="<?php echo $cart['CART_ITEM_ID']; ?>">
                                <button type="submit" name="deleteCartItem" class="cart-item-delete">Delete</button>
                            </div>
                        </div>
                    </form>
                <?php } ?>
                </a>
            </div>
            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="cart-summary-details">
                    <h2>Cart Summary</h2>
                    <div >
                        <p>Subtotal:<span class="price-detail">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$ <?php echo $totalAmount; ?></span> </p>
                    </div>
                    <div>
                        <p> Total:<span class="price-detail">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$ <?php echo $totalAmount; ?></span></p>
                    </div>
                </div>
                <?php
                // Check if the cart is empty
                if (empty($carts)) {
                    echo "<p>Your shopping cart is empty</p>";
                } else {
                    echo '<a href="../checkout/checkout.php"><button class="checkout-button">Checkout</button></a>';
                }
                ?>
            </div>
        </div>
    </div>
    <div><?php include('../includes/footer.php');?></div>
</body>
</html>


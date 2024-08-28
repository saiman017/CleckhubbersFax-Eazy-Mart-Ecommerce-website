<?php 
// Database connection
include '../messages/notifications.php';

list($error,$notification)=flashNotification();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

if (isset($_GET['shopId'])) {
    $shopID = $_GET['shopId'];
} else {
    $_SESSION['error'] = 'Shop iD is not provided';
    exit();
}

// for shop 
$queryShop = " SELECT * FROM Shop WHERE SHOP_ID = '$shopID'";
$statement_shop = oci_parse($conn, $queryShop);
oci_execute($statement_shop);
$row_shop = oci_fetch_assoc($statement_shop);


// retrive product information from shopid


$queryProduct = "SELECT * FROM 
Product WHERE SHOP_ID = '$shopID'";
$statement_product = oci_parse($conn, $queryProduct);
oci_execute($statement_product);

$products = array();
while ($row = oci_fetch_assoc($statement_product)) {
    $products[] = $row;
}
oci_close($conn);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Category Details</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../messages/notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        main { padding: 20px; }
        .category-details { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; }
        .category-image { flex: 0 0 40%; max-width: 40%; }
        .category-image img { display: block; max-width: 80%;width: 80%; height: 350px; border-radius: 10px; }
        .category-info { flex: 0 0 55%; max-width: 55%; }
        .category-info h2 { margin-top: 0; padding-bottom: 10px; font-size: 24px; color: #333; }
        .category-info p { margin-top: 0; padding-bottom: 10px; color: #666; }
        .category-info ul { margin: 0; padding: 0; list-style: none; color: #888; }
        .category-info li { padding: 5px 0; }
        .product-cards { display: flex; flex-wrap: wrap; overflow-x: auto; max-width: 100%; justify-content: space-between; }
        @media only screen and (max-width: 768px) {
            .category-details { flex-direction: column; align-items: center; }
            .category-info { max-width: 100%; margin-top: 20px; }
            .product-cards { flex-direction: column; align-items: center; flex-wrap: nowrap; overflow-x: hidden; overflow-y: auto; }
            .product-card { max-width: 90%; }
        }

        .product-card {
    flex: 0 1 calc(20% - 16px); 
    margin-bottom: 20px; 
    margin: 20px auto; 
    padding: 10px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    position: relative;
    max-width: 300px; 
    width: 100%; 
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
}

.product-card img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.product-card .price {
    font-weight: bold;
    color: #00b300;
    font-size: 16px;
    margin-bottom: 10px;
}

.product-card .cart-icon {
    position: absolute;
    bottom: 20px;
    right: 20px;
    color: #333;
    font-size: 20px;
    cursor: pointer;
    transition: transform 0.3s ease;
    border: 1px solid #333;
    border-radius: 50%;
    padding: 8px;
}

.product-card .cart-icon:hover {
    transform: scale(1.1);
    background-color: #333;
    color: #fff;
    border-color: transparent;
}

.product-card .stars {
    display: flex;
    margin: 10px 0;
    color: #fdd835;
}

.product-card .stars .fa-star {
    font-size: 16px;
    margin-right: 4px;
    color: #fdd835;
}

.product-card a {
    color: inherit; 
    text-decoration: none; 
    cursor: pointer; 
}

#feature-shop {
    text-align: center;
    padding: 50px 0;
}

#feature-shop #shop-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

#feature-shop .fe-box {
    width: 200px;
    margin: 20px;
    padding: 10px;
    background-color: #f5f5f5;
    border-radius: 8px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

#feature-shop .fe-box img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

#feature-shop .fe-box h4 {
    margin-top: 10px;
    font-size: 18px;
    color: #333;
}

#feature-shop .fe-box:hover {
    transform: translateY(-5px);
    box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
}

#feature-shop .shop-link {
    text-decoration: none;
    color: inherit;
}

.product-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 16px; /* Adding gap between the cards */
    justify-content: space-around; /* Adjusting the alignment */
    padding: 0 16px;
}

    </style>
</head>
<body>
    <div><?php include('../includes/head.php');?></div>
    <main>
        <section class="category-details">
            <div class="category-image">
            <?php if (!empty($row_shop['SHOP_IMAGE'])): ?>
                            <img src="../trader/upload/<?php echo $row_shop['SHOP_IMAGE'];?>" alt="" >
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
            </div>
            <div class="category-info">
                <h2><?php echo $row_shop['SHOP_NAME']; ?></h2>
                <ul>
                    <li><strong>Location :</strong> <?php echo $row_shop['SHOP_LOCATION']; ?></li><?php echo $row_shop['SHOP_IMAGE'];?>
                    <li><strong>Contact number :</strong> <?php echo $row_shop['CONTACT_NUMBER']; ?></li>
                    <li><strong>Email:</strong> <?php echo $row_shop['EMAIL']; ?></li>
                </ul>
            </div>
        </section>
        
        
<section id="feature-product2" class="section-p2">
    <section class="product-cards">
    <?php foreach ($products as $product) { ?>  
        <div class="product-card">
            <a href="../product/product_detail.php?productId=<?php echo $product['PRODUCT_ID']; ?>">
            <?php if (!empty($product['PRODUCT_IMAGE'])): ?>
                            <img src="../trader/upload/<?php echo $product['PRODUCT_IMAGE']; ?>" alt="product Image" >
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                <h3><?php echo $product['PRODUCT_NAME']; ?></h3>
                <p><?php echo $product['DESCRIPTION']; ?></p>
                <i class="fas fa-shopping-cart cart-icon"></i> <!-- Cart icon -->
                <span class="price">$<?php echo $product['PRICE']; ?></span>
            </a>
        </div>
        <?php } ?>
    </section>

  
    </main>
    <div><?php include('../includes/footer.php');?></div>
</body>
</html>

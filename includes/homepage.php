<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}


// include '../middlewares/checkRoles.php';

// checkUserRole('customer');
include '../messages/notifications.php';

list($error,$notification)=flashNotification();


$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header("Location: error.php");
    exit();
}


// feature product
$sql = "SELECT PRODUCT_NAME, PRICE,DESCRIPTION,  PRODUCT_IMAGE, PRODUCT_ID FROM (
  SELECT PRODUCT_NAME, PRICE,DESCRIPTION,  PRODUCT_IMAGE, PRODUCT_ID, ROW_NUMBER() OVER (ORDER BY PRODUCT_NAME) AS row_num FROM PRODUCT
) WHERE row_num <= 5";
$statement = oci_parse($conn, $sql);
oci_execute($statement);

$products = array();
while ($row = oci_fetch_assoc($statement)) {
    $products[] = $row;
}

// feature shop
$shopdetail = "SELECT SHOP_NAME, SHOP_LOCATION, CONTACT_NUMBER, EMAIL, SHOP_IMAGE, SHOP_ID
FROM (
  SELECT SHOP_NAME, SHOP_LOCATION, CONTACT_NUMBER, EMAIL, SHOP_IMAGE, SHOP_ID, 
         ROW_NUMBER() OVER (ORDER BY SHOP_NAME) AS row_num
  FROM SHOP
)
WHERE row_num <= 5";
$statement2 = oci_parse($conn, $shopdetail);
oci_execute($statement2);

$shopfeature = array();
while ($row = oci_fetch_assoc($statement2)) {
    $shopfeature[] = $row;
}


//new related product
$newRelated = "SELECT PRODUCT_NAME, PRICE, DESCRIPTION, PRODUCT_IMAGE, PRODUCT_ID
FROM (
  SELECT PRODUCT_NAME, PRICE, DESCRIPTION, PRODUCT_IMAGE, PRODUCT_ID, 
         ROW_NUMBER() OVER (ORDER BY PRODUCT_NAME DESC) AS row_num
  FROM PRODUCT)WHERE row_num <= 5";
  $statement1 = oci_parse($conn, $newRelated);
  oci_execute($statement1);
  $newproducts = array();
  while ($row = oci_fetch_assoc($statement1)) {
      $newproducts[] = $row;
  }


oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>Home page</title>
    <link rel="stylesheet" href="./footer.css">
    <link rel="stylesheet" href="./header.css">
    <link rel="stylesheet" href="./banner.css">
    <link rel="stylesheet" href="../messages/notification.css">
    <style>
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
    max-height: 190px;
    width: 100%;
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
  width: 100%;
  height: 150px;
  
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
    gap: 16px; 
    justify-content: space-around; 
    padding: 0 16px;
}


    </style>
</head>

</style>
<body>
<!-- <?php if($error): ?>
<div class="error-message" role="alert">
<?php  echo $error; ?>
</div>
<?php endif; ?>
<?php if($notification): ?>
<div class="notification-message" role="alert">
<?php  echo $error; ?>
</div>
<?php endif; ?> -->
<div><?php include('head.php'); ?></div>
    <section class="home">
  <div class="img">
    <img src="../assets/images/FriutBanner.jpg" alt="Fresh fruits">
  </div>
  <div class="content">
    <h1>
      <span>Fresh Grocery Products</span><br>
      Healthy <span id="span2">hygienic</span> 
    </h1>
    <p>Your Cleckhuddersfax Eazy Mart</p>
    <div class="btn"><button>Shop Now</button></div>
  </div>
</section>

  <section id="feature-shop" class="section-p1">
  <h2>Featured Shops</h2>
  <div id="shop-container" class="shop-container">
   <?php forEach($shopfeature as $shops) { ?>
    <a href="../Shop_category/shopProducts.php?shopId=<?php echo $shops['SHOP_ID']; ?>" class="shop-link">
      <div class="fe-box">
      <?php if (!empty($shop['SHOP_IMAGE'])): ?>
                            
                            <img src="../trader/upload/<?php echo $shops['SHOP_IMAGE'];?>" alt="" >
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
        <h4><?php echo $shops['SHOP_NAME']?></h4>
      </div>
    </a>
  <?php }?>
  </div>
</section>


<section id="feature-product2" class="section-p2">
  <h2>New Products</h2>
  
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
                <i class="fas fa-shopping-cart cart-icon"></i>
                <span class="price">$<?php echo $product['PRICE']; ?></span>
            </a>
        </div>
        <?php } ?>
    </section>
</section>
  
<section id="feature-product2" class="section-p2">
  <h2>Feature Products</h2>

  <section class="product-cards">
    <?php foreach ($newproducts as $product) { ?>  
        <div class="product-card">
        <a href="../product/product_detail.php?productId=<?php echo $product['PRODUCT_ID']; ?>">
        <?php if (!empty($product['PRODUCT_IMAGE'])): ?>
                      <img src="../trader/upload/<?php echo $product['PRODUCT_IMAGE']; ?>" alt="product Image" >
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                <h3><?php echo $product['PRODUCT_NAME']; ?></h3>
                <p><?php echo $product['DESCRIPTION']; ?></p>
                <i class="fas fa-shopping-cart cart-icon"></i>
                <span class="price">$<?php echo $product['PRICE']; ?></span>
            </a>
        </div>
        <?php } ?>
    </section>
</section>

<div><?php include('footer.php');?></div>
</body>
</html>



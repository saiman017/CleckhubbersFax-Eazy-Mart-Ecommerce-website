<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
}

// Fetch all shop names
$shopName = "SELECT * FROM SHOP";
$statement = oci_parse($conn, $shopName);
oci_execute($statement);

// Fetch products and store them in an array
$shops = array();
while ($row = oci_fetch_assoc($statement)) {
    $shops[] = $row;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" type="text/css" href="header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header>
        <nav id="up-nav">
            <div class="wrapper">
                <div class="logo"><a href="../index.php"><img src="../assets/images/icons/logo.png" alt="" id="logo"></a></div>
                <div class="search">
                    <form action="../search/search_product.php" method="GET">
                        <input type="text" placeholder="Search product..." name="search" required>
                        <button type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>
                <div class="nav-toggle">
                    <i class="fa-solid fa-bars fa-lg"></i>
                </div>
                <ul class="nav-link">
                    <li class="nav-link-item">
                        <?php if (!isset($_SESSION['user'])): ?>
                            <a href="../Login/customer_signin.php"><i class="fa-solid fa-arrow-right-to-bracket fa-lg"></i> Login <i class="fa-solid fa-angle-down"></i></a>
                        <?php else: ?>
                            <a href="../Login/customer_signin.php"><i class="fa-regular fa-user fa-lg"></i> Menu <i class="fa-solid fa-angle-down"></i></a>
                        <?php endif; ?>
                        <ul class="drop-menu">
                            <?php if(!isset($_SESSION['user'])): ?>
                                <li><a class="dropdown-item" href="../Sign Up/customer_signup.php"><span id="head-signup">New Customer?</span> Sign Up</a></li>
                                <li><a class="dropdown-item" href="../Login/customer_signin.php">Login</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="../Customer/customer_profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="../Customer/customer_order.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="../Customer/Wishlist.php">My Wishlist</a></li>
                                <li><a class="dropdown-item" href="../Authentication/logout.php">Logout</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <?php if(!isset($_SESSION['user'])): ?>
                        <li class="nav-link-item">
                            <a href="../Customer/Customer_cart.php">
                                <i class="fa-solid fa-cart-shopping fa-lg"></i> &nbsp;Cart 
                            </a>
                        </li>   
                    <?php else: ?>
                        <li class="nav-link-item">
                            <a href="../Customer/Customer_cart.php">
                                <i class="fa-solid fa-cart-shopping fa-lg"></i> &nbsp;Cart 
                                <?php 
                                    $items = $_SESSION['Cart_Item'];
                                    echo "<span id=\"cart_count\" class=\"cart-count\">$items</span>";
                                ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-link-item"><a href="../Sign Up/trader_signup.php"><i class="fa-solid fa-shop fa-lg"></i>&nbsp;Become a trader</a></li>
                </ul>
            </div>
        </nav>
        
        <nav id="down-nav">
            <div class="wrapper2">
                <ul class="nav-links">
                    <li><a href="../index.php">HOME</a></li>
                    <li><a href="../about us/about-us.php">ABOUT US</a></li>
                    <li>
                        <a href="../includes/homepage.php#feature-sho" id="topIcons">SHOP</a>
                        <ul class="drop-menu">
                            <?php foreach ($shops as $shop) { ?>
                                <li><a class="dropdown-item" href="../Shop_category/shopProducts.php?shopId=<?php echo $shop['SHOP_ID']; ?>"><?php echo $shop['SHOP_NAME']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li><a href="../contactUs/contactus.php">CONTACT US</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const navLinks = document.querySelector('.nav-link');
            const navLinks2 = document.querySelector('.nav-links');

            navToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                navLinks2.classList.toggle('active');
            });
        });
    </script>
</body>
</html>

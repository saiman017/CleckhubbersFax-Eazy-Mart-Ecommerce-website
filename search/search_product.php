<?php 

session_start();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

if(isset($_GET['search']) || isset($_GET['category']) || isset($_GET['price']) || isset($_GET['sort'])){
    $filtervalues = isset($_GET['search']) ? $_GET['search'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : 'all';
    $priceRange = isset($_GET['price']) ? $_GET['price'] : 'all';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';

    $query = "SELECT * FROM PRODUCT WHERE PRODUCT_NAME LIKE '%$filtervalues%'";

    if ($category != 'all') {
        $query .= " AND CATEGORY_ID = '$category'";
    }

    if ($priceRange != 'all') {
        $priceParts = explode('-', $priceRange);
        $minPrice = $priceParts[0];
        $maxPrice = $priceParts[1];
        $query .= " AND PRICE BETWEEN $minPrice AND $maxPrice";
    }

    if ($sort == 'price-low') {
        $query .= " ORDER BY PRICE ASC";
    } elseif ($sort == 'price-high') {
        $query .= " ORDER BY PRICE DESC";
    }

    $statement = oci_parse($conn, $query);
    oci_execute($statement);

    $pnames = array();
    while ($row = oci_fetch_assoc($statement)) {
        $pnames[] = $row;
    }
    if (empty($pnames)) {
        $_SESSION['message'] = "No results found.";
    }
} else {
    $_SESSION['error'] = "No filters applied.";
}

oci_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search Page</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../messages/notification.css">
    <style>
        /* Add your CSS styles here */
        .heading {
            background-color: #ccc;
            color: #333;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .heading h4 {
            margin: 0 20px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .filters {
            display: flex;
            align-items: center;
        }

        .filters select {
            margin-left: 10px;
            margin-right: 20px;
            padding: 8px;
            border: none;
            border-radius: 5px;
            background-color: #fff;
            font-size: 14px;
            color: #555;
            outline: none;
        }

        .main-content {
            display: flex;
            margin: 20px;
        }

        .sidebar {
            flex: 0 0 15%;
            background-color: #f9f9f9;
            padding: 20px;
            border-right: 2px solid #ccc;
            color: #333;
            border-radius: 10px;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
            text-transform: uppercase;
        }

        .filter-section {
            margin-bottom: 30px;
        }

        .filter-section h3 {
            margin-top: 0;
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
            text-transform: uppercase;
        }

        .sub-filters select {
            margin-top: 5px;
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 5px;
            background-color: #fff;
            font-size: 14px;
            color: #555;
            outline: none;
        }

        .product-card {
            flex: 0 0 calc(20% - 16px); 
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

        .no-results {
            text-align: center;
            font-size: 18px;
            color: #ff0000; 
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div><?php include('../includes/head.php');?></div>
    <div class="heading">
        <h4>Results</h4>
        <form method="GET" action="">
            <div class="filters">
                <div class="category-filter">
                    <label for="category">Category:</label>
                    <select id="category" name="category">
    <option value="all">All Categories</option>
    <?php
    // Fetch categories
    $query_categories = "SELECT * FROM Category";
    $statement_categories = oci_parse($conn, $query_categories);
    oci_execute($statement_categories);
    while ($row_category = oci_fetch_assoc($statement_categories)) {
        $selected = ($row_category['CATEGORY_ID'] == $category) ? 'selected' : '';
        echo '<option value="' . $row_category['CATEGORY_ID'] . '" ' . $selected . '>' . $row_category['CATEGORY_TYPE'] . '</option>';
    }
    ?>
</select>
</div>
<div class="sort-filter">
    <label for="sort">Sort By:</label>
    <select id="sort" name="sort">
        <option value="price-low" <?php if($sort == 'price-low') echo 'selected'; ?>>Price: Low to High</option>
        <option value="price-high" <?php if($sort == 'price-high') echo 'selected'; ?>>Price: High to Low</option>
    </select>
</div>
<button type="submit">Apply Filters</button>
</div>
</form>
</div>

<div class="main-content">
<aside class="sidebar">
    <h2>Filters</h2>
    <form method="GET" action="">
        <div class="filter-section">
            <h3>Price Range</h3>
            <div class="sub-filters">
                <select name="price">
                    <option value="all" <?php if($priceRange == 'all') echo 'selected'; ?>>All Prices</option>
                    <option value="0-10" <?php if($priceRange == '0-10') echo 'selected'; ?>>Rs.0 - Rs.10</option>
                    <option value="10-20" <?php if($priceRange == '10-20') echo 'selected'; ?>>Rs.10 - Rs.20</option>
                    <option value="20-30" <?php if($priceRange == '20-30') echo 'selected'; ?>>Rs.20 - Rs.30</option>
                </select>
            </div>
        </div>
        <button type="submit">Apply Filters</button>
    </form>
</aside>

<section id="feature-product2" class="section-p2">
    <section class="product-cards">
        <?php 
        if (isset($pnames) && !empty($pnames)) {
            foreach ($pnames as $product) { ?>  
                <div class="product-card">
                    <a href="../product/product_detail.php?productId=<?php echo $product['PRODUCT_ID']; ?>">
                        <?php if (!empty($product['PRODUCT_IMAGE'])): ?>
                            <img src="../trader/upload/<?php echo $product['PRODUCT_IMAGE']; ?>" alt="Product Image">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                        <h3><?php echo $product['PRODUCT_NAME']; ?></h3>
                        <p><?php echo $product['DESCRIPTION']; ?></p>
                        <i class="fas fa-shopping-cart cart-icon"></i>
                        <span class="price"><?php echo $product['PRICE']; ?></span>
                    </a>
                </div>
            <?php } 
        } else { ?>
            <p class="no-results">No results found.</p>
        <?php } ?>
    </section>
</section>
</div>

<div><?php include('../includes/footer.php');?></div>
</body>
</html>

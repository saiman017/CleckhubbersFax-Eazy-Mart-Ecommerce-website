<?php 
require_once '../middlewares/checkAuthentication.php';
include '../messages/notifications.php';

list($error, $notification) = flashNotification();

// Database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header('Location: ../errorPage.php'); // Redirect to a custom error page
    exit();
}

if (isset($_GET['productId'])) {
    $productID = $_GET['productId'];
} else {
    $_SESSION['error'] = "Product ID is not provided";
    header('Location: ../errorPage.php'); // Redirect to a custom error page
    exit();
}

// Fetch product details using a parameterized query
$queryProduct = "SELECT * FROM Product WHERE PRODUCT_ID = :productID";
$statement_product = oci_parse($conn, $queryProduct);
oci_bind_by_name($statement_product, ':productID', $productID);
oci_execute($statement_product);
$row_product = oci_fetch_assoc($statement_product);

// Fetch reviews
$queryReview = "SELECT
C.First_Name AS CustomerName,
R.Rating,
R.Review_Comment,
P.Product_Name
FROM
Customer C
JOIN
Review R ON C.Customer_ID = R.Customer_ID
JOIN
Product P ON R.Product_ID = P.Product_ID WHERE P.PRODUCT_ID = :productID";
$statementreview = oci_parse($conn, $queryReview);
oci_bind_by_name($statementreview, ':productID', $productID);
oci_execute($statementreview);

$reviews = array();
while($row_review = oci_fetch_assoc($statementreview)){
  $reviews[] = $row_review;
}

// Fetch similar products
$sql = "SELECT PRODUCT_NAME, PRICE, DESCRIPTION, PRODUCT_IMAGE, PRODUCT_ID FROM (
  SELECT PRODUCT_NAME, PRICE, DESCRIPTION, PRODUCT_IMAGE, PRODUCT_ID, ROW_NUMBER() OVER (ORDER BY PRODUCT_NAME) AS row_num FROM Product
) WHERE row_num <= 5 AND PRODUCT_ID != :productID";
$statement = oci_parse($conn, $sql);
oci_bind_by_name($statement, ':productID', $productID);
oci_execute($statement);

$products = array();
while ($row = oci_fetch_assoc($statement)) {
    $products[] = $row;
}

$sql = "SELECT SUM(rating) AS total_rating, COUNT(*) AS count_rating FROM REVIEW WHERE PRODUCT_ID = :productID";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':productID', $productID);

// Execute the query
if (!oci_execute($stid)) {
    $e = oci_error($stid);  // For oci_execute errors pass the statement handle
    echo htmlentities($e['message']);
    exit;
}

// Initialize variables
$rating = null;
$c = null;

// Fetch the results
if ($row = oci_fetch_assoc($stid)) {
    $c = $row["COUNT_RATING"];
    if ($c == 0) {
        $rating = 0;
    } else {
        $s = $row["TOTAL_RATING"];
        $rating = $s / $c;
        $rating = number_format($rating, 1);
    }
}

oci_close($conn);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Page</title>
  <link rel="stylesheet" href="../includes/style.css">
  <link rel="stylesheet" href="../includes/header.css">
  <link rel="stylesheet" href="../includes/footer.css">
  <link rel="stylesheet" href="./product_detail.css">
  <link rel="stylesheet" href="../messages/notification.css">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div><?php include_once '../includes/head.php'; ?></div>

<main>
<div class="h5"><?php echo $error; ?></div>
  <div class="product-container">
    <div class="product-img-container">
      <?php if (!empty($row_product['PRODUCT_IMAGE'])): ?>
        <img src="../trader/upload/<?php echo htmlspecialchars($row_product['PRODUCT_IMAGE']); ?>" alt="Product Image">
      <?php else: ?>
        No Image
      <?php endif; ?>
    </div>
    <div class="product-details-container">
      <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification-message" role="alert">
          <?php echo htmlspecialchars($_SESSION['notification']); unset($_SESSION['notification']); ?>
        </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message" role="alert">
          <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>
      <h2 class="product-title"><?php echo htmlspecialchars($row_product['PRODUCT_NAME']); ?></h2>
      <div class="product-rating">
      <p class="product-stock"> Rating: <?php echo "$rating/5"; ?><i class="fas fa-star"></i> <?php echo " ($c)"; ?></p>
        </div>
      
      <p class="product-price">$ &nbsp;<?php echo htmlspecialchars($row_product['PRICE']); ?></p>
      <p class="stock">Stock Available:  &nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($row_product['STOCK_AVAILABLE']); ?></p>
      <p class="stock"><?php echo htmlspecialchars($row_product['PRODUCT_STATUS']); ?></p>
      <p class="max-min">Min Order: <?php echo htmlspecialchars($row_product['MIN_ORDER']); ?> &nbsp;&nbsp;&nbsp;&nbsp; Max Order: <?php echo htmlspecialchars($row_product['MAX_ORDER']); ?></p>
      
      <!-- Quantity Selector -->
      <div class="quantity-selector">
        <label id="label" for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="1" min="<?php echo htmlspecialchars($row_product['MIN_ORDER']); ?>" max="<?php echo htmlspecialchars($row_product['MAX_ORDER']); ?>">
      </div>
      
      <!-- Add to Cart Button -->
      <a id="addToCartLink" href="../Customer/addTocart.php?id=<?php echo htmlspecialchars($row_product['PRODUCT_ID']); ?>&quantity=1"><button type="button" class="add-to-cart-btn">Add to Cart</button></a>
      <a href="../Customer/addWishlist.php?id=<?php echo htmlspecialchars($row_product['PRODUCT_ID']); ?>"><button class="add-to-cart-btn">Add to Wishlist</button></a>
      
      <!-- Product Description -->
      <p class="product-description"> 
        <h4>Description</h4>
        <?php echo htmlspecialchars($row_product['DESCRIPTION']); ?>
      </p>
      
      <!-- Allergy Information -->
      <p class="allergy-info">
        <h4>Allergy Information:</h4> 
        <?php echo htmlspecialchars($row_product['ALLERGY']); ?>
      </p>
    </div>
  </div>

  <div class="comments">
    <h3>Comments</h3>
    <?php foreach ($reviews as $review) { ?>
      <div class="comment">
        <p class="comment-text"><?php echo htmlspecialchars($review['REVIEW_COMMENT']); ?></p>
        <p class="comment-author">- <?php echo htmlspecialchars($review['CUSTOMERNAME']); ?></p>
        <div class="stars">
          <?php for ($i = 1; $i <= 5; $i++) {
              echo '<i class="fas fa-star' . ($i <= $review['RATING'] ? '' : '-half-alt') . '"></i>';
          } ?>
        </div>
      </div>
    <?php } ?>
  </div>
  <div class="comment-section">
      <form id="reviewForm" action="../review/submitReview.php" method="POST">
      <div class="rating">
          <span>Rate this product:</span>
          <div class="stars">
            <?php for ($i = 1; $i <= 5; $i++) { ?>
              <i class="far fa-star" data-value="<?php echo $i; ?>"></i>
            <?php } ?>
          </div>
          <input type="hidden" name="rating" id="rating-value" value="1">
        </div>
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productID); ?>">
        <textarea name="review_comment" placeholder="Leave a comment" required></textarea>
        
        <button type="submit">Submit Comment</button>
      </form>
    </div>
</main>

<section id="feature-product2" class="section-p2">
  <h2>New Products</h2>
  
  <section class="product-cards">
    <?php foreach ($products as $product) { ?>  
      <div class="product-card">
        <a href="../product/product_detail.php?productId=<?php echo htmlspecialchars($product['PRODUCT_ID']); ?>">
          <?php if (!empty($product['PRODUCT_IMAGE'])): ?>
            <img src="../trader/upload/<?php echo htmlspecialchars($product['PRODUCT_IMAGE']); ?>" alt="Product Image">
          <?php else: ?>
            No Image
          <?php endif; ?>
          <h3><?php echo htmlspecialchars($product['PRODUCT_NAME']); ?></h3>
          <p><?php echo htmlspecialchars($product['DESCRIPTION']); ?></p>
          <i class="fas fa-shopping-cart cart-icon"></i>
          <span class="price">$<?php echo htmlspecialchars($product['PRICE']); ?></span>
        </a>
      </div>
    <?php } ?>
  </section>
</section>

<div><?php include_once '../includes/footer.php'; ?></div>

<script>
  document.getElementById('quantity').addEventListener('input', function() {
    const quantityInput = this;
    const minOrder = <?php echo htmlspecialchars($row_product['MIN_ORDER']); ?>;
    const maxOrder = <?php echo htmlspecialchars($row_product['MAX_ORDER']); ?>;
    let quantity = parseInt(quantityInput.value, 10);

    if (quantity < minOrder) {
      quantity = minOrder;
    } else if (quantity > maxOrder) {
      quantity = maxOrder;
    }

    quantityInput.value = quantity;

    const addToCartLink = document.getElementById('addToCartLink');
    const productId = "<?php echo htmlspecialchars($row_product['PRODUCT_ID']); ?>";
    addToCartLink.href = `../Customer/addTocart.php?id=${productId}&quantity=${quantity}`;
  });

  // JavaScript for star rating
  const ratingIcons = document.querySelectorAll('.rating .stars .fa-star');

  ratingIcons.forEach(icon => {
    icon.addEventListener('click', () => {
      const ratingValue = icon.getAttribute('data-value');
      document.getElementById('rating-value').value = ratingValue;
      ratingIcons.forEach((icon, index) => {
        icon.classList.toggle('fas', index < ratingValue);
        icon.classList.toggle('far', index >= ratingValue);
      });
    });
  });

  // Handle review form submission with AJAX
  document.getElementById('reviewForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch(this.action, {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(data => {
      // Optionally handle the response from the server
      location.reload(); // Reload the page to see the new review
    })
    .catch(error => console.error('Error:', error));
  });
</script>

</body>
</html>

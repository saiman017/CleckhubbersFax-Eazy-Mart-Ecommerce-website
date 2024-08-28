<?php
session_start();

require_once '../middlewares/checkAuthentication.php';
checkIfUserIsLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
    if (!$conn) {
        $_SESSION['error'] = "Failed to connect to the database.";
        exit();
    }

    $productID = $_POST['product_id'];
    $customerID = $_SESSION['user']['CUSTOMER_ID'];
    $rating = $_POST['rating'];
    $reviewComment = $_POST['review_comment'];

    // Insert the review into the database
    $query = "INSERT INTO Review (Product_ID, Customer_ID, Rating, Review_Comment,REVIEW_DATE) VALUES ('$productID', '$customerID', '$rating', '$reviewComment',SYSDATE)";
    $statement = oci_parse($conn, $query);

    if (oci_execute($statement)) {
        $_SESSION['notification'] = "Review submitted successfully!";
       
    } else {
        $_SESSION['error'] = "Failed to submit the review.";
    }
   

    oci_close($conn);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>

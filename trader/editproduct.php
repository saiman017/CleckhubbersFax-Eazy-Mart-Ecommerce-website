<?php
require_once '../middlewares/checkAuthentication.php';
include '../middlewares/checkRoles.php';
include '../middlewares/traderApproval.php';
include '../messages/notifications.php';

list($error, $notification) = flashNotification();

checkIfUserIsLoggedIn();
checkUserRole('trader');
checkTraderApproval();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $_SESSION['error'] = "Failed to connect to the database.";
    header("Location: trader_dashboard.php");
    exit();
}

$traderId = $_SESSION['user']['TRADER_ID'];
$query_shop = "SELECT * FROM Shop WHERE Trader_id = :traderId";
$statement_shop = oci_parse($conn, $query_shop);
oci_bind_by_name($statement_shop, ':traderId', $traderId);
oci_execute($statement_shop);
$row_shop = oci_fetch_assoc($statement_shop);

if (!$row_shop) {
    $_SESSION['error'] = "Shop details not found.";
    header("Location: trader_dashboard.php");
    exit();
}

$shopId = $row_shop['SHOP_ID'];

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $query_product = "SELECT * FROM Product WHERE PRODUCT_ID = :productId AND SHOP_ID = :shopId";
    $statement_product = oci_parse($conn, $query_product);
    oci_bind_by_name($statement_product, ':productId', $productId);
    oci_bind_by_name($statement_product, ':shopId', $shopId);
    oci_execute($statement_product);
    $product = oci_fetch_assoc($statement_product);

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: view_product_detail.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: view_product_detail.php");
    exit();
}

if (isset($_POST['submit'])) {
    $productName = $_POST['productName'];
    $productDescription = $_POST['productDes'];
    $productPrice = $_POST['productPrice'];
    $productStock = $_POST['productStock'];
    $minOrder = $_POST['minOrder'];
    $maxOrder = $_POST['maxOrder'];
    $allergy = $_POST['allergy'];
    $categories = $_POST['categories'];

    $productPhoto = $_FILES['oldPhoto']['name'];
    $allowed_extension = array('gif', 'png', 'jpg', 'jpeg');
    $file_extension = pathinfo($productPhoto, PATHINFO_EXTENSION);

    if ($productPhoto && !in_array($file_extension, $allowed_extension)) {
        $_SESSION['notification'] = "You are allowed with only jpg, png, jpeg, and gif";
        header('Location: view_product_detail.php?id=' . $productId);
        exit();
    }

    if ($productPhoto && file_exists("upload/" . $productPhoto)) {
        $_SESSION['error'] = "Image already exists: " . $productPhoto;
        header("Location: view_product_detail.php?id=" . $productId);
        exit();
    } else {
        if ($productPhoto) {
            if (move_uploaded_file($_FILES["oldPhoto"]["tmp_name"], "upload/" . $productPhoto)) {
                $productPhotoQuery = ", PRODUCT_IMAGE = :productPhoto";
            } else {
                $_SESSION['error'] = "Failed to upload product photo.";
                header("Location: view_product_detail.php?id=" . $productId);
                exit();
            }
        } else {
            $productPhotoQuery = "";
        }

        $query = "UPDATE Product SET 
                  PRODUCT_NAME = :productName,
                  DESCRIPTION = :productDescription,
                  PRICE = :productPrice,
                  STOCK_AVAILABLE = :productStock,
                  MIN_ORDER = :minOrder,
                  MAX_ORDER = :maxOrder,
                  ALLERGY = :allergy
                  $productPhotoQuery
                  WHERE PRODUCT_ID = :productId AND SHOP_ID = :shopId";

        $statement = oci_parse($conn, $query);

        oci_bind_by_name($statement, ':productName', $productName);
        oci_bind_by_name($statement, ':productDescription', $productDescription);
        oci_bind_by_name($statement, ':productPrice', $productPrice);
        oci_bind_by_name($statement, ':productStock', $productStock);
        oci_bind_by_name($statement, ':minOrder', $minOrder);
        oci_bind_by_name($statement, ':maxOrder', $maxOrder);
        oci_bind_by_name($statement, ':allergy', $allergy);
        oci_bind_by_name($statement, ':productId', $productId);
        oci_bind_by_name($statement, ':shopId', $shopId);

        if ($productPhoto) {
            oci_bind_by_name($statement, ':productPhoto', $productPhoto);
        }

        $result = oci_execute($statement);

        if ($result) {
            oci_commit($conn);
            $_SESSION['notification'] = "Product updated successfully.";
        } else {
            $error = oci_error($statement);
            $_SESSION['error'] = $error['message'];
        }

        header("Location: view_product_detail.php?id=" . $productId);
        exit();
    }
}

oci_close($conn);
?>

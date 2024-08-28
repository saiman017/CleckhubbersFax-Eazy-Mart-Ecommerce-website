<?php
require_once '../middlewares/checkAuthentication.php';
include '../messages/notifications.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

list($error, $notification) = flashNotification();

// Establish database connection
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit();
}

if(isset($_POST['add-shop'])) {
    // Retrieve form data
    $email = htmlspecialchars($_POST['shopEmail'], ENT_QUOTES, 'UTF-8');
    $shopName = htmlspecialchars($_POST['shopName'], ENT_QUOTES, 'UTF-8');
    $number = htmlspecialchars($_POST['shopContactNumber'], ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
    $shop_image = $_FILES['shopImage']['name']; // Retrieve the uploaded file name

    // Check if email, shop name or contact number already exists
    $query_check = "SELECT EMAIL, SHOP_NAME, CONTACT_NUMBER FROM Shop WHERE EMAIL = '$email' OR SHOP_NAME = '$shopName' OR CONTACT_NUMBER = '$number'";
    $statement_check = oci_parse($conn, $query_check);
    oci_execute($statement_check);
    $row = oci_fetch_assoc($statement_check);

    if ($row !== false) {
        $message = "Email, Shop name, or contact number already exists. Please use different ones.";
        if ($email === $row['EMAIL']) {
            $message = "Email already exists. Please use a different email.";
        } elseif ($shopName === $row['SHOP_NAME']) {
            $message = "Shop name already exists. Please use a different shop name.";
        } elseif ($number === $row['CONTACT_NUMBER']) {
            $message = "Contact number already exists. Please use a different one.";
        }
        $_SESSION['error'] = $message;
        header("Location: add_shop_after_signup.php");
        exit();
    }

    // Check file extension and upload the image
    $allowed_extension = array('gif', 'png', 'jpg', 'jpeg');
    $file_extension = pathinfo($shop_image, PATHINFO_EXTENSION);
    if (!in_array($file_extension, $allowed_extension)) {
        $_SESSION['error'] = "You are allowed with only jpg, png, jpeg, and gif";
        header('Location: add_shop_after_signup.php');
        exit();
    } else {
        if (file_exists("upload/" . $shop_image)) {
            $_SESSION['error'] = "Image already exists: " . $shop_image;
            header("Location: add_shop_after_signup.php");
            exit();
        } else {
            // Insert shop details into the database
            $traderID = $_SESSION['user']['TRADER_ID'];
            $query = "INSERT INTO Shop (SHOP_NAME, EMAIL, SHOP_LOCATION, CONTACT_NUMBER, TRADER_ID, SHOP_IMAGE) 
                      VALUES ('$shopName', '$email', '$location', '$number', '$traderID', '$shop_image')";
            $statement = oci_parse($conn, $query);
            $result = oci_execute($statement);

            if ($result) {
                oci_commit($conn);
                move_uploaded_file($_FILES['shopImage']['tmp_name'], "upload/" . $shop_image);
                header("Location: trader_dashboard.php");
                exit();
            } else {
                $error = oci_error($statement);
                $_SESSION['error'] = $error['message']; // Display Oracle error message
                header("Location: add_shop_after_signup.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trader Signup</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../messages/notification.css">
</head>
<body>
    <!-- Shop details form -->
    <div class="add-shop">
        <div class="right-side" id="shopDetailsForm">
            <a href="./trader_dashboard.php" class="logo-link"><img src="../assets/images/icons/logo.png" alt="" class="logo"></a>
            <h2>Shop Details</h2>
            <?php if($error): ?>
            <div class="error-message" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            <form class="shop-form" method="POST" id="shopForm" enctype="multipart/form-data">
                <span>Shop Name</span>
                <input type="text" id="shopName" name="shopName" placeholder="Shop Name" class="input-field">
                <span>Location</span>
                <input type="text" id="location" name="location" placeholder="Location" class="input-field">
                <span>Email</span>
                <input type="email" id="shopEmail" name="shopEmail" placeholder="Email" class="input-field">
                <span>Contact Number</span>
                <input type="tel" id="shopContactNumber" name="shopContactNumber" placeholder="Contact Number" class="input-field">
                <span>Shop Image</span>
                <input type="file" id="shopImage" name="shopImage" class="input-field">
                <button type="submit" name="add-shop" class="submit-btn">Next</button>
            </form>
        </div>
    </div>
</body>
</html>

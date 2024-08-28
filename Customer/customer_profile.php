<?php
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit();
} 

if (isset($_SESSION['user']['EMAIL'])) {
    $userEmail = $_SESSION['user']['EMAIL'];
} else {
    $_SESSION['error'] = "User not found";
    header("Location: ../Login/customer_signin.php");
    exit();
}

$query = "SELECT * FROM Customer WHERE Email = :email";
$statement = oci_parse($conn, $query);
oci_bind_by_name($statement, ":email", $userEmail);
oci_execute($statement);
// Fetch the user record
$fetch = oci_fetch_assoc($statement);

if (isset($_POST['saveProfile'])) {
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $customerId = $_SESSION['user']['CUSTOMER_ID'];
    $number = $_POST['contact_number'];
    $address = $_POST['address'];
    $Uname = $_POST['username'];
    $profileImage = $_FILES['image']['name'];
    $allowed_extension = array('gif', 'png', 'jpg', 'jpeg');
    $file_extension = pathinfo($profileImage, PATHINFO_EXTENSION);

    if ($profileImage && !in_array($file_extension, $allowed_extension)) {
        $_SESSION['status'] = "You are allowed with only jpg, png, jpeg, and gif";
        header('Location: customer_profile.php');
        exit();
    }

    if ($profileImage) {
        $uploadDir = 'profileImage/';
        $uploadFile = $uploadDir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadFile)) {
            $productPhotoQuery = ", PROFILE_IMAGE = :profile_image";
        } else {
            $_SESSION['error'] = "Failed to upload photo.";
            header("Location: customer_profile.php");
            exit();
        }
    } else {
        $productPhotoQuery = "";
    }

    $query = "UPDATE Customer 
              SET FIRST_NAME = :first_name, LAST_NAME = :last_name, ADDRESS = :address, USERNAME = :username, CONTACT_NUMBER = :contact_number $productPhotoQuery 
              WHERE CUSTOMER_ID = :customer_id";
    $statement = oci_parse($conn, $query);
    oci_bind_by_name($statement, ":first_name", $first_name);
    oci_bind_by_name($statement, ":last_name", $last_name);
    oci_bind_by_name($statement, ":address", $address);
    oci_bind_by_name($statement, ":username", $Uname);
    oci_bind_by_name($statement, ":contact_number", $number);
    oci_bind_by_name($statement, ":customer_id", $customerId);
    if ($profileImage) {
        oci_bind_by_name($statement, ":profile_image", $profileImage);
    }

    $result = oci_execute($statement);

    if ($result) {
        oci_commit($conn);
        $_SESSION['success'] = "Profile updated successfully.";
    } else {
        $error = oci_error($statement);
        $_SESSION['error'] = "Failed to update profile.";
    }

    header("Location: customer_profile.php");
    exit();
}

oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header><?php include('../includes/head.php'); ?></header>
    <div class="customer-profile">
        <div class="container">
            <div class="sidebar">
                <a href="./customer_profile.php">My Profile</a>
                <a href="./customer_order.php">My Orders</a>
                <a href="./Wishlist.php">My Wishlist</a>
                <a href="./paymenthistory.php">Payment History</a>
            </div>
            <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <div class="main-content">
                <div class="profile" id="profile">
                    <div class="profile-header">
                        <h1>Customer Profile</h1>
                        <p>Welcome back, <?php echo $fetch['FIRST_NAME']; ?></p>
                        <div class="profile-picture">
                        <?php if (!empty($fetch['PROFILE_IMAGE'])): ?>
                            <img src="profileImage/<?php echo htmlspecialchars($fetch['PROFILE_IMAGE']); ?>" alt="Customer Image" class="w-20 h-20 object-cover">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
    
                        
                    </div>
                    <div class="profile-content" id="profile-content">
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="first-name">First Name</label>
                                <input id="first-name" type="text" name="first_name" value="<?php echo $fetch['FIRST_NAME']; ?>" readonly>
                            </div>
                            <div class="profile-field">
                                <label for="last-name">Last Name</label>
                                <input id="last-name" type="text" name="last_name" value="<?php echo $fetch['LAST_NAME']; ?>" readonly>
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="address">Address</label>
                                <input id="address" type="text" name="address" value="<?php echo $fetch['ADDRESS']; ?>" readonly>
                            </div>
                            <div class="profile-field">
                                <label for="number">Contact Number</label>
                                <input id="number" type="number" name="contact_number" value="<?php echo $fetch['CONTACT_NUMBER']; ?>" readonly>
                            </div>
                        </div>
                        <button id="edit-profile">Edit Profile</button>
                        <button id="change-password">Change Password</button>
                    </div>
                    <div class="profile-content" id="edit-profile-content" style="display: none;">
                        <!-- Edit profile form -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="profile-row">
                                <div class="profile-field">
                                    <label for="first-name">First Name</label>
                                    <input id="first-name" type="text" name="first_name" value="<?php echo $fetch['FIRST_NAME']; ?>">
                                </div>
                                <div class="profile-field">
                                    <label for="last-name">Last Name</label>
                                    <input id="last-name" type="text" name="last_name" value="<?php echo $fetch['LAST_NAME']; ?>">
                                </div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-field">
                                    <label for="address">Address</label>
                                    <input id="address" type="text" name="address" value="<?php echo $fetch['ADDRESS']; ?>">
                                </div>
                                <div class="profile-field">
                                    <label for="number">Contact Number</label>
                                    <input id="number" type="number" name="contact_number" value="<?php echo $fetch['CONTACT_NUMBER']; ?>">
                                </div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-field">
                                    <label for="image">Profile</label>
                                    <input id="image" type="File" name="image" accept="image/*" >
                                </div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-field">
                                    <label for="username">Username</label>
                                    <input id="username" type="text" name="username" value="<?php echo $fetch['USERNAME']; ?>">
                                </div>
                            </div>
                            <div class="profile-row">
                                <button name="saveProfile" id="save-profile">Save Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div><?php include('../includes/footer.php'); ?></div>
    <script>
        document.getElementById('edit-profile').addEventListener('click', function() {
            document.getElementById('profile-content').style.display = 'none';
            document.getElementById('edit-profile-content').style.display = 'block';
        });


        document.getElementById('change-password').addEventListener('click', function() {
            window.location.href = 'change_password.php'; // Redirect to change password page
        });

    </script>
</body>
</html>

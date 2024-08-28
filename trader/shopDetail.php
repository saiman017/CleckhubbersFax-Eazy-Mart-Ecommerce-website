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
$query_shop = "SELECT * FROM Shop WHERE Trader_id = '$traderId'";
$statement_shop = oci_parse($conn, $query_shop);
oci_execute($statement_shop);
$row_shop = oci_fetch_assoc($statement_shop);

if (!$row_shop) {
    $_SESSION['error'] = "Shop details not found.";
    header("Location: trader_dashboard.php");
    exit();
}

$shopId = $row_shop['SHOP_ID'];

if (isset($_POST['submit'])) {
    // Retrieve form data
    $shopName = $_POST['shopname'];
    $shopLocation = $_POST['location'];
    $email = $_POST['email'];
    $number = $_POST['contact_number'];
    $shopImage = $_FILES['shopImage']['name']; // Change here to match your form field name

    // Check if an image is uploaded
    if ($shopImage) {
        // Move the uploaded image to the desired location
        if (move_uploaded_file($_FILES["shopImage"]["tmp_name"], "upload/" . $shopImage)) {
            $shopImageQuery = ", SHOP_IMAGE = '$shopImage'"; // Include in the query if an image is uploaded
        } else {
            $_SESSION['error'] = "Failed to upload shop image.";
            header("Location: shopDetail.php");
            exit();
        }
    } else {
        $shopImageQuery = ""; // If no image is uploaded, don't include in the query
    }

    // SQL query to update shop details
    $query = "UPDATE Shop 
              SET Shop_Name = '$shopName', Shop_Location = '$shopLocation', Contact_Number = '$number', Email = '$email' $shopImageQuery
              WHERE Trader_ID = '$traderId'";

    $statement = oci_parse($conn, $query);

    $result = oci_execute($statement);

    if ($result) {
        oci_commit($conn);
        $_SESSION['notification'] = "Shop details updated successfully.";
    } else {
        $error = oci_error($statement);
        $_SESSION['error'] = $error['message'];
    }

    header("Location: shopDetail.php");
    exit();
}

$query = "SELECT * FROM SHOP WHERE TRADER_ID = '$traderId'";
$statement = oci_parse($conn, $query);
oci_execute($statement);
$fetch = oci_fetch_assoc($statement);

oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Trader Profile</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="trader.css">
    <link rel="stylesheet" href="../messages/notification.css">
    
  </head>
  <style>
    /* customer profile */
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  outline: none;
  font-family: "Poppins", sans-serif;

}

body {
  width: 100%;
  background-color: white;
  overflow-x: hidden;
  margin: 0;
  min-width: 700;
  
}


.trader-profile .container {
  display: flex;
  padding-bottom: 10px;
  margin-bottom: 20px;
  margin-top: 30px;
}

.trader-profile  .main-content {
  margin-left: 50px;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 20px;
}

.trader-profile  .profile {
  background-color: #f9f9f9;
  border-radius: 20px;
  box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
  padding: 40px;
  max-width: 800px;
  width: 100%;
  /* position: relative;  */
}

.trader-profile  .profile-row {
  display: flex;
  justify-content: space-between; 
  margin-bottom: 20px; 
}

.trader-profile  .profile-field {
  flex: 1; 
  padding: 0 10px; 
}
.trader-profile .profile-header h1 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 10px;
  align-items: center;
  text-align: center;
}

.trader-profile  .profile-header p {
  font-size: 1.2rem;
  color: #666;
  align-items: center;
  text-align: center;
  margin-bottom: 10px;
}

.trader-profile .profile-picture {
  width: 150px;
  height: 150px;
  border-radius: 100%;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
  overflow: hidden; 
  position: relative;
  margin: 0 auto; 
  border-color: #666;
  margin-bottom: 40px;
}

.trader-profile .profile-picture img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
}

.trader-profile .edit-profile-icon {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background-color: #007bff;
  color: white;
  padding: 5px;
  border-radius: 50%;
  cursor: pointer;
  display: none;
}

.trader-profile .edit-profile-icon:hover {
  background-color: #0056b3;
}

.trader-profile .profile:hover .edit-profile-icon {
  display: block; 
}

.trader-profile .profile-field label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #555;
  display: block;
  font-size: 1rem;
}

.trader-profile .profile-field input, .profile-field select, .profile-field p {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 35px;
  font-size: 1rem;
}

.trader-profile  .profile-field p {
  background-color: #f9f9f9;
}

.trader-profile  button {
  background-color: orange;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 35px;
  cursor: pointer;
}

.trader-profile  button:hover {
  background-color: crimson;
}
</style>
  <body>
    <div class="grid-container">

    <header class="header">
  <div class="menu-icon" onclick="openSidebar()">
    <span class="material-icons-outlined">menu</span>
  </div>
  <div class="search-bar">
    <input type="text" placeholder="Search...">
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</div>
  <div class="profile-section">
    <div class="profile-image">
    <i class="fa-regular fa-user bigger-icon"></i>
    </div>
    <div class="profile-name"><?php echo $_SESSION['user']['FIRST_NAME'];?></div>
  </div>
</header>


     <!-- Sidebar -->
<aside id="sidebar">
  <div class="sidebar-title">
    <div class="sidebar-brand">
      <a href="./trader_dashboard.php"><img src="../assets/images/icons/logo.png" alt=""></a>
    </div>
    <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
  </div>

  <ul class="sidebar-list">
  <li class="sidebar-list-item">
                    <a href="./trader_dashboard.php">
                        <img src="" alt=""> Dashboard
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="http://127.0.0.1:8080/apex/f?p=101:LOGIN_DESKTOP:16026069182778:::::" target="_blank">
                        <img src="" alt=""> Report
                    </a>
                    <!-- <ul class="submenu">
                        <li><a href="#">Report 1</a></li>
                        <li><a href="#">Report 2</a></li>
                        <li><a href="#">Report 3</a></li>
                    </ul> -->
                </li>
</li>
    <li class="sidebar-list-item">
      <a href="./trader_profile.php">
       <img src="#" alt=""> My Profile
      </a>
    </li>
    <li class="sidebar-list-item">
      <a href="./view_product_detail.php">
       <img src="" alt=""> Product Detail
      </a>
    </li>
    <li class="sidebar-list-item">
      <a href="./shopDetail.php">
       <img src="#" alt=""> Shop Detail
      </a>
    </li>
  </ul>

  <!-- Logout Button -->
  <div class="logout-button">
  <a href="../Authentication/logout.php"><button>Logout</button></a>
  </div>
</aside>
<!-- End Sidebar -->


      <!-- Main -->
      <main class="main-container">
        <div class="main-title">
          <h2>Shop Details</h2>
        </div>
        <div class="trader-profile">
    <div class="container">
    <div class="main-content">
                <div class="profile" id="profile">
                    <div class="profile-header">
                        <h1>Shop Details </h1>
                    </div>
                    <?php if($error): ?>
            <div class="error-message" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
                    <div class="profile-content" id="profile-content">
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="shopname">Shop Name</label>
                                <input id="shopname" type="text" name="shopname" value="<?php echo $fetch['SHOP_NAME']; ?>" readonly>
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="location"> Shop Location </label>
                                <input id="location" type="text" name="location" value="<?php echo $fetch['SHOP_LOCATION']; ?>" readonly>
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="number">Contact Number</label>
                                <input id="number" type="number" name="contact_number" value="<?php echo $fetch['CONTACT_NUMBER']; ?>" readonly>
                            </div>
                        </div>
                        <button id="edit-profile">Edit Shop</button>
                    </div>
                    <div class="profile-content" id="edit-profile-content" style="display: none;">
                        <!-- Edit profile form -->
                        <form action="" method="POST" enctype="multipart/form-data">>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="shopname">Shop Name</label>
                                <input id="shopname" type="text" name="shopname" value="<?php echo $fetch['SHOP_NAME']; ?>" >
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="location"> Shop Location </label>
                                <input id="location" type="text" name="location" value="<?php echo $fetch['SHOP_LOCATION']; ?>" >
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="number">Contact Number</label>
                                <input id="number" type="number" name="contact_number" value="<?php echo $fetch['CONTACT_NUMBER']; ?>" >
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="<?php echo $fetch['EMAIL']; ?>" >
                            </div>
                        </div>
                        <div class="profile-row">
                            <div class="profile-field">
                                <label for="file">shop Photo</label>
                              <input type="file" id="shopImage" name="shopImage" class="input-field">
                            </div>
                        </div>
                            <div class="profile-row">
                                <button name="submit" id="save-profile">Save Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>

    </div>

      
              
      </main>
    </div>

  </body>
  <script>
        document.getElementById('edit-profile').addEventListener('click', function() {
            document.getElementById('profile-content').style.display = 'none';
            document.getElementById('edit-profile-content').style.display = 'block';
        });

        document.getElementById('save-profile').addEventListener('click', function() {
            // Code to save the updated profile
            alert('Profile saved successfully!');
            // For demo purposes, let's switch back to the view mode
            document.getElementById('edit-profile-content').style.display = 'none';
            document.getElementById('profile-content').style.display = 'block';
        });

        let sidebarOpen = false;
const sidebar = document.getElementById('sidebar');

function openSidebar() {
    if (!sidebarOpen) {
        sidebar.classList.add('sidebar-responsive');
        sidebarOpen = true;
    }
}

function closeSidebar() {
    if (sidebarOpen) {
        sidebar.classList.remove('sidebar-responsive');
        sidebarOpen = false;
    }
}
    </script>

</html>




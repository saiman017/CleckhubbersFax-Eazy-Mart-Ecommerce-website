<?php


require_once '../middlewares/checkAuthentication.php';
include '../middlewares/checkRoles.php';
include '../middlewares/traderApproval.php';
include '../messages/notifications.php';



checkIfUserIsLoggedIn();
checkUserRole('trader');

checkTraderApproval();


$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error']= $m['message'];
    exit();
} 


if (isset($_SESSION['user']['EMAIL'])) {
    $userEmail = $_SESSION['user']['EMAIL'];
} else {
  $_SESSION['error'] = "User not found";
}

    
$query = "SELECT * FROM Trader WHERE Email = '$userEmail'";
$statement = oci_parse($conn, $query);
oci_execute($statement);
// Fetch the user record
$fetch = oci_fetch_assoc($statement);


if (isset($_POST['saveProfile'])) {
  // Retrieve form data
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $traderId = $_SESSION['user']['TRADER_ID'];
  $number = $_POST['contact_number'];
  $address = $_POST['address'];
  $Uname = $_POST['username'];

  $query = "UPDATE TRADER 
            SET FIRST_NAME = '$first_name', LAST_NAME = '$last_name', ADDRESS = '$address', USERNAME = '$Uname', CONTACT_NUMBER = '$number' 
            WHERE TRADER_ID = '$traderId'";

  $statement = oci_parse($conn, $query);
  $result = oci_execute($statement);

  if ($result) {
      oci_commit($conn);
      $_SESSION['notification']= "Successfully updated profile!";
      header("Location: trader_profile.php");
      exit();
  } else {
      $_SESSION['error']= "Error updating profile!";
  } 

  oci_close($conn);
}
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
    <div class="profile-name"><?php echo $fetch['FIRST_NAME'];?></div>
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
          <h2>My Profile</h2>
        </div>
        <div class="trader-profile">
    <div class="container">
    <div class="main-content">
                <div class="profile" id="profile">
                    <div class="profile-header">
                        <h1>My Profile</h1>
                        <p>Welcome back, <?php echo $fetch['FIRST_NAME']; ?></p>
                        <!-- <div class="profile-picture">
                            <img src="https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?q=80&w=1476&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Profile Picture">
                            <div class="edit-profile-icon">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                        </div> -->
                    </div>
                      <?php if (isset($_SESSION['notification'])): ?>
                      <div class="notification-message" role="alert">
                          <?php echo $_SESSION['notification']; unset($_SESSION['notification']); ?>
                      </div>
                  <?php endif; ?>
                  <?php if (isset($_SESSION['error'])): ?>
                      <div class="error-message" role="alert">
                          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                      </div>
                  <?php endif; ?>
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
                        <form action="" method="POST">
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



        // SIDEBAR TOGGLE

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




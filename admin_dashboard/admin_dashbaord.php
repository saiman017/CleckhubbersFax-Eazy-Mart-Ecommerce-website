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
if (isset($_SESSION['user']['USERNAME'])) {
    $userEmail = $_SESSION['user']['USERNAME'];
} else {
    $_SESSION['error'] = "User not found";
    header("Location: ../Login/customer_signin.php");
    exit();
}


$query = "SELECT 
Product.Product_ID,
Product.Product_Name,
Product.Price AS Product_Price,
Product.Stock_Available AS Product_Stock,
Product.Product_Status,
Product.Allergy,
Product.Description,
Shop.Shop_Name,
Trader.First_Name || ' ' || Trader.Last_Name AS Trader_Name
FROM Product
INNER JOIN Shop ON Product.Shop_ID = Shop.Shop_ID
INNER JOIN Trader ON Shop.Trader_ID = Trader.Trader_ID";

$admin = $_SESSION['user']['USERNAME'];
 $statement = oci_parse($conn, $query);
 oci_execute($statement);

// total customer
$customer = "SELECT COUNT(*) AS Total_Customers FROM Customer";
$statementcust = oci_parse($conn, $customer);
oci_execute($statementcust);
$row1 = oci_fetch_assoc($statementcust);

$totalcustomer = $row1['TOTAL_CUSTOMERS'];


// total Approve Trader
$trader = "SELECT COUNT(*) AS Total_Approve_Traders FROM Trader WHERE TRADER_STATUS = 1";
$statementtrad = oci_parse($conn, $trader);
oci_execute($statementtrad);
$row2 = oci_fetch_assoc($statementtrad);
$Approvetrader = $row2['TOTAL_APPROVE_TRADERS'];


//  Trader pending reguest
$ptrader = "SELECT COUNT(*) AS Pending_Traders FROM Trader WHERE TRADER_STATUS = 0";
$statementpend = oci_parse($conn, $ptrader);
oci_execute($statementpend);
$row3 = oci_fetch_assoc($statementpend);
$Pendingtrader = $row3['PENDING_TRADERS'];

// Total revenu

$revenu = "SELECT SUM(Total_Amount) AS Total_Revenue FROM Order_Detail";
$statementrev = oci_parse($conn, $revenu);
oci_execute($statementrev);
$row4 = oci_fetch_assoc($statementrev);
$Totalrevenue = $row4['TOTAL_REVENUE'];



 oci_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="../messages/notification.css">
  </head>
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
    <div class="profile-name"><?php echo $admin?></div>
  </div>
</header>


    <!-- Sidebar -->
    <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    <a href="./admin_dashbaord.php"><img src="../assets/images/icons/logo.png" alt=""></a>
                </div>
                <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
            </div>

            <ul class="sidebar-list">
            <li class="sidebar-list-item">
                    <a href="./admin_dashbaord.php">
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
                <li class="sidebar-list-item">
                    <a href="./Manage_product.php">
                        <img src="" alt=""> Manage Product
                    </a>
                    
                </li>
                <li class="sidebar-list-item">
                    <a href="./manage_trader.php">
                        <img src="" alt=""> Manage Trader
                    </a>
                </li>
                <li class="sidebar-list-item">
      <a href="./manage_customer.php">
       <img src="#" alt=""> Manage Customer
      </a>
    </li>
    <li class="sidebar-list-item">
                    <a href="./traderApprove.php">
                        <img src="" alt=""> Trader Approve 
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./manageOrder.php">
                        <img src="" alt=""> Manage Order 
                    </a>
                </li>
            </ul>

            <!-- Logout Button -->
            <div class="logout-button">
                <a href="../Authentication/logout.php"><button>Logout</button></a>
            </div>
        </aside>
      <<!-- Main -->
      <main class="main-container">
        <div class="main-title">
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
          <h2>Dashboard</h2>
        </div>

        <div class="main-cards">

          <div class="card">
            <div class="card-inner">

            <h3>Total Customer</h3>
              <h5><?php echo $totalcustomer?></h5>
            </div>
          </div>

          <div class="card">
            <div class="card-inner">
              <h3>Approve Traders</h3>
              <h5><?php echo $Approvetrader?> </h5>
            </div>
          </div>
    

          <div class="card">
            <div class="card-inner">
            <h3>Total Revenu</h3>
            <h6><?php echo $Totalrevenue?> <h6>
            </div>
          </div>

          <div class="card">
            <div class="card-inner">
            <h3>Pending Request</h3>
            <h5><?php echo $Pendingtrader?> </h5>
            </div>
          </div>
        </div>       
      </main>
    </div>
    </div>

    <script>
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
  </body>
</html>
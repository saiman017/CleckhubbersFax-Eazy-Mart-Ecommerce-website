<?php
require_once '../middlewares/checkAuthentication.php';
include '../middlewares/checkRoles.php';
include '../middlewares/traderApproval.php';
include '../messages/notifications.php';

list($error,$notification)=flashNotification();

try {
    // Check if the user is logged in
    checkIfUserIsLoggedIn();

    checkUserRole('trader');
} catch (\Throwable $th) {
    //throw $th;
} finally {
    checkTraderApproval();
}

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
}

$query = "SELECT * FROM Trader WHERE Email = '$userEmail' AND trader_status = 1";
$statement = oci_parse($conn, $query);
oci_execute($statement);
// Fetch the user record
$fetch = oci_fetch_assoc($statement);

$traderId =  $fetch['TRADER_ID'];

// Total products
$product = "SELECT COUNT(*) as Total_product FROM Product p
INNER JOIN Shop s ON p.Shop_ID = s.Shop_ID
INNER JOIN Trader t ON t.Trader_ID = s.Trader_ID WHERE t.TRADER_ID = '$traderId'";
$statementProduct = oci_parse($conn, $product);
oci_execute($statementProduct);
$row1 = oci_fetch_assoc($statementProduct);
$Totalproduct = $row1['TOTAL_PRODUCT'];

// Check if the trader has a shop
$shopQuery = "SELECT COUNT(*) as Shop_Count FROM Shop WHERE Trader_ID = :trader_id";
$shopStatement = oci_parse($conn, $shopQuery);
oci_bind_by_name($shopStatement, ':trader_id', $fetch['TRADER_ID']);
oci_execute($shopStatement);
$shopRow = oci_fetch_assoc($shopStatement);
$hasShop = $shopRow['SHOP_COUNT'] > 0;

oci_close($conn);

// Fetch Reviews
$review = "SELECT t.Trader_ID, t.First_Name, t.Last_Name, t.Email, COUNT(r.Review_ID) AS Total_Reviews
FROM Trader t
JOIN Shop s ON t.Trader_ID = s.Trader_ID
JOIN Product p ON s.Shop_ID = p.Shop_ID
JOIN Review r ON p.Product_ID = r.Product_ID
WHERE t.Trader_ID = :traderId
GROUP BY t.Trader_ID, t.First_Name, t.Last_Name, t.Email";

$statementReview = oci_parse($conn, $review);
oci_bind_by_name($statementReview, ':traderId', $traderId);
oci_execute($statementReview);

$Totalreview = 0; // Initialize default value

if ($rowReview = oci_fetch_assoc($statementReview)) {
    $Totalreview = $rowReview['TOTAL_REVIEWS'] ?? 0; // Use null coalescing operator
}

// Fetch Revenue
$revenue = "SELECT t.Trader_ID, t.First_Name, t.Last_Name, t.Email, SUM(o.Total_Amount) AS Total_Revenue
FROM Trader t
JOIN Shop s ON t.Trader_ID = s.Trader_ID
JOIN Product p ON s.Shop_ID = p.Shop_ID
JOIN Order_Product op ON p.Product_ID = op.Product_ID
JOIN Order_Detail o ON op.Order_ID = o.Order_ID
WHERE t.Trader_ID = :traderId
GROUP BY t.Trader_ID, t.First_Name, t.Last_Name, t.Email";

$statementRevenue = oci_parse($conn, $revenue);
oci_bind_by_name($statementRevenue, ':traderId', $traderId);
oci_execute($statementRevenue);

$fetchRevenue = 0; // Initialize default value

if ($rowRevenue = oci_fetch_assoc($statementRevenue)) {
    $fetchRevenue = $rowRevenue['TOTAL_REVENUE'] ?? 0; // Use null coalescing operator
}

oci_close($conn);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Trader Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="trader.css">
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
        <div class="profile-name"><?php echo $fetch['FIRST_NAME']; ?></div>
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
                <img src="" alt=""> Shop Detail
            </a>
        </li>
    </ul>

    <!-- Logout Button -->
    <div class="logout-button">
        <a href="../Authentication/logout.php"><button>Logout</button></a>
    </div>
</aside>
<!-- End Sidebar -->

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
            <h3>Total Product</h3>
            <h5><?php echo $Totalproduct ?></h5>
        </div>
    </div>

    <?php if (!$hasShop): ?>
        <a href="../trader/add_shop_after_signup.php" class="card-link">
            <div class="card">
                <div class="card-inner">
                    <h3>Add Shop</h3>
                    <h5>&nbsp;</h5>
                </div>
            </div>
        </a>
    <?php endif; ?>

    <div class="card">
        <div class="card-inner">
            <h3>Total Revenue</h3>
            <h6><?php echo $fetchRevenue ?></h6>
        </div>
    </div>

    <div class="card">
        <div class="card-inner">
            <h3>Total Review</h3>
            <h5><?php echo $Totalreview?></h5>
        </div>
    </div>
</div>
      
</main>
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

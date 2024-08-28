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
    header("Location: ../Login/customer_signin.php");
    exit();
}

if (isset($_SESSION['user']['USERNAME'])) {
    $userEmail = $_SESSION['user']['USERNAME'];
} else {
    $_SESSION['error'] = "User not found";
    header("Location: ../Login/customer_signin.php");
    exit();
}

$admin = $_SESSION['user']['USERNAME'];

if (isset($_GET['action']) && $_GET['action'] == 'approve' && isset($_GET['id'])) {
    $traderId = $_GET['id'];

    $updateQuery = "UPDATE TRADER SET TRADER_STATUS = 1 WHERE TRADER_ID =  '$traderId'";
    $stmt = oci_parse($conn, $updateQuery);
    $execute= oci_execute($stmt);
    if (!$execute) {
        $error = oci_error($stmt);
        $_SESSION['error'] ='Failed to approve trader';
    }else{
        $_SESSION['notification']= 'Trader Approved successfully';

    }

}


$query = "SELECT TRADER_ID, FIRST_NAME || ' ' || LAST_NAME AS Trader_Name, CONTACT_NUMBER, ADDRESS, GENDER, EMAIL, REGISTER_DATE, USERNAME FROM TRADER WHERE TRADER_STATUS = 0 ORDER BY TRADER_ID ASC";
$stid = oci_parse($conn, $query);
oci_execute($stid);

oci_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Trader Approval</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <div class="profile-name"><?php echo $admin; ?></div>
            </div>
        </header>

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
            <div class="logout-button">
                <a href="../Authentication/logout.php"><button>Logout</button></a>
            </div>
        </aside>

        <main class="main-container">
            <div class="main-title">
                <h2> Trader Approval</h2>
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
            <div class="product-container">
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Trader ID</th>
                            <th>Trader Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>Register Date</th>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php



                            while ($row = oci_fetch_assoc($stid)) {
                                echo "<tr>";
                                echo "<td>" . $row['TRADER_ID'] . "</td>";
                                echo "<td>" . $row['TRADER_NAME'] . "</td>";
                                echo "<td>" . $row['CONTACT_NUMBER'] . "</td>";
                                echo "<td>" . $row['ADDRESS'] . "</td>";
                                echo "<td>" . $row['GENDER'] . "</td>";
                                echo "<td>" . $row['EMAIL'] . "</td>";
                                echo "<td>" . $row['REGISTER_DATE'] . "</td>";
                                echo "<td>" . $row['USERNAME'] . "</td>";
                                echo '<td><a href="?action=approve&id=' . $row['TRADER_ID'] . '" class="btn btn-success btn-sm">Approve</a></td>';
                                echo "</tr>";
                            }


                        
                        ?>
                    </tbody>
                </table>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

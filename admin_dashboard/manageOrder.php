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

$admin = $_SESSION['user']['USERNAME'];

$query = "SELECT 
od.Order_ID,
od.Order_Date,
od.Order_Status,
od.Total_Amount,
cs.Collection_Day,
cs.Collection_Date,
cs.Collection_Time,
p.Payment_ID,
p.Amount,
p.Payment_Method,
p.Payment_Date,
c.Customer_ID,
c.First_Name,
c.Last_Name,
c.Email,
c.Contact_Number,
c.Address
FROM 
Order_Detail od
JOIN 
Collection_Slot cs ON od.Collection_ID = cs.Collection_ID
JOIN 
Payment p ON od.Payment_ID = p.Payment_ID
JOIN 
Customer c ON od.Customer_ID = c.Customer_ID
WHERE 
od.Order_Status = 'not received'

";

$result = oci_parse($conn, $query);
$execute = oci_execute($result);

if (!$execute) {
    $error = oci_error($result);
    $_SESSION['error'] = $error['message'];
    oci_close($conn);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'approve' && isset($_GET['id'])) {
    $orderID = $_GET['id'];

    $updateQuery = "UPDATE Order_Detail SET Order_Status = 'received' WHERE Order_ID = :order_id";
    $stmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($stmt, ':order_id', $orderID);
    $execute = oci_execute($stmt);

    if (!$execute) {
        $error = oci_error($stmt);
        $_SESSION['error'] = 'Failed to approve order';
    } else {
        $_SESSION['notification'] = 'Order approved successfully';
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['cid'])) {
    $orderID = $_GET['cid'];

    $updateQuery = "UPDATE Order_Detail SET Order_Status = 'cancelled' WHERE Order_ID = :order_id";
    $stmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($stmt, ':order_id', $orderID);
    $execute = oci_execute($stmt);

    if (!$execute) {
        $error = oci_error($stmt);
        $_SESSION['error'] = 'Failed to cancel order';
    } else {
        $_SESSION['notification'] = 'Order cancelled successfully';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Manage Order confirmation</title>
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
                <div class="profile-name"><?php echo htmlspecialchars($admin); ?></div>
            </div>
        </header>

        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    <a href="./admin_dashbaord.php"><img src="../assets/images/icons/logo.png" alt="Logo"></a>
                </div>
                <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
            </div>

            <ul class="sidebar-list">
                <li class="sidebar-list-item">
                    <a href="./admin_dashbaord.php">Dashboard</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="http://127.0.0.1:8080/apex/f?p=101:LOGIN_DESKTOP:16026069182778:::::" target="_blank">Report</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./Manage_product.php">Manage Product</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./manage_trader.php">Manage Trader</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./manage_customer.php">Manage Customer</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./traderApprove.php">Trader Approve</a>
                </li>
                <li class="sidebar-list-item">
                    <a href="./manageOrder.php">Manage Order</a>
                </li>
            </ul>

            <!-- Logout Button -->
            <div class="logout-button">
                <a href="../Authentication/logout.php"><button>Logout</button></a>
            </div>
        </aside>

        <!-- Main -->
        <main class="main-container">
            <div class="main-title">
                <h2>Manage Order confirmation</h2>
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
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Customer Phone Number</th>
                            <th>Order Date</th> 
                            <th>Total Amount</th>
                            <th>Payment ID</th>
                            <th>Collection Day</th>
                            <th>Collection Date</th>
                            <th>Collection Time</th>
                            <th>Order Status</th>
                            <th>Approve</th>
                            <th>Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = oci_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ORDER_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['FIRST_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CONTACT_NUMBER']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ORDER_DATE']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['TOTAL_AMOUNT']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['PAYMENT_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['COLLECTION_DAY']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['COLLECTION_DATE']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['COLLECTION_TIME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ORDER_STATUS']) . "</td>";
                            echo '<td>';
                            echo '<a href="?action=approve&id=' . $row['ORDER_ID'] . '" class="btn btn-success btn-sm">Approve</a>';
                            echo '</td>';
                            echo '<td>';
                            echo '<a href="?action=cancel&cid=' . $row['ORDER_ID'] . '" class="btn btn-danger btn-sm">Cancel</a>';
                            echo '</td>';
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

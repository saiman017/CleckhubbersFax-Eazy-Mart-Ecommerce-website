<?php
require_once '../middlewares/checkAuthentication.php';
include '../messages/notifications.php';

list($error,$notification)=flashNotification();

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();



$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit();
} else {
    $_SESSION['notification'] = "Connected to Oracle!";
}

if (isset($_SESSION['user']['USERNAME'])) {
    $userEmail = $_SESSION['user']['USERNAME'];
} else {
    $_SESSION['error'] = "User not found";
    header("Location: ../Login/customer_signin.php");
    exit();
}

$query = "SELECT 
r.Review_ID,
c.First_Name || ' ' || c.Last_Name AS Customer_Name,
p.Product_Name,
r.Review_Date,
r.Rating,
r.Review_Comment
FROM 
Review r
JOIN 
Customer c ON r.Customer_ID = c.Customer_ID
JOIN 
Product p ON r.Product_ID = p.Product_ID
ORDER BY 
r.Review_Date DESC ";


$result = oci_parse($conn, $query);
$execute = oci_execute($result);

$admin = $_SESSION['user']['USERNAME'];

if (!$execute) {
    $error = oci_error($result);
    $_SESSION['error'] = $error['message'];
    oci_close($conn);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Manage Customer</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="grid-container">
    <?php if($error): ?>
    <div class="alert alert-danger" role="alert">
    <?php  echo $error; ?>
    </div>
    <?php endif; ?>
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
                    <img src="../assets/images/Shop/butcher 1.jpg" alt="Profile Image">
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
                    <a href="#">
                        <img src="" alt=""> Report
                    </a>
                    <ul class="submenu">
                        <li><a href="#">Report 1</a></li>
                        <li><a href="#">Report 2</a></li>
                        <li><a href="#">Report 3</a></li>
                    </ul>
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
                    <a href="./manageReview.php">
                        <img src="" alt=""> Manage Review
                    </a>
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
                <h2>Manage Customer</h2>
            </div>
            <div class="product-container overflow ">
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Review ID</th>
                            <th>Customer Name</th>
                            <th>Product Name</th>
                            <th>Review Date</th>
                            <th>Customer Rating</th>
                            <th>Customer Comment</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
    while ($row = oci_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['REVIEW_ID'] . "</td>";
        echo "<td>" . $row['CUSTOMER_NAME'] . "</td>";
        echo "<td>" . $row['PRODUCT_NAME'] . "</td>";
        echo "<td>" . $row['REVIEW_DATE'] . "</td>";
        echo "<td>" . $row['RATING'] . "</td>";
        echo "<td>" . $row['REVIEW_COMMENT'] . "</td>";
        echo '<td><a href="deleteReview.php?id=' . $row['REVIEW_ID'] . '" class="btn btn-danger btn-sm">Delete</a></td>';
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

<?php
oci_free_statement($result);
oci_close($conn);
?>

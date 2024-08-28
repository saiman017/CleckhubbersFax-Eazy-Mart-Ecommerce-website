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
    $_SESSION['error'] = "Failed to connect to the database.";
    exit();
}

// Fetch shop details
$traderId = $_SESSION['user']['TRADER_ID'];
$query_shop = "SELECT * FROM Shop WHERE Trader_id = :trader_id";
$statement_shop = oci_parse($conn, $query_shop);
oci_bind_by_name($statement_shop, ':trader_id', $traderId);
oci_execute($statement_shop);
$row_shop = oci_fetch_assoc($statement_shop);

if (!$row_shop) {
    $_SESSION['error'] = "Shop details not found.";
    header("Location: trader_dashboard.php");
    exit();
}

$shopId = $row_shop['SHOP_ID'];

if (isset($_POST['addproduct'])) {
    // Retrieve and sanitize form data
    $productName = $_POST['productName'];
    $productDescription = $_POST['productDescription'];
    $productPrice = $_POST['productPrice'];
    $productStock = $_POST['productStock'];
    $minOrder = $_POST['minOrder'];
    $maxOrder = $_POST['maxOrder'];
    $allergy = $_POST['allergy'];
    $categories = $_POST['categories'];
    $productPhoto = $_FILES['productPhoto']['name'];

    // Check if product name already exists
    $query_check = "SELECT PRODUCT_NAME FROM Product WHERE PRODUCT_NAME = :productName";
    $statement_check = oci_parse($conn, $query_check);
    oci_bind_by_name($statement_check, ':productName', $productName);
    oci_execute($statement_check);
    $row = oci_fetch_assoc($statement_check);

    if ($row !== false) {
        $_SESSION['error'] = "Product name already exists. Please use a different name.";
        header("Location: view_product_detail.php");
        exit();
    }


    $allowed_extension = array('gif','png','jpg','jpeg');
    $filename = $_FILES['productPhoto']['name'];
    $file_extension = pathinfo($filename,PATHINFO_EXTENSION);
    if(!in_array($file_extension,$allowed_extension)){
        $_SESSION['error'] = "You are allowed with only jpg png jpeg and gif";
        header('Location: view_product_detail.php');
    }
    else{

            
                // . is concatinate
            if(file_exists("upload/".$_FILES['productPhoto']['name'])){
                $filename = $_FILES['productPhoto']['name'];
                $_SESSION['error'] = "Image already exists".$filename ;
                header(("Location: view_product_detail.php"));
                exit();
            }
            else{
                        // Insert new product into database
                $query = "INSERT INTO Product (PRODUCT_NAME, DESCRIPTION, PRICE, STOCK_AVAILABLE, MIN_ORDER, MAX_ORDER, ALLERGY, PRODUCT_IMAGE, SHOP_ID, CATEGORY_ID) 
                VALUES ('$productName', '$productDescription', '$productPrice', '$productStock', '$minOrder', '$maxOrder', '$allergy', '$productPhoto', '$shopId', '$categories')";
                    $statement = oci_parse($conn, $query);
                    
                        $result = oci_execute($statement);

                    if ($result) {
                        if (move_uploaded_file($_FILES["productPhoto"]["tmp_name"], "upload/".$_FILES["productPhoto"]["name"])) {
                            oci_commit($conn);
                            $_SESSION['notification'] = "Product added successfully.";
                        } else {
                            $_SESSION['error'] = "Failed to upload product photo.";
                        }
                        header("Location: view_product_detail.php");
                        exit();
                    } else {
                        $error = oci_error($statement);
                        $_SESSION['error'] = $error['message'];
                        header("Location: view_product_detail.php");
                        exit();
                    }

                }
    }



}

//retrieve products 

// Query to fetch all products
$query1 = "SELECT * FROM Product WHERE SHOP_ID='$shopId'";
$statement2 = oci_parse($conn, $query1);
oci_execute($statement2);

// Fetch products and store them in an array
$products = array();
while ($row = oci_fetch_assoc($statement2)) {
    $products[] = $row;
}

oci_close($conn);
?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>View Shop Details</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <!-- Include Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./trader.css">
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
<div class="profile-name"><?php echo $_SESSION['user']['FIRST_NAME']?></div>
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
</li>
<li class="sidebar-list-item">
  <a href="trader_profile.php">
   <img src="#" alt=""> My Profile
  </a>
</li>
<li class="sidebar-list-item">
  <a href="./view_product_detail.php">
   <img src="view_product_detail.php" alt=""> Product Detail
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
      <h2>Product Detail</h2>
    </div>
    <!-- Product CRUD View -->
    <div class="container mx-auto py-8 px-4">
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
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Your Products</h2>
                <button id="addProductBtn" class="addbg-gradient-to-r from-purple-500 to-indigo-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-gradient bg-gradient transition duration-300">Add Product</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 product-list">
                <?php foreach ($products as $product) { ?>
                    <!-- Product Card -->
                    <div class="product-card">
                    <?php if (!empty($product['PRODUCT_IMAGE'])): ?>
                                        <img src="upload/<?php echo htmlspecialchars($product['PRODUCT_IMAGE']); ?>" alt="Product Image" class="w-20 h-20 object-cover">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                        <div class="product-info p-4">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo $product['PRODUCT_NAME']; ?></h3>
                            <p class="text-sm text-gray-600 mt-2">Price: <?php echo $product['PRICE']; ?></p>
                            <p class="text-sm text-gray-600 mt-2">Stock: <?php echo $product['STOCK_AVAILABLE']; ?></p>
                            <p class="text-sm text-gray-600 mt-2">Product Status: <?php echo $product['PRODUCT_STATUS']; ?></p>
                            <p class="text-sm text-gray-600 mt-2">Description: <?php echo $product['DESCRIPTION']; ?></p>
                        </div>
                        <div class="product-actions flex justify-between items-center">
                        <!-- <button class="text-xs text-gray-600 hover:text-indigo-600 transition duration-300 view-btn" data-product='<?php echo htmlspecialchars(json_encode($product)); ?>'>View</button> -->
                            <button class="text-xs text-gray-600 hover:text-indigo-600 transition duration-300 edit-btn" data-product-id="<?php echo htmlspecialchars(json_encode($product)); ?>">Edit</a></button>
                            <button class="text-xs text-red-600 hover:text-red-700 transition duration-300"><a href="deleteProduct.php?product_id=<?php echo $product['PRODUCT_ID']; ?>">Delete</a></button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <!-- Repeat product cards here -->
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content bg-white">
        <span class="close">&times;</span>
        <div id="productFormContainer"></div>
    </div>
</div>

  </main>
</div>

    </div>
    

  </body>
  <script>
        // Get the modal
        var modal = document.getElementById("productModal");

        // Get the button that opens the modal
        var addProductBtn = document.getElementById("addProductBtn");

        // Get the <span> element that closes the modal
        var closeBtn = document.getElementsByClassName("close")[0];

        // When the user clicks the button to add a product, show the add product form
        addProductBtn.onclick = function() {
            showAddProductForm();
        }

        function showAddProductForm() {
    var productFormContainer = document.getElementById("productFormContainer");
    productFormContainer.innerHTML = `
        <div id="productAddForm">
            <h2 class="text-xl font-semibold mb-4">Add Product</h2>
            <form class="space-y-4" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" name="productName" id="productName" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="productPrice" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="productPrice" id="productPrice" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                    <div>
                        <label for="productStock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="productStock" id="productStock" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="minOrder" class="block text-sm font-medium text-gray-700">Min Order</label>
                        <input type="number" name="minOrder" id="minOrder" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                    <div>
                        <label for="maxOrder" class="block text-sm font-medium text-gray-700">Max Order</label>
                        <input type="number" name="maxOrder" id="maxOrder" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="categories" id="categories" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                        <option value="0">Select category</option>
                        <?php
                        // Fetch categories
                        $query_categories = "SELECT * FROM Category";
                        $statement_categories = oci_parse($conn, $query_categories);
                        oci_execute($statement_categories);
                        while ($row_category = oci_fetch_assoc($statement_categories)) {
                            echo '<option value="' . $row_category['CATEGORY_ID'] . '">' . $row_category['CATEGORY_TYPE'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="productPhoto" class="block text-sm font-medium text-gray-700">Photo</label>
                    <input type="file" name="productPhoto" id="productPhoto" accept="image/*" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div>
                    <label for="productDescription" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="productDescription" id="productDescription" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div>
                    <label for="allergy" class="block text-sm font-medium text-gray-700">Allergy Information</label>
                    <input type="text" name="allergy" id="allergy" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="addproduct" class="bg-indigo-600 hover:bg-gradient bg-gradient text-white px-4 py-2 rounded-md shadow-md transition duration-300">Add Product</button>
                </div>
            </form>
        </div>
    `;
    modal.style.display = "block";
}

        // Example event listeners for edit and view buttons
        document.querySelectorAll('.edit-btn').forEach(item => {
            item.addEventListener('click', event => {
                const product = item.getAttribute('data-product-id');
                showEditProductForm(product);
            })
        });

        // document.querySelectorAll('.view-btn').forEach(item => {
        //     item.addEventListener('click', event => {
        //         showViewProductForm();
        //     })
        // });

        document.addEventListener('click', function (event) {
    if (event.target.classList.contains('edit-btn')) { // Check if the clicked element is an edit button
        const productJSON = event.target.getAttribute('data-product-id');
        showEditProductForm(productJSON);
    } else if (event.target.classList.contains('view-btn')) {
        // ... (your view-btn logic)
        showViewProductForm();
    }
});

        // Example function to show the edit product form
        function showEditProductForm(productJSON) {
    var product = JSON.parse(productJSON);
    var productFormContainer = document.getElementById("productFormContainer");
    productFormContainer.innerHTML = `
        <div id="productEditForm">
            <h2 class="text-xl font-semibold mb-4">Edit Product</h2>
            <form class="space-y-4" action="editproduct.php?id=${product['PRODUCT_ID']}" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" name="productName" value="${product['PRODUCT_NAME']}" id="productName"  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="productPrice" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="productPrice" value="${product['PRICE']}" id="productPrice" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                    <div>
                        <label for="productStock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="productStock" value="${product['STOCK_AVAILABLE']}" required id="productStock" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="minOrder" class="block text-sm font-medium text-gray-700">Min Order</label>
                        <input type="number" name="minOrder" value="${product['MIN_ORDER']}" id="minOrder"required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                    <div>
                        <label for="maxOrder" class="block text-sm font-medium text-gray-700">Max Order</label>
                        <input type="number" name="maxOrder" value="${product['MAX_ORDER']}" id="maxOrder"required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                    </div>
                </div>
                <div>
                    <label for="oldPhoto" class="block text-sm font-medium text-gray-700">Photo</label>
                    <input type="file" name="oldPhoto" id="oldPhoto" accept="image/*" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div>
                    <label for="productDes" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="productDes" value="${product['DESCRIPTION']}" id="productDes" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div>
                    <label for="allergy" class="block text-sm font-medium text-gray-700">Allergy Information</label>
                    <input type="text" name="allergy" id="allergy" value="${product['ALLERGY']}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md placeholder-gray-400 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-300 bg-gray-100 hover:bg-gray-200">
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="submit" class="bg-indigo-600 hover:bg-gradient bg-gradient text-white px-4 py-2 rounded-md shadow-md transition duration-300">Save Changes</button>
                </div>
            </form>
        </div>
    `;
    modal.style.display = "block";
}


        // When the user clicks on <span> (x), close the modal
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }


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



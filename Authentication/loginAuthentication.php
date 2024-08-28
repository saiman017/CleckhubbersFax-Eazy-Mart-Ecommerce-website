<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
} 

if (isset($_POST['login'])) {
    $email_username = $_POST['email_username'];
    $password = $_POST['password'];
    $role = $_POST['role']; 
    
    if ($role == 'customer') {
        $query = "SELECT * FROM Customer WHERE Email = :email OR Username = :username";
    } elseif ($role == 'trader') {
        $query = "SELECT * FROM Trader WHERE Email = :email OR Username = :username";
    } elseif ($role == 'admin') {
        $query = "SELECT * FROM Management WHERE  Username = :username";
    } else {
        $_SESSION['error'] = "Invalid role selected.";
        header('Location: ../Login/customer_signin.php');
        exit();
    }

    $statement = oci_parse($conn, $query);
    
    // Bind parameters
    oci_bind_by_name($statement, ":email", $email_username);
    oci_bind_by_name($statement, ":username", $email_username);
    
    oci_execute($statement);

    // Fetch the user record
    $user = oci_fetch_assoc($statement);

    if ($user) {
        // Verify email/username and password
        if (($user['EMAIL'] === $email_username || $user['USERNAME'] === $email_username) && $user['PASSWORD'] === $password) {
            // Authentication successful
            $_SESSION['user'] = $user; 
            // Redirect users to role-based pages
            if ($role == 'customer') {
                if (isset($_SESSION['user']['CUSTOMER_ID'])) {
                    $customerID = $_SESSION['user']['CUSTOMER_ID'];
                    $cartitemQuery = "SELECT 
                                        ca.Cart_ID,
                                        COUNT(ci.Cart_Item_ID) AS ITEM_COUNT
                                    FROM 
                                        Cart ca
                                    LEFT JOIN 
                                        Cart_Item ci ON ca.Cart_ID = ci.Cart_ID
                                    WHERE 
                                        ca.CUSTOMER_ID = :customerID
                                    GROUP BY 
                                        ca.Cart_ID";
                
                    $statement2 = oci_parse($conn, $cartitemQuery);
                    oci_bind_by_name($statement2, ":customerID", $customerID);
                    oci_execute($statement2);
                
                    $fetch = oci_fetch_assoc($statement2);   
                    
                    $_SESSION['Cart_Item'] = $fetch['ITEM_COUNT'];
                }
                header("Location: ../index.php");
            } elseif ($role == 'trader') {
                header("Location: ../trader/trader_dashboard.php");
            } elseif ($role == 'admin') {
                header("Location: ../admin_dashboard/admin_dashbaord.php");
            }
            exit(); // Make sure to exit after redirection
        } else {
            // Password doesn't match or email/username is incorrect
            $_SESSION['error'] = "Wrong email/username or password. Please enter the correct credentials.";
        }
    } else {
        // User not found
        $_SESSION['error'] = "User data not found.";
    }
    
    oci_close($conn);
    header('Location: ../login/customer_signin.php'); // Redirect back to login page
    exit();
}
?>

<?php 
include '../messages/notifications.php';

// Check if the user is logged in
if(isset($_SESSION['user'])){
    header("Location: ../index.php");
}
?>
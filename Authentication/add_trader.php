<?php
session_start();

$conn = oci_connect('saiman', 'Stha_12', '127.0.0.1:1521/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error']=$m['message'];
    exit;
} else {
    // print "Connected to Oracle!";
    $_SESSION['notification'] = "Connected to Oracle!";
}


if(isset($_POST['submit']))
{
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $fname = $_POST['first-name'];
    $lname = $_POST['last-name'];
    $number = $_POST['contact-number'];
    $address = $_POST['address'];
    $dateOfBirth = date('Y-m-d', strtotime($_POST['dob']));
    $gender = $_POST["gender"];
    $Uname = $_POST['username'];
    $registry = date('d-M-Y');
    $cpassword = $_POST['cpassword'];

    // Check if email, username, or contact number already exists
    $query_check = "SELECT Email, Username, Contact_Number FROM Trader WHERE Email = '$email' OR Username = '$Uname' OR Contact_Number = '$number'";
    $statement_check = oci_parse($conn, $query_check);
    oci_execute($statement_check);
    $row = oci_fetch_assoc($statement_check);
    
    if ($row !== false) {
        if ($email === $row['Email']) {
            $_SESSION['error'] = "Email already exists. Please use a different email.";
        } elseif ($Uname === $row['Username']) {
            $_SESSION['error'] = "Username already exists. Please use a different username.";
        } elseif ($number === $row['Contact_Number']) {
            $_SESSION['error'] = "Contact number already exists. Please use a different one.";
        } else {
            $_SESSION['error'] = "Email, username, or contact number already exists. Please use different ones.";
        }
        header("Location: ../Sign Up/trader_signup.php");
        exit();
    }
    

    // password validation
    if ($password !== $cpassword) {
        $_SESSION['error'] = "Password and Confirm Password do not match. Please try again.";
        header("Location: ../Sign Up/trader_signup.php");
        exit();  
    }
    
    if (strlen($password) < 8 || strlen($password) > 32 ) {
        $_SESSION['error'] = "Password must be at least 8 or less than 32 characters long.";
        header("Location: ../Sign Up/trader_signup.php");
        exit();
    }

    if (!preg_match('/[!@#$%^&*()\-_=+]/', $password)) {
        $_SESSION['error'] = "Password must contain at least one special charcater.";
        header("Location: ../Sign Up/trader_signup.php");
        exit();
    }

    

    $query = "INSERT INTO Trader (First_Name, Last_Name, Contact_Number, Address, Date_of_Birth, Gender, Email, Register_Date, Username, Password, Profile_Image) 
    VALUES ('$fname', '$lname', '$number', '$address', TO_DATE('$dateOfBirth', 'YYYY-MM-DD'), '$gender', '$email', SYSDATE, '$Uname', '$password', null)";

    $statement = oci_parse($conn, $query);
    
    $result = oci_execute($statement);

    if($result) {
        oci_commit($conn);
        $_SESSION['notification'] = 'Trader Register sucessfully';
        header("Location: ../Login/customer_signin.php");
        exit(); 
    }
    else {
        $error = oci_error($statement);
        $_SESSION['error'] = $error['message'];
        header("Location: ../Sign Up/trader_signup.php");
        exit();
    }

    oci_close($conn);
}
?>

<?php
// Start the session for error handling
session_start();
include '../messages/notifications.php';

list($error,$notification)=flashNotification();
// Establish connection to the database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
}

    // Retrieve form data
    $email =  $_SESSION['email'];
    $password = $_SESSION['password'];
    $fname = $_SESSION['first_name'];
    $lname =  $_SESSION['last_name'] ;
    $number = $_SESSION['contact_number'];
    $address = $_SESSION['address'];
    $dateOfBirth =  $_SESSION['dob'];
    $gender =  $_SESSION['gender'];
    $Uname = $_SESSION['username'];

    // // Check if email, username, or contact number already exists
    // $query_check = "SELECT Email, Username, Contact_Number FROM Customer WHERE Email = '$email' OR Username = '$Uname' OR Contact_Number = '$number'";
    // $statement_check = oci_parse($conn, $query_check);
    // oci_execute($statement_check);
    // $row = oci_fetch_assoc($statement_check);

    // if ($row !== false) {
    //     $message = "Email, username, or contact number already exists. Please use different ones.";
    //     if ($email === $row['EMAIL']) {
    //         $message = "Email already exists. Please use a different email.";
    //     } elseif ($Uname === $row['USERNAME']) {
    //         $message = "Username already exists. Please use a different username.";
    //     } elseif ($number === $row['CONTACT_NUMBER']) {
    //         $message = "Contact number already exists. Please use a different one.";
    //     }
    //     $_SESSION['error'] = $message;
    //     header("Location: ../Sign Up/customer_signup.php");
    //     exit();
    // }

    // // Password validation
    // if ($password !== $cpassword) {
    //     $_SESSION['error'] = "Password and Confirm Password do not match. Please try again.";
    //     header("Location: ../Sign Up/customer_signup.php");
    //     exit();  
    // }

    // if (strlen($password) < 8 || strlen($password) > 32 ) {
    //     $_SESSION['error'] = "Password must be at least 8 or less than 32 characters long.";
    //     header("Location: ../Sign Up/customer_signup.php");
    //     exit();
    // }

    // if (!preg_match('/[!@#$%^&*()\-_=+]/', $password)) {
    //     $_SESSION['error'] = "Password must contain at least one special character.";
    //     header("Location: ../Sign Up/customer_signup.php");
    //     exit();
    // }

    // SQL query
    $query = "INSERT INTO Customer (First_Name, Last_Name, Contact_Number, Address, Date_of_Birth, Gender, Email, Register_Date, Username, Password, Profile_Image) 
    VALUES ('$fname', '$lname', '$number', '$address', TO_DATE('$dateOfBirth', 'YYYY-MM-DD'), '$gender', '$email', SYSDATE, '$Uname', '$password', null)";

    $statement = oci_parse($conn, $query);
    $result = oci_execute($statement);

    if($result) {
        oci_commit($conn);
        $_SESSION['notification'] = "Please wait for admin approval";
        header("Location: ../Login/customer_signin.php");
        exit(); 
    } else {
        $error = oci_error($statement);
        $_SESSION['error'] = $error['message'];
        exit(); // Display Oracle error message
    }

    oci_close($conn);

?>

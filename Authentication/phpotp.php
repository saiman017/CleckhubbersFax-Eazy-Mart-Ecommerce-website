<?php
// Start the session
session_start();

// Include PHPMailer autoload file
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Establish connection to the database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    header("Location: ../Sign Up/customer_signup.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $fname = $_POST['first-name'];
    $lname = $_POST['last-name'];
    $number = $_POST['contact-number'];
    $address = $_POST['address'];
    $dateOfBirth = date('Y-m-d', strtotime($_POST['dob']));
    $gender = $_POST["gender"];
    $Uname = $_POST['username'];
    $cpassword = $_POST['cpassword'];

    // Check if email, username, or contact number already exists
    $query_check = "SELECT Email, Username, Contact_Number FROM Customer WHERE Email = :email OR Username = :username OR Contact_Number = :contact_number";
    $statement_check = oci_parse($conn, $query_check);
    oci_bind_by_name($statement_check, ':email', $email);
    oci_bind_by_name($statement_check, ':username', $Uname);
    oci_bind_by_name($statement_check, ':contact_number', $number);
    oci_execute($statement_check);
    $row = oci_fetch_assoc($statement_check);

    if ($row !== false) {
        $message = "Email, username, or contact number already exists. Please use different ones.";
        if ($email === $row['EMAIL']) {
            $message = "Email already exists. Please use a different email.";
        } elseif ($Uname === $row['USERNAME']) {
            $message = "Username already exists. Please use a different username.";
        } elseif ($number === $row['CONTACT_NUMBER']) {
            $message = "Contact number already exists. Please use a different one.";
        }
        $_SESSION['error'] = $message;
        header("Location: ../Sign Up/customer_signup.php");
        exit();
    }

    // Password validation
    if ($password !== $cpassword) {
        $_SESSION['error'] = "Password and Confirm Password do not match. Please try again.";
        header("Location: ../Sign Up/customer_signup.php");
        exit();  
    }

    if (strlen($password) < 8 || strlen($password) > 32) {
        $_SESSION['error'] = "Password must be at least 8 or less than 32 characters long.";
        header("Location: ../Sign Up/customer_signup.php");
        exit();
    }

    if (!preg_match('/[!@#$%^&*()\-_=+]/', $password)) {
        $_SESSION['error'] = "Password must contain at least one special character.";
        header("Location: ../Sign Up/customer_signup.php");
        exit();
    }

    // Store form data in session variables
    $_SESSION['first_name'] = $fname;
    $_SESSION['last_name'] = $lname;
    $_SESSION['email'] = $email;
    $_SESSION['contact_number'] = $number;
    $_SESSION['address'] = $address;
    $_SESSION['dob'] = $dateOfBirth;
    $_SESSION['gender'] = $gender;
    $_SESSION['username'] = $Uname;
    $_SESSION['password'] = $password;


// Generate OTP
    $otp = rand(100000, 999999);

    // Store OTP in session
    $_SESSION["OTP"] = $otp;

    // Create a new PHPMailer instance
    $mail = new PHPMailer();

    // Set PHPMailer to use SMTP
    $mail->isSMTP();

    // Set the SMTP server details
    $mail->Host = 'smtp.gmail.com'; // Your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'cleckhudderfaxeazymart@gmail.com'; // Your SMTP username
    $mail->Password = 'ufyffrfwwimjjpok'; // Your SMTP password
    $mail->SMTPSecure = 'ssl'; // Use SSL encryption
    $mail->Port = 465; // TCP port to connect to

    // Set email headers and content
    $mail->setFrom('cleckhudderfaxeazymart@gmail.com');
    $mail->addAddress($_SESSION["email"]);
    $mail->isHTML(true);
    $mail->Subject = 'OTP Verification';
    $mail->Body = "Your OTP for verification is: $otp";

    // Send the email
    if ($mail->send()) {
        // Email sent successfully, redirect to OTP verification page
        header("Location: ../Email/opt_verify.php");
        exit();
    } else {
        // Email sending failed
        echo 'Failed to send OTP via email. Error: ' . $mail->ErrorInfo;
    }
} else {
    // If the form is not submitted, redirect to the contact page
    header("Location: ../contactUs/contactus.php");
}
?> 
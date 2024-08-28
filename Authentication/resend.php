<?php
session_start();



// Include PHPMailer autoload file
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    header("Location: ../Sign Up/customer_signup.php");
    exit();
}
// Generate a new OTP
$otp = rand(100000, 999999);

// Debugging: Print the OTP to verify it's generated correctly
echo "Generated OTP: $otp<br>";

// Store OTP in session
$_SESSION["OTP"] = $otp;

// Debugging: Print message to verify if OTP is stored in session
if(isset($_SESSION["OTP"])) {
    echo "OTP stored in session: " . $_SESSION["OTP"] . "<br>";
} else {
    echo "Error: OTP not stored in session<br>";
}
$mail = new PHPMailer();

// Set PHPMailer to use SMTP
$mail->isSMTP();

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
    // Email sent successfully, redirect back to the OTP verification page
    header("Location: ../Email/opt_verify.php");
    exit();
} else {
    // Email sending failed
    $_SESSION['error'] = 'Failed to resend OTP via email. Error: ' . $mail->ErrorInfo;
    header("Location: ../Email/opt_verify.php");
    exit();
}
?>

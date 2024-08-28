<?php

include '../messages/notifications.php';

list($error, $notification) = flashNotification();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../messages/notification.css">
</head>
<body>


<div class="login-container">

    <div class="form-container">
        <div class="left-side">
             <img src="../assets/images/login/Free Vector _ Online groceries concept illustration.jpg" alt="Background Image">
        </div>
        <div class="right-side">
            <a href="../index.php"><img src="../assets/images/icons/logo.png" alt="Logo" class="logo"></a>
            <h2>Login</h2>
            <?php 

                if($error): ?>
                <div class="alert alert-danger" role="alert">
                <?php  echo $error; ?>
                </div>
                <?php endif; ?>
            <form action="../Authentication/loginAuthentication.php" method="post">

                <span>Username/Email</span>
                <input type="text" id="email" required name="email_username" placeholder="Username/Email" class="input-field">
                <span>Password</span>
                <input type="password" required id="password" name="password" placeholder="Password" class="input-field">
                <!-- Role Selector -->
                <span>Select Role</span>
                <select name="role" id="role" required class="input-field">
                    <option disabled selected>Select Role</option>
                    <option value="customer">Customer</option>
                    <option value="trader">Trader</option>
                    <option value="admin">Admin</option>
                </select>
   
                <!-- End Role Selector -->

                <div class="remember-me">
    <input type="checkbox" id="rememberMe" required value="lsRememberMe">
    <label for="rememberMe">Remember me</label>
    <span class="forget-link"><a href="../Email/email_verify.php">Forgot your password?</a></span>
</div>

                <button type="submit" class="submit-btn" name="login">Login</button>
            </form>
            <div class="text-center mt-4">
                <p>Don't have an account? <a href="../Sign Up/customer_signup.php" class="signin-link">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

</body>
</html>

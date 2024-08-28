<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../middlewares/checkForTraderStatusInContactUsForm.php';

try {
    checkForTraderStatusInContactUsForm();
} catch (\Throwable $th) {
    $_SESSION['error'] = $th;
}
// echo $_SESSION['user']['TRADER_STATUS'];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"
    />

    <style>
        .logout-button {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .logout-button a {
            color: #ffffff;
            background-color: #333333;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-button a:hover {
            background-color: #555555;
        }
    </style>
</head>

<body>
    <div>
        <!-- Add logout button here -->
        <div class="logout-button">
            <a href="../Authentication/logout.php">Logout</a>
        </div>
    </div>

    <div class="contactus-container">
        <div class="left-container">
            <img src="../assets/images/contact_us/contact_us.png" alt="">
        </div>
        <div class="right-container">
            <div class="h1">Ask Your admin to approve your trader account</div>
            <h2>Contact Us</h2>
            <form id="contact-form" action="https://api.web3forms.com/submit" method="POST">
                <input type="hidden" name="access_key" value="989f27e1-a22e-416b-b3d0-79fef50fbf77">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="4" required></textarea>
                <input type="submit" value="Submit">
            </form>
        </div>
    </div>

    <div>
        <?php include('../includes/footer.php');?>
    </div>
</body>

</html>


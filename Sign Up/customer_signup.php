<?php 
session_start();
include '../messages/notifications.php';

list($error,$notification)=flashNotification();


// Check if the user is logged in
if(isset($_SESSION['user'])){
    header("Location: ../index.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer SignUp</title>
    <link rel="shortcut icon" href="../assets/images/icons/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- css -->
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../messages/notification.css">
    <link rel="stylesheet" href="../messages/notification.css">
</head>

<body>
    <div class="customer-signup">
        <div class="background-container"></div>
     
        <div class="form-container">
            <div class="left-side">
                <img src="../assets/images/login/Grocery shopping online vector.jpg" alt="Background Image">
            </div>
            <div class="right-side">
                <a href="../index.php"><img src="../assets/images/icons/logo.png" alt="Logo" class="logo"></a>
                <h2>Create Account</h2> 
               
                <?php if($error): ?>
                    <div class="error-message" role="alert">
                    <?php  echo $error; ?>
                    </div>
                    <?php endif; ?>

                <form class="w-full max-w-lg" action="../Authentication/phpotp.php" method="post">
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">First
                                Name</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="first-name" required name="first-name" type="text" placeholder="">
                        </div>
                        <div class="w-full md:w-1/2 px-3">
                            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Last
                                Name</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="last-name" required name="last-name" type="text" placeholder="">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                            <label
                                class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Email</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="email" required name="email" type="email" placeholder="">
                        </div>
                        <div class="w-full md:w-1/2 px-3">
                            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Contact
                                Number</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required  id="contact-number" name="contact-number" type="number" placeholder="">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                            <label
                                class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Address</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required id="address" name="address" type="text" placeholder="">
                        </div>
                        <div class="w-full md:w-1/2 px-3">
                            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Date of
                                Birth</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required id="dob" name="dob" type="date" placeholder="">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                            <label
                                class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Gender</label>
                            <select
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="gender" required name="gender">
                                <option disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/2 px-3">
                            <label
                                class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Username</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required id="username" name="username" type="text" placeholder="">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                            <label
                                class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Password</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required  id="password" name="password" type="password" placeholder="">
                        </div>
                        <div class="w-full md:w-1/2 px-3">
                            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1">Confirm
                                Password</label>
                            <input
                                class="appearance-none block w-full    text-gray-700 border rounded py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                required  id="confirm-password" name="cpassword" type="password" placeholder="">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3 mb-3">
                        <div class="w-full px-3">
                            <input type="checkbox" required class="form-checkbox h-4 w-4 text-indigo-600" id="terms"
                                name="terms">
                            <label for="terms" class="ml-2 text-gray-700"><a href="../Support/Terms_and_conditions.php">I agree to terms and conditions</a></label>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="submit-btn">Sign Up</button>
                </form>
                <div class="text-center mt-2">
                    <p>Already have an account? <a href="../Login/customer_signin.php" class="signin-link">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
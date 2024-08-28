<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


function checkIfUserIsLoggedIn(){
    if(!isset($_SESSION['user'])){
        header("Location: ../Login/customer_signin.php");
        exit();
    }
}

?>
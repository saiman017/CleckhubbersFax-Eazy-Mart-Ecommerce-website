<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkForTraderStatusInContactUsForm(){
    if (isset($_SESSION['user']) && isset($_SESSION['user']['TRADER_STATUS'])) {
        if ($_SESSION['user']['TRADER_STATUS'] != 0) {
            header("Location: ../trader/trader_dashboard.php");
            exit(); 
        }
    } else {
        $_SESSION['error'] =  "TRADER_STATUS not set please contact the website maintainers";
    }
}





?>
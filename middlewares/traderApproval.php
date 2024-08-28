<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkTraderApproval() {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['TRADER_STATUS'])) {
        if ($_SESSION['user']['TRADER_STATUS'] != 1) {
            header("Location: ../contactUs/traderApprovals.php");
            exit(); 
        }
    } else {
        $_SESSION['error'] =  "TRADER_STATUS not set please contact the website maintainers";
    }
}
?>

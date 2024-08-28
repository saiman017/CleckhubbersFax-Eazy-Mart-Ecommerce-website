<?php 

function checkUserRole($requiredRole){
    if(isset($_SESSION['user'])){
        if($_SESSION['user']['ROLE'] !== $requiredRole ){
            // print_r($_SESSION['user']['ROLE']);
            header('Location: ../messages/unauthorized.php');
            exit();
        }
    }
}



?>
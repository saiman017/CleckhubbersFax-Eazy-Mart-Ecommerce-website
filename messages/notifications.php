<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$error = '';
$notification = '';

// Check if the function is already defined
if (!function_exists('flashNotification')) {
    function flashNotification(){
        global $error, $notification;

        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['notification'])) {
            $notification = $_SESSION['notification']; 
            unset($_SESSION['notification']);
        }

        return [$error,$notification];
    }
}
?>

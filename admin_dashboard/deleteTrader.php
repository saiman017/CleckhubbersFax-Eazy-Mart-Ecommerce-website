<?php
require_once '../middlewares/checkAuthentication.php';

// Check if the user is logged in
checkIfUserIsLoggedIn();

include '../messages/notifications.php';

list($error, $notification) = flashNotification();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['id'])) {
    $traderID = intval($_GET['id']); // Convert to integer
}

// Connect to the Oracle database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $e = oci_error();
    $_SESSION['error'] = "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    // header("Location: ../admin_dashboard/manage_trader.php");
    exit;
}

try {
    // Start a transaction
    oci_execute(oci_parse($conn, 'BEGIN'), OCI_NO_AUTO_COMMIT);

    // Delete from Report
    $sqlReport = "DELETE FROM Report WHERE Trader_ID = :trader_id";
    $stidReport = oci_parse($conn, $sqlReport);
    oci_bind_by_name($stidReport, ':trader_id', $traderID);
    if (!oci_execute($stidReport, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Report");
    }

    // Delete from Product via Shop
    $sqlProduct = "DELETE FROM Product WHERE Shop_ID IN (SELECT Shop_ID FROM Shop WHERE Trader_ID = :trader_id)";
    $stidProduct = oci_parse($conn, $sqlProduct);
    oci_bind_by_name($stidProduct, ':trader_id', $traderID);
    if (!oci_execute($stidProduct, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Product");
    }

    // Delete from Shop
    $sqlShop = "DELETE FROM Shop WHERE Trader_ID = :trader_id";
    $stidShop = oci_parse($conn, $sqlShop);
    oci_bind_by_name($stidShop, ':trader_id', $traderID);
    if (!oci_execute($stidShop, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Shop");
    }

    // Finally, delete the trader record from the Trader table
    $sqlTrader = "DELETE FROM Trader WHERE Trader_ID = :trader_id";
    $stidTrader = oci_parse($conn, $sqlTrader);
    oci_bind_by_name($stidTrader, ':trader_id', $traderID);
    if (!oci_execute($stidTrader, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Error deleting from Trader");
    }

    // Commit the transaction
    oci_commit($conn);

    $_SESSION['notification'] = "Trader and related records deleted successfully!";
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    oci_rollback($conn);
    $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
} finally {
    // Free the statement identifiers if they are initialized
    if (isset($stidReport)) oci_free_statement($stidReport);
    if (isset($stidProduct)) oci_free_statement($stidProduct);
    if (isset($stidShop)) oci_free_statement($stidShop);
    if (isset($stidTrader)) oci_free_statement($stidTrader);

    // Close the Oracle connection
    oci_close($conn);

    // Redirect back to admin dashboard
    header("Location: ../admin_dashboard/manage_trader.php");
    exit;
}
?>

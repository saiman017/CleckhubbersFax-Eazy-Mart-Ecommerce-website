

<?php 
session_start();
include '../messages/notifications.php';

list($error, $notification) = flashNotification();

// Establish connection to the database
$conn = oci_connect('saiman', 'Stha_12', '//localhost/xe');
if (!$conn) {
    $m = oci_error();
    $_SESSION['error'] = $m['message'];
    exit;
}

// Function to find out available collection slots
function getAvailableCollectionSlots($conn, $order_date) {
    $slots = [];
    $days = ['Wednesday', 'Thursday', 'Friday'];
    $times = ['10-13', '13-16', '16-19'];
    $min_date = clone $order_date;
    $min_date->modify('+24 hours');

    foreach ($days as $day) {
        $slot_date = clone $order_date;
        $slot_date->modify('next ' . $day);
        if ($slot_date < $min_date) {
            continue;
        }
        foreach ($times as $time) {
            // Check if the slot is already filled
            $slot_query = "SELECT COUNT(*) AS order_count
                           FROM Collection_Slot
                           WHERE Collection_Date = TO_DATE(:slot_date, 'YYYY-MM-DD')
                           AND Collection_Time = :collection_time";
            $stmt = oci_parse($conn, $slot_query);
            $slot_date_str = $slot_date->format('Y-m-d');
            oci_bind_by_name($stmt, ':slot_date', $slot_date_str);
            oci_bind_by_name($stmt, ':collection_time', $time);
            oci_execute($stmt);
            $row = oci_fetch_assoc($stmt);
            oci_free_statement($stmt);

            if ($row['ORDER_COUNT'] < 20) {  // Only include slots with less than 20 orders
                $slots[] = [
                    'day' => $day,
                    'date' => $slot_date->format('Y-m-d'),
                    'time' => $time
                ];
            }
        }
    }
    return $slots;
}

$totalAmount = $_SESSION['TOTAL_AMOUNT'];
$order_date = new DateTime(); // Captures the current date and time
$collection_slots = getAvailableCollectionSlots($conn, $order_date);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./checkout.css"> 
    <link rel="stylesheet" href="../messages/notification.css">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <style>
        .collection-slot {
            margin-bottom: 20px;
        }
        .collection-slot h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .collection-slot label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .collection-slot select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }
        .collection-slot select:focus {
            outline: none;
            border-color: #007bff;
        }
        .collection-slot select option {
            padding: 10px;
        }
        .collection-slot select option:checked {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <header><?php include('../includes/head.php'); ?></header>
    <div class="checkoutcontainer">
        <h1>Checkout</h1>
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification-message" role="alert">
                <?php echo $_SESSION['notification']; unset($_SESSION['notification']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <!-- Collection Slot -->
        <form id="collection-form" action="../order/addToorder.php" method="POST"> 
            <div class="collection-slot">
                <h2>Collection Slot</h2>
                <label for="collection-slot">Select a Collection Slot</label>
                <select id="collection-slot" name="collection-slot" required>
                    <option value="" disabled selected>Select a collection slot</option>
                    <?php if (!empty($collection_slots)): ?>
                        <?php foreach ($collection_slots as $slot): ?>
                            <option value="<?php echo htmlspecialchars($slot['date'] . ' ' . $slot['time']); ?>">
                                <?php echo htmlspecialchars($slot['day'] . ' ' . $slot['time']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No slots available</option>
                    <?php endif; ?>
                </select>
            </div>
        </form>
        <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="buy" name="buy">
            <input type="hidden" name="business" value="sb-xqzgl30555991@business.example.com">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="amount" value="<?php echo $totalAmount; ?>">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="return" value="http://localhost/project/Ecommerce/ecommerces/order/addToorder.php?action=success&Total_amount=<?php echo $totalAmount; ?>">
            <button type="submit">Place Order</button>
        </form>
    </div>
    <div><?php include('../includes/footer.php'); ?></div>

    <!-- JavaScript to save selected collection slot to session -->
    <script>
document.addEventListener("DOMContentLoaded", function() {
    var collectionSelect = document.getElementById('collection-slot');
    
    collectionSelect.addEventListener('change', function() {
        var selectedSlot = collectionSelect.value;

        // Save selectedSlot data in a cookie
        document.cookie = "selectedSlot=" + encodeURIComponent(selectedSlot) + "; path=/";
    });
});

    </script>
</body>
</html>
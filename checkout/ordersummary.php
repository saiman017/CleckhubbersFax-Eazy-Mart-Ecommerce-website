<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Products</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* styles.css */
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 90%;
    max-width: 1200px;
}

.product-box h2, .collection-slot h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333333;
}

.product-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 20px;
}

.product-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

.product-item img {
    width: 80px;
    height: 80px;
    border-radius: 10px;
}

.product-details {
    display: flex;
    justify-content: space-between;
    width: 100%;
    padding-left: 20px;
}

.product-info, .product-Quan, .product-price {
    flex: 1;
    text-align: center;
    color: #555555;
}

.product-info span, .product-Quan span, .product-price span {
    font-size: 16px;
}

.collection-slot {
    margin-top: 30px;
}

.collection-slot label {
    display: block;
    margin-bottom: 5px;
    color: #333333;
}

.radio-group {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.radio-group input[type="radio"] {
    margin-right: 5px;
}

.radio-group label {
    margin-bottom: 0;
    color: #555555;
    display: flex;
    align-items: center;
}

.collection-slot select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #cccccc;
    border-radius: 5px;
    font-size: 16px;
}

button[type="submit"] {
    display: block;
    width: 100%;
    padding: 15px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #218838;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="product-box">
            <h2>Order Products</h2>
            <div class="product-row">
                <div class="product-item">
                    <img src="../Image/apple.jpeg" alt="Fresh Apple">
                    <div class="product-details">
                        <div class="product-info">
                            <span>Fresh Apple</span>
                        </div>
                        <div class="product-Quan">
                            <span>Quantity: 1</span>
                        </div>
                        <div class="product-price">
                            <span>$20.00</span>
                        </div>
                    </div>
                </div>  
                <div class="product-item">
                    <img src="../Image/banana.jpeg" alt="Fresh Banana">
                    <div class="product-details">
                        <div class="product-info">
                            <span>Fresh Banana</span>
                        </div>
                        <div class="product-Quan">
                            <span>Quantity: 1</span>
                        </div>
                        <div class="product-price">
                            <span>$20.00</span>
                        </div>
                    </div>
                </div>
                <div class="product-item">
                    <img src="../Image/grapes.jpeg" alt="Fresh Grapes">
                    <div class="product-details">
                        <div class="product-info">
                            <span>Fresh Grapes</span>
                        </div>
                        <div class="product-Quan">
                            <span>Quantity: 1</span>
                        </div>
                        <div class="product-price">
                            <span>$25.00</span>
                        </div>
                    </div>
                </div>
                <div class="product-item">
                    <img src="../Image/orange.jpeg" alt="Fresh Orange">
                    <div class="product-details">
                        <div class="product-info">
                            <span>Fresh Orange</span>
                        </div>
                        <div class="product-Quan">
                            <span>Quantity: 1</span>
                        </div>
                        <div class="product-price">
                            <span>$18.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="collection-slot">
            <h2>Collection Slot</h2>
            <label>Select a Day</label>
            <div class="radio-group">
                <label for="wednesday">
                    <input type="radio" id="wednesday" name="collection-day" value="Wednesday" required> Wednesday
                </label>
                <label for="thursday">
                    <input type="radio" id="thursday" name="collection-day" value="Thursday"> Thursday
                </label>
                <label for="friday">
                    <input type="radio" id="friday" name="collection-day" value="Friday"> Friday
                </label>
            </div>

            <label for="collection-time">Select a Time Slot</label>
            <select id="collection-time" name="collection-time" required>
                <option value="" disabled selected>Select a time slot</option>
                <option value="10-13">10:00 - 13:00</option>
                <option value="13-16">13:00 - 16:00</option>
                <option value="16-19">16:00 - 19:00</option>
            </select>
        </div>
        <button type="submit">Place Order</button>
    </div>
</body>
</html>

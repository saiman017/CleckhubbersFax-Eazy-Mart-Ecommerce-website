<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>
.banner {
    background-color: #007bff;
    color: #fff;
    padding: 60px 20px; /* Increased padding */
    text-align: center;
    height: 400px;
}

.banner h1 {
    font-size: 36px;
    margin-bottom: 20px;
    margin-top: 50px; /* Increased spacing */
}

.banner p {
    font-size: 18px;
    margin-bottom: 30px; /* Increased spacing */
}



.banner button {
    background-color: #fff;
    color: #333;
    border: none;
    padding: 15px 30px; /* Increased padding */
    font-size: 16px;
    cursor: pointer;
    border-radius: 5px;
}

.banner button:hover {
    background-color: #f0f0f0;
}

.about-details {
    text-align: center;
    width: 80%;
    margin: auto;
    padding: 50px 20px; /* Increased padding */
    line-height: 1.6; /* Improved readability */
    padding-bottom: 100px;
}

.about-details span {
    font-weight: 500;
    color: #007bff; /* Changed color */
}

</style>
<body>

<header><?php include('../includes/head.php');?></header>
    
    <div class="banner" style="background-image:url(images/photo.jpg);">
        <h1>Say "Hello" <br> to a good buy!</h1>
        <p>Discover amazing content and services!</p>
        <button><a href="../contactUs/contactus.php">Contact Us</a></button>
    </div>
    

    <div class="about-details">
    <p><span>Welcome to Cleckhuddersfax Eazy Mart,</span> where local flavor meets online convenience! We're passionate about bringing the best of our community's offerings right to your doorstep.</p><br>
    <p>At Cleckhuddersfax Eazy Mart, we're all about supporting our local economy and the independent traders who make it thrive. That's why we've curated a diverse selection of fresh produce, succulent meats, and delicious baked goods, all sourced directly from the talented artisans and small businesses in our area.</p><br><br>
    <p>Our mission is simple: to make local shopping easy and enjoyable for you while championing the incredible talent and dedication of our community's entrepreneurs. When you shop with us, you're not just getting high-quality products - you're also investing in the vibrancy and sustainability of Cleckhuddersfax's local economy.</p><br><br>
    </div>
   

<div><?php include('../includes/footer.php');?></div>

</body>
</html>

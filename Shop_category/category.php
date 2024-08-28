<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleckhuddersfax Eazy Mart</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" type="text/css" href="../includes/footer.css">
    <link rel="stylesheet" type="text/css" href="../includes/header.css">
    <!--
      - favicon
    -->
    <link rel="shortcut icon" href="./assets/images/logo/logo.png" type="image/x-icon">
  
    <!--
      - custom css link
    -->
  
    <!--
      - google font link
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
      rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
      <script src="https://kit.fontawesome.com/8fef77af92.js" crossorigin="anonymous"></script>
  
  </head>
  <body>
   
    <div><?php include('../includes/head.php');?></div>
    

   
  
    
    <main>
      <div class="product-container">
        <div class="container">
          <div class="product-main">
            <h2 class="title">Category</h2>
            <div class="product-grid">
              <!-- Product 1 -->
              <a href="./product_details.html">
                <div class="showcase" >
                  <div class="showcase-banner">
                    <img
                      src="https://plus.unsplash.com/premium_photo-1711684803510-6f05fa515378?q=80&w=1469&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                      alt="Mens Winter Leathers Jackets"
                      width="300"
                      class="product-img default"
                    />
                    <p class="showcase-badge">15%</p>
                    
                  </div>
                  <div class="showcase-content">
                    <a href="#" class="showcase-category">Cauliflower</a>
                    <a href="#"
                      ><h3 class="showcase-title">From Balen Shop</h3></a
                    >
                    <div class="showcase-rating">
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star-outline"></ion-icon>
                      <ion-icon name="star-outline"></ion-icon>
                    </div>
                    <div class="price-box">
                      <p class="price">£ 48.00</p>
                      <del>£ 75.00</del>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <dialog id="productDetails">
        <div class="product-featured">
          <h2 class="title">Product Details</h2>
          <button id="hide" class="close-button">X</button>
          <div class="product-featured">
            <div class="showcase-wrapper has-scrollbar">
              <div class="showcase-container">
                <div class="showcase">
                  <div class="showcase-banner">
                    <img
                      src="./assets/images/products/banner_image.png"
                      alt="shampoo, conditioner & facewash packs"
                      class="showcase-img"
                    />
                  </div>

                  <div class="showcase-content">
                    <h3 class="showcase-title">Product Name</h3>
                    <p class="categoryName">Category Name</p>

                    <p class="showcase-desc">
                      Lorem ipsum dolor sit amet consectetur Lorem ipsum dolor
                      dolor sit amet consectetur Lorem ipsum dolor
                    </p>
                    <div class="showcase-rating">
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star"></ion-icon>
                      <ion-icon name="star-outline"></ion-icon>
                      <ion-icon name="star-outline"></ion-icon>
                    </div>

                    <div class="price-box">
                      <p class="price">Rs 150.00</p>

                      <del>Rs 200.00</del>
                    </div>

                    <button class="add-cart-btn">add to cart</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </dialog>
    </main>
    


    

  
      </div>

      <div><?php include('../includes/footer.php');?></div>
      
     
      
   


    <!--
    - custom js link
  -->
    <script src="./assets/js/script.js"></script>

    <!--
    - ionicon link
  -->
    <script
      type="module"
      src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"
    ></script>
    <script
      nomodule
      src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"
    ></script>
  </body>
</html>

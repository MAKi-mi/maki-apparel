<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:login.php');
};

if(isset($_POST['add_to_cart'])){

   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($select_cart) > 0){
      $message[] = 'product already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, image, quantity) VALUES('$user_id', '$product_name', '$product_price', '$product_image', '$product_quantity')") or die('query failed');
      $message[] = 'product added to cart!';
   }

};

if(isset($_POST['update_cart'])){
   $update_quantity = $_POST['cart_quantity'];
   $update_id = $_POST['cart_id'];
   mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_quantity' WHERE id = '$update_id'") or die('query failed');
   $message[] = 'cart quantity updated successfully!';
}

if(isset($_GET['remove'])){
   $remove_id = $_GET['remove'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'") or die('query failed');
   header('location:index.php');
}
  
if(isset($_GET['delete_all'])){
   mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   header('location:index.php');
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shopping cart</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

   <script>
      $(document).ready(function(){
         // Function to handle filter change
         $('#category_id').change(function(){
            var categoryId = $(this).val();
            // AJAX request to fetch filtered products
            $.ajax({
               url: 'fetch_products.php',
               type: 'GET',
               data: { category_id: categoryId },
               success: function(response){
                  $('.products .box-container').html(response);
               }
            });
         });
      });
   </script>
</head>
<body>
   
<?php
if(isset($message)){
   foreach($message as $message){
      echo '<div class="message" onclick="this.remove();">'.$message.'</div>';
   }
}
?>

<div class="container">
<div class="user-profile">
   <?php
      $select_user = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
      if(mysqli_num_rows($select_user) > 0){
         $fetch_user = mysqli_fetch_assoc($select_user);
   ?>
   <p> Name: <span><?php echo $fetch_user['name']; ?></span> </p>
   <!-- Only display username -->
   <div class="flex">
      <a href="index.php?logout=<?php echo $user_id; ?>" onclick="return confirm('Are you sure you want to logout?');" class="delete-btn">Logout</a>
   </div>
   <?php } ?>
</div>

<!-- Filter dropdown -->
<div class="filter">
   <style>
      select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            width: 100%;
            max-width: 300px; /* Adjust the width as needed */
            box-sizing: border-box;
            -webkit-appearance: none; /* Remove default arrow in Chrome/Safari */
            -moz-appearance: none; /* Remove default arrow in Firefox */
            appearance: none; /* Remove default arrow */
        }

        /* Style for the dropdown arrow */
        select::after {
            content: '\25BC'; /* Unicode character for down arrow */
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Style for hover effect */
        select:hover {
            border-color: #999;
        }

        /* Style for focus effect */
        select:focus {
            outline: none;
            border-color: #555;
        }
   </style>
   <h3>Filter by Category:</h3>
   <form action="" method="get">
      <select id="category_id" name="category_id">
         <option value="">All Categories</option>
         <?php
            $select_categories = mysqli_query($conn, "SELECT * FROM `categories`") or die('query failed');
            while($fetch_category = mysqli_fetch_assoc($select_categories)){
               echo '<option value="'.$fetch_category['id'].'">'.$fetch_category['name'].'</option>';
            }
         ?>
      </select>
   </form>
</div>

<div class="products">

   <h1 class="heading">latest products</h1>
   <div class="header-right" onclick="toggleCart()">  
   <a href="my_orders.php" class="cart-icon">
    <img src="images/cart-icon.png" height="40px" width="40px" alt="Cart Icon"></a>
</div>
<br>
   <div class="box-container">

   <?php
   
      // Check if category filter is set
      $category_filter = '';
      if(isset($_GET['category_id']) && $_GET['category_id'] != ''){
         $category_id = $_GET['category_id'];
         $category_filter = " WHERE p.category_id = '$category_id'";
      }

      $select_product = mysqli_query($conn, "SELECT p.*, c.name AS category_name, c.id AS category_id FROM `products` p JOIN `categories` c ON p.category_id = c.id $category_filter") or die('query failed');
      if(mysqli_num_rows($select_product) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_product)){
   ?>
      <form method="post" class="box" action="">
         <img src="images/<?php echo $fetch_product['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_product['name']; ?></div>
         <div class="price">PHP<?php echo $fetch_product['price']; ?>/-</div>
         <div class="category">Category: <?php echo $fetch_product['category_name']; ?></div>
         <input type="number" min="1" name="product_quantity" value="1">
         <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
         <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
         <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
         <input type="submit" value="add to cart" name="add_to_cart" class="btn">
      </form>
   <?php
      };
   } else {
      echo '<p>No products found.</p>';
   }
   ?>

   </div>

</div>

<div class="shopping-cart">

   <h1 class="heading">shopping cart</h1>

   <table>
      <thead>
         <th>image</th>
         <th>name</th>
         <th>price</th>
         <th>quantity</th>
         <th>total price</th>
         <th>action</th>
      </thead>
      <tbody>
      <?php
         $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
         $grand_total = 0;
         if(mysqli_num_rows($cart_query) > 0){
            while($fetch_cart = mysqli_fetch_assoc($cart_query)){
      ?>
         <tr>
            <td><img src="images/<?php echo $fetch_cart['image']; ?>" height="100" alt=""></td>
            <td><?php echo $fetch_cart['name']; ?></td>
            <td>PHP<?php echo $fetch_cart['price']; ?>/-</td>
            <td>
               <form action="" method="post">
                  <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                  <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
                  <input type="submit" name="update_cart" value="update" class="option-btn">
               </form>
            </td>
            <td>PHP<?php echo $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</td>
            <td><a href="index.php?remove=<?php echo $fetch_cart['id']; ?>" class="delete-btn" onclick="return confirm('remove item from cart?');">remove</a></td>
         </tr>
      <?php
         $grand_total += $sub_total;
            }
         }else{
            echo '<tr><td style="padding:20px; text-transform:capitalize;" colspan="6">no item added</td></tr>';
         }
      ?>
      <tr class="table-bottom">
         <td colspan="4">Subtotal :</td>
         <td>PHP<?php echo $grand_total; ?>/-</td>
         <td><a href="index.php?delete_all" onclick="return confirm('delete all from cart?');" class="delete-btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">delete all</a></td>
      </tr>
   </tbody>
   </table>

 <div class="cart-btn">  
   <a href="checkout.php" class="btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
</div>

</div>

</div>

</body>
</html>
<?php
include 'config.php';

if(isset($_GET['category_id'])){
   $category_id = $_GET['category_id'];

   // Check if category filter is set
   $category_filter = '';
   if($category_id != ''){
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
      }
   } else {
      echo '<p>No products found.</p>';
   }
}
?>

<?php
include 'config.php';
session_start();

if(isset($_POST['checkout'])){
    // Retrieve form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];
    $product_id = $_POST['product_id'];
    
    // Validate form data (you can add more validation as needed)
    if(empty($fullname) || empty($email) || empty($address) || empty($city) || empty($zip)){
        $error = "All fields are required!";
    } else {
        // Calculate subtotal, tax, shipping, and total
        $select_cart_query = mysqli_query($conn, "SELECT SUM(price * quantity) AS subtotal FROM cart WHERE user_id = '{$_SESSION['user_id']}'") or die('query failed');
        $row = mysqli_fetch_assoc($select_cart_query);
        $subtotal = $row['subtotal'];
        $tax = $subtotal * 0.10;
        $shipping = 100;
        $total_amount = $subtotal + $tax + $shipping;
        
       // Insert order into database with total amount
$insert_order_query = "INSERT INTO orders ( user_id, fullname, email, address, city, zip, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_order_query);
$stmt->bind_param("issssid", $_SESSION['user_id'], $fullname, $email, $address, $city, $zip, $total_amount);

        
        if($stmt->execute()){
            // Clear cart after successful checkout
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = '{$_SESSION['user_id']}'") or die('query failed');
            
            // Set success message

            // Redirect back to index.php
            header("Location: index.php");

            exit(); // Stop further execution
        } else {
            $error = "Error placing order: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetching subtotal and total_amount from index.php
if(isset($_SESSION['subtotal'])) {
    $subtotal = $_SESSION['subtotal'];
    $tax = $subtotal * 0.10;
    $shipping = 100;
    $total_amount = $subtotal + $tax + $shipping;
} else {
    // Calculate subtotal if not set (fallback)
    $select_cart_query = mysqli_query($conn, "SELECT SUM(price * quantity) AS subtotal FROM cart WHERE user_id = '{$_SESSION['user_id']}'") or die('query failed');
    $row = mysqli_fetch_assoc($select_cart_query);
    $subtotal = $row['subtotal'];
    $tax = $subtotal * 0.10;
    $shipping = 100;
    $total_amount = $subtotal + $tax + $shipping;
}

// Display success message if set
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']); // Clear the message after displaying it

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            width: 60%;
            margin: auto;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
   <div class="container">
       <br>
       <div class="header-right" onclick="toggleCart()">  
       <a href="index.php" class="cart-icon"><img src="images/back-icon.png" height="40px" width="40px" alt="Cart Icon"></a>
       </div>
        <br>
       <h2>Checkout</h2>
       <?php if(isset($error)) : ?>
           <div class="error"><?php echo $error; ?></div>
       <?php endif; ?>
       <?php if(isset($success_message)) : ?>
           <div class="success"><?php echo $success_message; ?></div>
       <?php endif; ?>
       <form method="post">
           <input type="text" name="fullname" placeholder="Full Name" required><br>
           <input type="email" name="email" placeholder="Email" required><br>
           <input type="text" name="address" placeholder="Address" required><br>
           <input type="text" name="city" placeholder="City" required><br>
           <input type="text" name="zip" placeholder="ZIP/Postal Code" required><br>
           <button type="submit" name="checkout">Place Order</button>
       </form>
       
       <!-- Display total breakdown -->
       <!-- You can customize this section according to your requirements -->
       <div class="price">
           <!-- Total breakdown -->
           <h3>Total Breakdown</h3>
           <p><strong>Mode of Payment: Cash on Delivery</strong> </p>
           <p>Subtotal: PHP<?php echo $subtotal; ?></p>
           <p>Tax (10%): PHP<?php echo $tax; ?></p>
           <p>Shipping: PHP<?php echo $shipping; ?></p>
           <hr>
           <p>Total: PHP<?php echo $total_amount; ?></p>
       </div>
   </div>
</body>
</html>
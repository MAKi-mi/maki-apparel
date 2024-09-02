<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location: login.php');
    exit();
}

// Check if the order form is submitted
if(isset($_POST['submit_order'])) {
    // Retrieve order details from the form
    $product_id = $_POST['product_id']; // Assuming you have a form field to select the product
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];
    $total_amount = $_POST['total_amount'];

    // Insert the order into the database
    $insert_order_sql = "INSERT INTO orders (user_id, fullname, email, address, city, zip, total_amount, product_id) 
                         VALUES ('$user_id', '$fullname', '$email', '$address', '$city', '$zip', '$total_amount', '$product_id')";
    
    if(mysqli_query($conn, $insert_order_sql)) {
        // Order successfully inserted
        header('Location: my_orders.php');
        exit();
    } else {
        // Error occurred while inserting order
        echo "Error: " . mysqli_error($conn);
    }
}

// Modify your SQL query to select orders only for the current user
$sql_orders = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$result_orders = mysqli_query($conn, $sql_orders);


// Handle cancel order functionality
if(isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // Delete the order from the database
    $delete_sql = "DELETE FROM orders WHERE id = '$order_id'";
    if(mysqli_query($conn, $delete_sql)) {
        // Order successfully canceled
        header('Location: my_orders.php');
        exit();
    } else {
        // Error occurred while canceling order
        echo "Error: " . mysqli_error($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <!-- Add your CSS links here -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Styling for pending orders */
        .pending {
            color: orange;
        }

        /* Styling for complete orders */
        .complete {
            color: green; /* White text for better visibility */
        }
    </style>
</head>
<body>

<div class="container">
    <br>
    <div class="header-right" onclick="toggleCart()">  
       <a href="index.php" class="cart-icon"><img src="images/back-icon.png" height="40px" width="40px" alt="Cart Icon"></a>
    </div>
    <br>
    <h2>My Orders</h2>
    <table>
        <thead>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Address</th>
            <th>City</th>
            <th>ZIP/Postal Code</th>
            <th>Total Amount</th>
            <th>Order Date</th>
            <th>Status</th> <!-- Add a new column for order status -->
            <th>Action</th> <!-- Add a new column for the action button -->
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result_orders)): ?>
            <tr>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['city']; ?></td>
                <td><?php echo $row['zip']; ?></td>
                <td><?php echo $row['total_amount']; ?></td>
                <td><?php echo $row['order_date']; ?></td>
                <td class="<?php echo strtolower($row['status']); ?>">
                    <?php echo $row['status']; ?>
                </td> <!-- Display the status with class based on status -->
                <td>
                    <?php if ($row['status'] == 'Pending'): ?>
                        <!-- Button for cancel order -->
                        <form action="" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="cancel_order" class="logout-btn">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    
</div>
</body>
</html>

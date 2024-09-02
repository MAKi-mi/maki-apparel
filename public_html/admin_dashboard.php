<?php
session_start();
include 'config.php';

// Redirect to login page if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('location: admin_login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: admin_login.php');
    exit();
}

// Add Product
if (isset($_POST['add_product'])) {
    // Validate and sanitize input fields
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category']);
    $image = '';

    // Check if an image is uploaded via file input
    if ($_FILES['image']['size'] > 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        // Extract only the filename
        $image = $image_name;
        // Move the uploaded file to the images directory
        move_uploaded_file($image_tmp, "images/$image_name");
    }
    
    // Check if all required fields are filled
    if (!empty($name) && !empty($price) && !empty($category_id)) {
        $sql = "INSERT INTO `products` (`name`, `price`, `category_id`, `image`) VALUES ('$name', '$price', '$category_id', '$image')";
        if (mysqli_query($conn, $sql)) {
            $success = "Product added successfully";
        } else {
            $error = "Error adding product: " . mysqli_error($conn);
        }
    } else {
        $error = "All fields are required";
    }
}


// Delete Product
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $sql = "DELETE FROM `products` WHERE `id` = '$product_id'";
    if (mysqli_query($conn, $sql)) {
        $success = "Product deleted successfully";
        // Redirect to refresh the page and prevent duplicate delete messages
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $error = "Error deleting product: " . mysqli_error($conn);
    }
}

// Delete Selected Products
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_products'])) {
        $selected_products = $_POST['selected_products'];
        $product_ids = implode(',', $selected_products);
        $sql = "DELETE FROM `products` WHERE `id` IN ($product_ids)";
        if (mysqli_query($conn, $sql)) {
            $success = "Selected products deleted successfully";
            // Redirect to refresh the page and prevent duplicate delete messages
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        } else {
            $error = "Error deleting selected products: " . mysqli_error($conn);
        }
    } else {
        $error = "No products selected";
    }
}

// Delete User
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM `user_form` WHERE `id` = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        $success = "User deleted successfully";
        // Redirect to refresh the page and prevent duplicate delete messages
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $error = "Error deleting user: " . mysqli_error($conn);
    }
}

// Fetch Products with Category
$sql = "SELECT p.*, c.name AS category_name FROM `products` p JOIN `categories` c ON p.category_id = c.id";
$result = mysqli_query($conn, $sql);

// Fetch Users
$sql_users = "SELECT * FROM `user_form`";
$result_users = mysqli_query($conn, $sql_users);


$sql_orders = "SELECT * FROM orders ORDER BY order_date DESC";
$result_orders = mysqli_query($conn, $sql_orders);

// Handle form submission for updating order status
if (isset($_POST['update_status'])) {
    // Check if admin is logged in
    if (isset($_SESSION['admin_id'])) {
        $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
        $status = mysqli_real_escape_string($conn, $_POST['update_status']);
        // Update the status of the order
        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        if (mysqli_query($conn, $sql)) {
            // Redirect to refresh the page after updating status
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = "Error updating order status: " . mysqli_error($conn);
        }
    } else {
        // Redirect to login page if admin is not logged in
        header('Location: admin_dashboard.php');
        exit();
    }
}

// Handle form submission for deleting completed orders
if (isset($_POST['delete_complete_orders'])) {
    // Check if admin is logged in
    if (isset($_SESSION['admin_id'])) {
        // Delete orders marked as 'Complete'
        $sql = "DELETE FROM orders WHERE status = 'Complete'";
        if (mysqli_query($conn, $sql)) {
            $success = "Completed orders deleted successfully";
            // Redirect to refresh the page after deleting complete orders
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = "Error deleting completed orders: " . mysqli_error($conn);
        }
    } else {
        // Redirect to login page if admin is not logged in
        header('Location: admin_dashboard.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        img {
            max-width: 100px;
            height: auto;
        }

        .delete-link {
            color: red;
            text-decoration: none;
        }

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
</head>
<body>
    <div class="container">
        <h2><br>Welcome to Admin Dashboard</h2><br>
        <div>
            <p><a class="logout-btn" href="admin_dashboard.php?logout=1">Logout</a></p>
        </div>
        <br>
        <!-- Add Product Form -->
        <h3>Add Product</h3>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" class="pcontainer" name="name" placeholder="Product Name" required><br><br>
            <input type="text" class="pcontainer" name="price" placeholder="&#8369; Product Price" required><br><br>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php
                // Fetch categories from database
                $categories_query = mysqli_query($conn, "SELECT * FROM `categories`");
                while ($category_row = mysqli_fetch_assoc($categories_query)) {
                    echo '<option value="' . $category_row['id'] . '">' . $category_row['name'] . '</option>';
                }
                ?>
            </select><br><br>
            <input type="file" name="image"><br><br>
            <button type="submit" name="add_product" class="btn">Add Product</button>
        </form>
        <br>
        <!-- Display Products -->
        <h3>Products</h3>
        <form method="post">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Action</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>"></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td><?php echo $row['category_name']; ?></td>
                            <td><a href="admin_dashboard.php?delete_product=<?php echo $row['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a></td>
                            <td><input type="checkbox" name="selected_products[]" value="<?php echo $row['id']; ?>"></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <br>
            <button type="submit" name="delete_selected" class="logout-btn" onclick="return confirm('Are you sure you want to delete the selected products?')">Delete Selected</button>
        </form>
        <br>
        <!-- Display Users -->
        <h3>Users</h3>
        <?php if (mysqli_num_rows($result_users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row_user = mysqli_fetch_assoc($result_users)): ?>
                        <tr>
                            <td><?php echo $row_user['name']; ?></td>
                            <td><?php echo $row_user['email']; ?></td>
                            <td><a href="admin_dashboard.php?delete_user=<?php echo $row_user['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found</p>
        <?php endif; ?>
            <!-- Display Orders -->
<br>
<h3>Orders</h3>
<form method="post">
    <?php if (mysqli_num_rows($result_orders) > 0): ?>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order_row = mysqli_fetch_assoc($result_orders)): ?>
                    <tr>
                        <td><?php echo $order_row['fullname']; ?></td>
                        <td><?php echo $order_row['email']; ?></td>
                        <td><?php echo $order_row['address']; ?></td>
                        <td><?php echo $order_row['city']; ?></td>
                        <td><?php echo $order_row['zip']; ?></td>
                        <td><?php echo $order_row['total_amount']; ?></td>
                        <td><?php echo $order_row['order_date']; ?></td>
                        <td>
                            <form method="post" class="mark-as-complete-form">
                                <input type="hidden" name="order_id" value="<?php echo $order_row['id']; ?>">
                                <?php if ($order_row['status'] == 'Pending'): ?>
                                    <button type="submit" name="update_status" value="Complete" class="mark-as-complete-button" style="color: orange;">Pending</button>
                                <?php else: ?>
                                    <button type="submit" name="update_status" value="Pending" class="mark-as-complete-button" style="color: green;">Complete</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <!-- Button to delete completed orders -->
        <button type="submit" name="delete_complete_orders" class="logout-btn" onclick="return confirm('Are you sure you want to delete all completed orders?')">Delete Completed Orders</button>
    <?php else: ?>
        <p>No orders found</p>
    <?php endif; ?>
</form>
    </div>

    <!-- JavaScript to handle form submission and display message -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var forms = document.querySelectorAll('.mark-as-complete-form');
            forms.forEach(function(form) {
                form.addEventListener('change', function() {
                    // Submit the form when the checkbox changes
                    this.submit();
                    // Show "Order Complete" message
                    alert("Order Complete");
                });
            });
        });
    </script>
</body>
</html>
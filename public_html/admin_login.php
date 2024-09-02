<?php
session_start();
include 'config.php';

$error = ''; // Initialize error message

if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM `admin` WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $row['admin_id'];
        header('location: admin_dashboard.php');
        exit(); // Ensure script execution stops after redirect
    } else {
        $error = "Incorrect username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Add your CSS links here -->
    <style>
        .admin-login {
            text-align: start;
            margin-left: 380px; /* Add some space between the text and the form */
        }
    </style>
</head>
<body>
    <br>
        
    <div class="admin-login">
        <div class="header-right" onclick="toggleCart()">  
       <a href="login.php" class="cart-icon"><img src="images/back-icon.png" height="40px" width="40px" alt="Cart Icon"></a>
    </div>
        <h1>Admin Login</h1>
    </div>
    <div class="container">
        <?php if($error !== ''): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" class="pcontainer" name="username" placeholder="Username" required><br><br>
            <input type="password" class="pcontainer" name="password" placeholder="Password" required><br>
            <button class="btn" type="submit" name="submit">Login</button>
        </form>
    </div>
</body>
</html>

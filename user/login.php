<?php

include '../components/connect.php';

if (isset($_POST['login'])) {
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $password = $_POST['password'];
   $password = filter_var($password, FILTER_SANITIZE_STRING);

   // Check if user exists
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
   $select_user->execute([$email, $password]);

   if ($select_user->rowCount() > 0) {
      $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
      $_SESSION['user_id'] = $fetch_user['id']; // store session
      header('location: ../index.php');
   } else {
      $error_msg = "Incorrect email or password!";
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>User Login</title>
   <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<section class="form-container">

   <form action="" method="post">
      <h3>Login Now</h3>

      <?php if (!empty($error_msg)): ?>
         <div class="error-msg"><?= $error_msg; ?></div>
      <?php endif; ?>

      <input type="email" name="email" required placeholder="enter your email" class="box">
      <input type="password" name="password" required placeholder="enter your password" class="box">
      <input type="submit" name="login" value="Login" class="btn">
   </form>

</section>

</body>
</html>

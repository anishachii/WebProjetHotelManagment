<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   header('location:login.php');
   exit;
}

// Handle delete request
if(isset($_POST['delete'])){
   $delete_id = filter_var($_POST['delete_id'], FILTER_SANITIZE_STRING);

   $verify_delete = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
   $verify_delete->execute([$delete_id]);

   if($verify_delete->rowCount() > 0){
      $delete_bookings = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
      $delete_bookings->execute([$delete_id]);
      $success_msg[] = 'Booking deleted!';
   }else{
      $warning_msg[] = 'Booking deleted already!';
   }
}

// Handle update request
if(isset($_POST['update'])){
   $booking_id = $_POST['booking_id'];
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $check_in = $_POST['check_in'];
   $check_out = $_POST['check_out'];
   $rooms = filter_var($_POST['rooms'], FILTER_SANITIZE_NUMBER_INT);
   $adults = filter_var($_POST['adults'], FILTER_SANITIZE_NUMBER_INT);
   $childs = filter_var($_POST['childs'], FILTER_SANITIZE_NUMBER_INT);

   $update = $conn->prepare("UPDATE `bookings` SET name=?, email=?, number=?, check_in=?, check_out=?, rooms=?, adults=?, childs=? WHERE booking_id=?");
   $update->execute([$name, $email, $number, $check_in, $check_out, $rooms, $adults, $childs, $booking_id]);

   $success_msg[] = 'Booking updated successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bookings</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="grid">
   <h1 class="heading">Bookings</h1>

   <div class="box-container">
   <?php
      $select_bookings = $conn->prepare("SELECT * FROM `bookings`");
      $select_bookings->execute();
      if($select_bookings->rowCount() > 0){
         while($booking = $select_bookings->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p>Booking ID : <span><?= $booking['booking_id']; ?></span></p>
      <p>Name : <span><?= $booking['name']; ?></span></p>
      <p>Email : <span><?= $booking['email']; ?></span></p>
      <p>Number : <span><?= $booking['number']; ?></span></p>
      <p>Check In : <span><?= $booking['check_in']; ?></span></p>
      <p>Check Out : <span><?= $booking['check_out']; ?></span></p>
      <p>Rooms : <span><?= $booking['rooms']; ?></span></p>
      <p>Adults : <span><?= $booking['adults']; ?></span></p>
      <p>Children : <span><?= $booking['childs']; ?></span></p>

      <!-- Edit Button triggers form by URL param -->
      <a href="?edit=<?= $booking['booking_id']; ?>#editForm" class="btn">Update Booking</a>

      <!-- Delete Form -->
      <form method="POST" action="">
         <input type="hidden" name="delete_id" value="<?= $booking['booking_id']; ?>">
         <input type="submit" name="delete" value="Delete Booking" onclick="return confirm('Delete this booking?');" class="btn">
      </form>
   </div>
   <?php
         }
      } else {
   ?>
   <div class="box" style="text-align: center;">
      <p>No bookings found!</p>
      <a href="dashboard.php" class="btn">Go to Home</a>
   </div>
   <?php } ?>
   </div>
</section>

<!-- Update Form Section -->
<?php
if(isset($_GET['edit'])){
   $edit_id = $_GET['edit'];
   $get_booking = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
   $get_booking->execute([$edit_id]);
   if($get_booking->rowCount() > 0){
      $edit = $get_booking->fetch(PDO::FETCH_ASSOC);
?>


<section class="form-container" id="editForm">
   <form method="POST" action="">
      <h3>Update Booking</h3>
      <input type="hidden" name="booking_id" value="<?= $edit['booking_id']; ?>">
      <input type="text" name="name" value="<?= $edit['name']; ?>" required class="box">
      <input type="email" name="email" value="<?= $edit['email']; ?>" required class="box">
      <input type="text" name="number" value="<?= $edit['number']; ?>" required class="box">
      <input type="date" name="check_in" value="<?= $edit['check_in']; ?>" required class="box">
      <input type="date" name="check_out" value="<?= $edit['check_out']; ?>" required class="box">
      <input type="number" name="rooms" value="<?= $edit['rooms']; ?>" min="1" step="1" required class="box">
      <input type="number" name="adults" value="<?= $edit['adults']; ?>" min="1" step="1" required class="box">
      <input type="number" name="childs" value="<?= $edit['childs']; ?>" min="0" step="1" required class="box">
      <input type="submit" name="update" value="Update Booking" class="btn">
   </form>
</section>



<?php
   } else {
      echo '<section class="form-container"><p class="empty">Booking not found.</p></section>';
   }
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>
</body>
</html>

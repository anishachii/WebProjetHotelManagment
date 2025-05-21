<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
   exit;
}

// Handle file upload
if (isset($_POST['upload_btn'])) {
   if (!empty($_FILES['uploaded_file']['name'])) {
      $file_name = $_FILES['uploaded_file']['name'];
      $tmp_name = $_FILES['uploaded_file']['tmp_name'];
      $file_path = 'uploads/' . basename($file_name);

      // Create uploads directory if not exists
      if (!is_dir('uploads')) {
         mkdir('uploads', 0777, true);
      }

      if (move_uploaded_file($tmp_name, $file_path)) {
         $remarks = $_POST['remarks'] ?? '';
         $insert = $conn->prepare("INSERT INTO uploads (admin_id, file_name, file_path, remarks) VALUES (?, ?, ?, ?)");
         $insert->execute([$admin_id, $file_name, $file_path, $remarks]);
         echo '<script>swal("Success!", "File uploaded successfully!", "success");</script>';
      } else {
         echo '<script>swal("Error!", "Failed to upload file.", "error");</script>';
      }
   } else {
      echo '<script>swal("Warning!", "Please choose a file.", "warning");</script>';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- dashboard section starts  -->
<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

      <!-- Admin Welcome -->
      <div class="box">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <h3>welcome!</h3>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update.php" class="btn">update profile</a>
      </div>

      <!-- Total Bookings -->
      <div class="box">
         <?php
            $select_bookings = $conn->prepare("SELECT * FROM `bookings`");
            $select_bookings->execute();
            $count_bookings = $select_bookings->rowCount();
         ?>
         <h3><?= $count_bookings; ?></h3>
         <p>total bookings</p>
         <a href="bookings.php" class="btn">view bookings</a>
      </div>

      <!-- Total Admins -->
      <div class="box">
         <?php
            $select_admins = $conn->prepare("SELECT * FROM `admins`");
            $select_admins->execute();
            $count_admins = $select_admins->rowCount();
         ?>
         <h3><?= $count_admins; ?></h3>
         <p>total admins</p>
         <a href="admins.php" class="btn">view admins</a>
      </div>

      <!-- Total Messages -->
      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $count_messages = $select_messages->rowCount();
         ?>
         <h3><?= $count_messages; ?></h3>
         <p>total messages</p>
         <a href="messages.php" class="btn">view messages</a>
      </div>

      <!-- Quick Select -->
      <div class="box">
         <h3>quick select</h3>
         <p>login or register</p>
         <a href="login.php" class="btn" style="margin-right: 1rem;">login</a>
         <a href="register.php" class="btn" style="margin-left: 1rem;">register</a>
      </div>

      <!-- Statistics -->
      <div class="box">
         <h3>statistics</h3>
         <p>view booking statistics</p>
         <a href="statistics.php" class="btn">View Statistics</a>
      </div>

      <!-- Upload Form -->
      <div class="box">
         <h3>Upload File</h3>
         <form action="" method="POST" enctype="multipart/form-data">
            <p>Choose file:</p>
            <input type="file" name="uploaded_file" required>
            <p>Remarks:</p>
            <textarea name="remarks" placeholder="Enter remarks..." rows="3" style="width:100%;"></textarea>
            <br><br>
            <input type="submit" value="Upload" name="upload_btn" class="btn">
         </form>
      </div>

      <!-- Display Uploads -->
      <div class="box">
         <h3>Your Uploads</h3>
         <?php
            $get_uploads = $conn->prepare("SELECT * FROM uploads WHERE admin_id = ? ORDER BY uploaded_at DESC");
            $get_uploads->execute([$admin_id]);
            if($get_uploads->rowCount() > 0){
               while($upload = $get_uploads->fetch(PDO::FETCH_ASSOC)){
         ?>
            <p><strong><?= htmlspecialchars($upload['file_name']) ?></strong><br>
            <?= htmlspecialchars($upload['remarks']) ?><br>
            <a href="<?= $upload['file_path'] ?>" target="_blank">View File</a></p>
            <hr>
         <?php } } else { echo "<p>No uploads yet.</p>"; } ?>
      </div>

   </div>

</section>
<!-- dashboard section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>

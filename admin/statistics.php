<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

// Fetch the total number of rooms booked from the bookings table
$select_total_rooms = $conn->prepare("SELECT SUM(rooms) AS total_rooms_booked FROM `bookings`");
$select_total_rooms->execute();
$total_rooms_data = $select_total_rooms->fetch(PDO::FETCH_ASSOC);

// Get the sum of all booked rooms (if any bookings exist)
$total_rooms_booked = $total_rooms_data['total_rooms_booked'] ?? 0;

// Set the total number of rooms available in the hotel
$max_rooms = 30; // Example, adjust if you want to change this

// Calculate available rooms by subtracting booked rooms from total rooms available
$available_rooms = $max_rooms - $total_rooms_booked;

// Fetch all bookings from the bookings table
$select_bookings = $conn->prepare("SELECT * FROM `bookings`");
$select_bookings->execute();
$bookings = $select_bookings->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables to track occupancy
$rooms_with_kids = 0;
$rooms_without_kids = 0;

foreach ($bookings as $booking) {
   // If there are children in the booking, increase the rooms_with_kids counter
   if ($booking['childs'] > 0) {
      $rooms_with_kids++;
   } else {
      $rooms_without_kids++;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Statistics</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <!-- Chart.js -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <style>
      /* Updated styling for larger font */
      body {
         font-size: 16px;
         font-family: 'Arial', sans-serif;
      }

      .statistics {
         padding: 30px 20px;
         background-color: #fff;
         border-radius: 8px;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
         margin: 20px;
      }

      h1.heading {
         text-align: center;
         margin: 20px 0;
         font-size: 3rem; /* Larger font size */
         color: #333;
      }

      .box-container {
         display: flex;
         justify-content: space-around;
         flex-wrap: wrap;
         gap: 20px;
         margin-top: 20px;
      }

      .box {
         background-color: #fff;
         border-radius: 8px;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
         padding: 20px;
         width: 220px;
         text-align: center;
         transition: all 0.3s ease;
      }

      .box:hover {
         transform: scale(1.05);
         box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
      }

      .box h3 {
         font-size: 2rem; /* Larger font size */
         color: #444;
      }

      .box p {
         font-size: 1.3rem; /* Slightly larger font */
         margin: 10px 0;
         color: #777;
      }

      .box a {
         display: inline-block;
         margin-top: 15px;
         padding: 10px 20px;
         background-color: #007bff;
         color: white;
         text-decoration: none;
         border-radius: 5px;
         font-weight: bold;
         transition: background-color 0.3s ease;
      }

      .box a:hover {
         background-color: #0056b3;
      }

      .additional-info {
         margin-top: 20px;
         padding: 10px;
         font-size: 1.5rem; /* Larger font size */
      }

      /* Chart container styling */
      .chart-container {
         max-width: 600px;
         margin: 40px auto;
         text-align: center;
      }

      /* Print Styles */
      @media print {
         body {
            font-family: 'Arial', sans-serif;
         }
         .btn-container,
         .box-container {
            display: none;
         }

         .statistics {
            margin-top: 20px;
            padding: 10px;
         }

         .statistics .box {
            width: 100%;
            margin: 10px 0;
            box-shadow: none;
         }

         .statistics .box h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
         }

         .statistics .box p {
            font-size: 1.8rem;
         }

         .statistics h1 {
            font-size: 3rem;
            text-align: center;
         }

         .statistics .additional-info {
            margin-top: 20px;
            padding: 10px;
            font-size: 1.5rem;
         }

         .statistics .additional-info p {
            margin-bottom: 20px;
         }
      }
   </style>
</head>
<body>

<!-- header section starts -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- statistics section -->
<section class="statistics">
   <h1 class="heading">Booking Statistics</h1>

   <div class="box-container">
      <div class="box">
         <h3>Total Rooms</h3>
         <p><?= $max_rooms; ?> rooms</p>
      </div>

      <div class="box">
         <h3>Rooms Occupied</h3>
         <p><?= $total_rooms_booked; ?> rooms</p>
      </div>

      <div class="box">
         <h3>Rooms Available</h3>
         <p><?= $available_rooms; ?> rooms</p>
      </div>

      <div class="box">
         <h3>Bookings with Kids</h3>
         <p><?= $rooms_with_kids; ?> rooms</p>
      </div>

      <div class="box">
         <h3>Bookings without Kids</h3>
         <p><?= $rooms_without_kids; ?> rooms</p>
      </div>
   </div>

   <!-- Pie Chart for Occupied vs Available Rooms -->
   <div class="chart-container">
      <canvas id="occupiedVsAvailableChart"></canvas>
   </div>

   <!-- Pie Chart for Bookings with Kids vs Bookings without Kids -->
   <div class="chart-container">
      <canvas id="withVsWithoutKidsChart"></canvas>
   </div>

   <!-- Additional Information Section -->
   <div class="additional-info">
      <h3>Statistics Overview:</h3>
      <p>
         As of the current booking data, we have a total of <strong><?= $max_rooms; ?> rooms</strong> in our hotel.
         Out of these, <strong><?= $total_rooms_booked; ?> rooms</strong> are occupied, leaving <strong><?= $available_rooms; ?> rooms</strong> available.
         Among the occupied rooms, <strong><?= $rooms_with_kids; ?> Bookings</strong> are occupied by families with children, while <strong><?= $rooms_without_kids; ?> Bookings</strong> are booked by guests without children.
      </p>
      <p>
         The available rooms can be assigned to new bookings, and we encourage new reservations to ensure maximum occupancy.
         <br><br>
         For the upcoming period, it is advisable to consider room adjustments based on guest preferences (e.g., whether they have children or not) to ensure a smooth check-in process.
      </p>
   </div>

   <!-- Print Button -->
   <div class="btn-container">
      <a href="javascript:void(0);" onclick="window.print();">Print Statistics</a>
   </div>
</section>

<script>
   // Data for the Occupied vs Available Rooms Pie Chart
   var occupiedVsAvailableData = {
      labels: ['Occupied Rooms', 'Available Rooms'],
      datasets: [{
         data: [<?= $total_rooms_booked; ?>, <?= $available_rooms; ?>],
         backgroundColor: ['#FF6384', '#36A2EB'],
         hoverBackgroundColor: ['#FF6384', '#36A2EB']
      }]
   };

   // Data for the Bookings with Kids vs Bookings without Kids Pie Chart
   var withVsWithoutKidsData = {
      labels: ['Bookings with Kids', 'Bookings without Kids'],
      datasets: [{
         data: [<?= $rooms_with_kids; ?>, <?= $rooms_without_kids; ?>],
         backgroundColor: ['#FFCE56', '#4BC0C0'],
         hoverBackgroundColor: ['#FFCE56', '#4BC0C0']
      }]
   };

   // Configuration for the Occupied vs Available Rooms Pie Chart
   var ctx1 = document.getElementById('occupiedVsAvailableChart').getContext('2d');
   var occupiedVsAvailableChart = new Chart(ctx1, {
      type: 'pie',
      data: occupiedVsAvailableData,
      options: {
         responsive: true,
         plugins: {
            legend: {
               position: 'top',
            },
            tooltip: {
               callbacks: {
                  label: function(tooltipItem) {
                     return tooltipItem.label + ': ' + tooltipItem.raw + ' rooms';
                  }
               }
            }
         }
      }
   });

   // Configuration for the Bookings with Kids vs Bookings without Kids Pie Chart
   var ctx2 = document.getElementById('withVsWithoutKidsChart').getContext('2d');
   var withVsWithoutKidsChart = new Chart(ctx2, {
      type: 'pie',
      data: withVsWithoutKidsData,
      options: {
         responsive: true,
         plugins: {
            legend: {
               position: 'top',
            },
            tooltip: {
               callbacks: {
                  label: function(tooltipItem) {
                     return tooltipItem.label + ': ' + tooltipItem.raw + ' rooms';
                  }
               }
            }
         }
      }
   });
</script>

</body>
</html>

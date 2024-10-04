<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "machine_health_monitoring";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest 6 entries for current and voltage
$sql_latest = "SELECT current_value, voltage_value,rpm_value,temperature_value,noise_value FROM sensordata ORDER BY id DESC LIMIT 7";
$result_latest = $conn->query($sql_latest);

// Initialize arrays for current and voltage data
$current_data = [];
$voltage_data = [];
$rpm_data = [];
$temperature_data = [];
$noise_data = [];

if ($result_latest->num_rows > 0) {
    while ($row = $result_latest->fetch_assoc()) {
        // Store the latest current and voltage values
        $current_data[] = $row['current_value'];
        $voltage_data[] = $row['voltage_value'];
        $rpm_data[] = $row['rpm_value'];
        $temperature_data[] = $row['temperature_value'];
        $noise_data[] = $row['noise_value'];
    }
    $avg_voltage = array_sum($voltage_data) / count($voltage_data);
    $avg_rpm = array_sum($rpm_data) / count($rpm_data);
    $avg_temperature = array_sum($temperature_data) / count($temperature_data);
    $avg_noise = array_sum($noise_data) / count($noise_data);
    $max_current_value = max($current_data);
    $max_rpm_value = max($rpm_data);
    $max_temperature_value = max($temperature_data);

    // Define normal room levels (you can adjust these based on your expected normal levels)
    $normal_voltage = 220.0;
    $normal_rpm = 1000.0;
    $normal_temperature = 33.0;
    $normal_noise = 80.0;

    // Generate feedback based on the average values
    $feedback = "";

    if ($avg_voltage > $normal_voltage) {
      $feedback .= "Voltage is higher than normal by " . ($avg_voltage - $normal_voltage) . "V.<br>";
  } elseif ($avg_voltage < $normal_voltage) {
      $feedback .= "Voltage is lower than normal by " . ($normal_voltage - $avg_voltage) . "V.<br>";
  }

  if ($avg_rpm > $normal_rpm) {
      $feedback .= "RPM is higher than normal by " . ($avg_rpm - $normal_rpm) . " RPM.<br>";
  } elseif ($avg_rpm < $normal_rpm) {
      $feedback .= "RPM is lower than normal by " . ($normal_rpm - $avg_rpm) . " RPM.<br>";
  }

  if ($avg_temperature > $normal_temperature) {
      $feedback .= "Temperature is higher than normal by " . ($avg_temperature - $normal_temperature) . "°C.<br>";
  } elseif ($avg_temperature < $normal_temperature) {
      $feedback .= "Temperature is lower than normal by " . ($normal_temperature - $avg_temperature) . "°C.<br>";
  }

  if ($avg_noise > $normal_noise) {
      $feedback .= "Noise level is higher than normal by " . ($avg_noise - $normal_noise) . " dB.<br>";
  } elseif ($avg_noise < $normal_noise) {
      $feedback .= "Noise level is lower than normal by " . ($normal_noise - $avg_noise) . " dB.<br>";
  }

} else {
    echo "No data found for current, voltage, RPM, temperature, or noise.";
    $avg_voltage = 0;
    $avg_rpm = 0;
    $avg_temperature = 0;
    $avg_noise = 0;
    $feedback = "No data available.";
    $max_current_value = 0; // Default value in case of no data
    $max_rpm_value = 0; // Default value in case of no data
    $max_temperature_value = 0; // Default value in case of no data
}
   
   

// Close connection
$conn->close();

// Convert PHP arrays to JSON for JavaScript
$current_data_json = json_encode(array_reverse($current_data)); // Reverse to maintain ascending order
$voltage_data_json = json_encode(array_reverse($voltage_data)); // Reverse to maintain ascending order
$rpm_data_json = json_encode(array_reverse($rpm_data)); // Reverse to maintain ascending order
$temperature_data_json = json_encode(array_reverse($temperature_data)); // 
$noise_data_json = json_encode(array_reverse($noise_data)); //                  
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Charts</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="assets/img/drill.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    body {
      background: url('bg1.png') no-repeat center center fixed;
      background-size: cover;
    }
  </style>
</head>
<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        
        <span class="d-none d-lg-block">Machine Monitor</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="pages-error-404.html">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->    

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->
        <li class="nav-item dropdown pe-3">
      </ul>
    </nav><!-- End Icons Navigation -->
  </header><!-- End Header -->


  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link collapsed" href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->      
      <li class="nav-item">
        <a class="nav-link " data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Charts</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          <li>
            <a href="charts-chartjs.html" class="active">
              <i class="bi bi-circle"></i><span>Chart.js</span>
            </a>
          </li>
      
      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.html">
          <i class="bi bi-person"></i>
          <span>Developers  Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-register.html">
          <i class="bi bi-card-list"></i>
          <span>Register</span>
        </a>
      </li><!-- End Register Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-login.html">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Login</span>
        </a>
      </li><!-- End Login Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-error-404.html">
          <i class="bi bi-dash-circle"></i>
          <span>Error 404</span>
        </a>
      </li><!-- End Error 404 Page Nav -->
    </ul>
  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Machine Monitor</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home / </a></li>
          <li class="new"> Charts</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <p class = "white" >Below charts shows all the data released from the sensors : </p>

    <div class="row">
      <!-- Temperature Card -->
      <div class="col-md-4">
        <div class="card info-card sales-card">
          <div class="card-body">
            <h5 class="card-title">Temperature <span>| Maximum</span></h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-thermometer-half"></i>
              </div>
              <div class="ps-3">
                <h6><?php echo  $max_temperature_value; ?></h6>
                <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      <!-- RPM Card -->
      <div class="col-md-4">
        <div class="card info-card revenue-card">
          <div class="card-body">
            <h5 class="card-title">Rotation Per Minute <span>| Maximum</span></h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-speedometer2"></i>
              </div>
              <div class="ps-3">
                <h6><?php echo $max_rpm_value; ?></h6>
                <span class="text-danger small pt-1 fw-bold">8%</span> <span class="text-muted small pt-2 ps-1">increase</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      <!-- Current Card -->
      <!-- Display the maximum current value in the card -->
        <div class="col-md-4">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Current <span>| Maximum</span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="ps-3">
                            <h6><?php echo $max_current_value; ?></h6>
                            <span class="text-danger small pt-1 fw-bold">12%</span>
                            <span class="text-muted small pt-2 ps-1">decrease</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Current Card -->

    <section class="section">
      <div class="row">
    
        <!-- Current & Voltage versus Time Card -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Current & Voltage versus Time</h5>
              <!-- Line Chart -->
              <canvas id="lineChart" style="max-height: 400px;"></canvas>
              <script>
                  document.addEventListener("DOMContentLoaded", () => {
                      // Parse the PHP data passed to JavaScript
                      const currentData = <?php echo $current_data_json; ?>;
                      const voltageData = <?php echo $voltage_data_json; ?>;

                      new Chart(document.querySelector('#lineChart'), {
                          type: 'line',
                          data: {
                              labels: ['0sec', '10sec', '20sec', '30sec', '40sec', '50sec', '60sec'], // Time or X-axis labels
                              datasets: [
                                  {
                                      label: 'Current',
                                      data: currentData, // Values for Current from PHP
                                      fill: false,
                                      borderColor: 'rgb(75, 192, 192)', // Line color for Current
                                      tension: 0.1
                                  },
                                  {
                                      label: 'Voltage',
                                      data: voltageData, // Values for Voltage from PHP
                                      fill: false,
                                      borderColor: 'rgb(255, 99, 132)', // Line color for Voltage
                                      tension: 0.1
                                  }
                              ]
                          },
                          options: {
                              scales: {
                                  y: {
                                      beginAtZero: true // Y-axis starts at zero
                                  }
                              }
                          }
                      });
                  });
              </script>
              <!-- End Line Chart -->
            </div>
          </div>
        </div>
    
        <!-- Two Cards Side by Side: Rotation Per Minute and Temperature -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Rotation Per Minute</h5>
              <!-- Bar Chart -->
              <canvas id="barChart" style="max-height: 400px;"></canvas>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                 // Parse the PHP data passed to JavaScript
                 const rpmData = <?php echo $rpm_data_json; ?>;
                  new Chart(document.querySelector('#barChart'), {
                    type: 'bar',
                    data: {
                      labels: ['0sec', '10sec', '20sec', '30sec', '40sec', '50sec', '60sec'], // Time or X-axis labels
                      datasets: [{
                        label: 'RPM',
                        data: rpmData,
                        backgroundColor: [
                          'rgba(255, 99, 132, 0.2)',
                          'rgba(255, 159, 64, 0.2)',
                          'rgba(255, 205, 86, 0.2)',
                          'rgba(75, 192, 192, 0.2)',
                          'rgba(54, 162, 235, 0.2)',
                          'rgba(153, 102, 255, 0.2)',
                          'rgba(201, 203, 207, 0.2)'
                        ],
                        borderColor: [
                          'rgb(255, 99, 132)',
                          'rgb(255, 159, 64)',
                          'rgb(255, 205, 86)',
                          'rgb(75, 192, 192)',
                          'rgb(54, 162, 235)',
                          'rgb(153, 102, 255)',
                          'rgb(201, 203, 207)'
                        ],
                        borderWidth: 1
                      }]
                    },
                    options: {
                      scales: {
                        y: {
                          beginAtZero: true
                        }
                      }
                    }
                  });
                });
              </script>
              <!-- End Bar Chart -->
            </div>
          </div>
        </div>
    
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Temperature</h5>
              <!-- Line Chart -->
              <canvas id="doughnutChart" style="max-height: 400px;"></canvas>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  // Parse the PHP data passed to JavaScript
                  const temperatureData = <?php echo $temperature_data_json; ?>;
                  const noiseData = <?php echo $noise_data_json; ?>;
                  new Chart(document.querySelector('#doughnutChart'), {
                    type: 'line',
                    data: {
                      labels: ['0sec', '10sec', '20sec', '30sec', '40sec', '50sec', '60sec'], // Time or X-axis labels
                      datasets: [
                                  {
                                      label: 'noise',
                                      data: noiseData, // Values for Current from PHP
                                      fill: false,
                                      borderColor: 'rgb(75, 192, 192)', // Line color for Current
                                      tension: 0.1
                                  },
                        
                                  {
                                  label: 'Temperature',
                                  data: temperatureData,
                                  backgroundColor: [
                                    'rgb(255, 99, 132)',
                                    'rgb(54, 162, 235)',
                                    'rgb(255, 205, 86)'
                                  ],
                                  hoverOffset: 4
                                }]
                    }
                  });
                });
              </script>
              <!-- End Line Chart -->
            </div>
          </div>
        </div>
    
        <!-- Pie Chart Card (Long) -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Avg of All Parameters</h5>
              <p><?php echo $feedback; ?></p> <!-- Feedback from PHP -->
              
              <!-- Pie Chart -->
              <canvas id="pieChart" style="max-height: 400px;"></canvas>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  const avgVoltage = <?php echo $avg_voltage; ?>;
                  const avgRpm = <?php echo $avg_rpm; ?>;
                  const avgTemperature = <?php echo $avg_temperature; ?>;
                  const avgNoise = <?php echo $avg_noise; ?>;

                  new Chart(document.querySelector('#pieChart'), {
                    type: 'pie',
                    data: {
                      labels: ['Avg Voltage (V)', 'Avg RPM', 'Avg Temperature (°C)', 'Avg Noise (dB)'],
                      datasets: [{
                        label: 'Average Parameters',
                        data: [avgVoltage, avgRpm, avgTemperature, avgNoise],
                        backgroundColor: [
                          'rgb(255, 99, 132)',
                          'rgb(54, 162, 235)',
                          'rgb(255, 205, 86)',
                          'rgb(75, 192, 192)'
                        ],
                        hoverOffset: 4
                      }]
                    }
                  });
                });
              </script>
              <!-- End Pie Chart -->
            </div>
          </div>
        </div>

    
      </div>
    </section>
    

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright"><strong><span>Machine Monitor</span></strong>. All Rights Reserved
    </div>
  </footer>
  <!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
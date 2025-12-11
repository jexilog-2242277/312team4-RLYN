<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - View Accounts</title>
  <link rel="stylesheet" href="../css/dashboardstyle.css"> 
  <link rel="stylesheet" href="../css/view_accounts_style.css"> 
</head>
<body>

   <header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
    </div>
    <div class="search-container">
      <input type="text" id="searchInput" class="search-input" placeholder="Search Accounts...">
    </div>
  </header>

  <div class="main-container">
    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="upload.php">Upload Documents</a></li>
          <li><a href="view_accounts.php" class="active">View Accounts</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <section class="content-area">
      <div class="card">
        <h2>Registered Accounts</h2>
        
        <div class="table-container">
          <table id="accounts-table">
            <thead>
              <tr>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Organization</th>
              </tr>
            </thead>

            <tbody>
              <!-- STATIC PLACEHOLDER ROWS -->
              <tr>
                <td>Mariel Santos</td>
                <td>mariel_gcs@slu.edu.ph</td>
                <td>Adviser</td>
                <td>SCHEMA</td>
              </tr>

            </tbody>

          </table>
        </div>
      </div>
    </section>
  </div>

</body>
</html>

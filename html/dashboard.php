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
  <title>Organization Dashboard</title>
  <link rel="stylesheet" href="../css/dashboardstyle.css">
</head>
<body>

  <header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
    </div>
    <div class="search-container">
      <input type="text" id="searchInput" class="search-input" placeholder="Search activities...">
    </div>
  </header>

  <div class="main-container">

    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php" class="active">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="view_accounts.php">View Accounts</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <section class="content-area">
      <div class="stats-grid">
        <div class="stat-card">
          <p>Total Activities</p>
          <p class="stat-number" id="totalActivities">0</p>
        </div>
        <div class="stat-card">
          <p>Total Documents</p>
          <p class="stat-number" id="totalDocuments">0</p>
        </div>
      </div>

      <div class="card">
        <h2>Activities</h2>
        <div class="activities-content" id="activities"></div>
      </div>

      <div class="card activity-overview">
        <h2>Documents</h2>
        <div class="documents-body" id="documents"></div>
      </div>

    </section>
  </div>

  <!-- MODAL -->
  <div id="modal" class="hidden"
       style="position: fixed; top:0; left:0; width:100%; height:100%;
              background: rgba(0,0,0,0.4); display:none; align-items:center;
              justify-content:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:400px;">
      <h3 id="modalTitle">Activity Details</h3>
      <div id="modalContent"></div>
      <button id="closeModal" style="margin-top:10px;">Close</button>
    </div>
  </div>

  <script src="../js/dashboard.js"></script>
</body>
</html>

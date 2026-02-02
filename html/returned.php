<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}

// Determine the Dashboard Title based on Role
$userRole = $_SESSION['role'] ?? 'organization'; 
$dashboardTitle = "";

if ($userRole === 'osas') {
    $dashboardTitle = "";
} elseif ($userRole === 'admin') {
    $dashboardTitle = "Admin Dashboard";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Returned Files - <?php echo $dashboardTitle; ?></title> 
  <link rel="stylesheet" href="../css/dashboardstyle.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* --- Table Styling --- */
    .returned-table { width: 100%; border-collapse: collapse; background: #fff; }
    .returned-table td { padding: 20px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }

    /* --- Custom Header Replacement --- */
    .table-header-row {
        background-color: #0E0465;
        display: flex;
        padding: 12px 15px;
        border-radius: 8px 8px 0 0;
    }
    .header-item {
        color: white;
        font-weight: bold;
        font-size: 14px;
        text-transform: none;
    }

    /* --- Activity Item Layout --- */
    .activity-info-box { display: flex; flex-direction: column; gap: 6px; }
    .activity-two-col { display: flex; justify-content: flex-start; gap: 30px; }
    .activity-two-col div { font-size: 13px; color: #333; min-width: 150px; }
    .activity-two-col strong { color: #000; }

    /* --- Reason Column --- */
    .reason-box { 
        background-color: #fffafa; 
        border-left: 3px solid #dc3545; 
        padding: 12px; 
        border-radius: 4px; 
        color: #d93025; 
        font-size: 13px; 
        font-style: italic;
        line-height: 1.5;
    }

    /* --- Action Buttons (Simplified) --- */
    .action-btn-container { 
        display: flex; 
        gap: 5px; 
        justify-content: flex-end; 
        padding-right: 10px; 
    }
    /* No custom button CSS - using browser defaults */
  </style>
</head>
<body>

  <header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
      <span style="color: white; font-weight: bold; margin-left: 15px; font-size: 1.2rem;">
        <?php echo $dashboardTitle; ?>
      </span>
    </div>
    
    <div class="search-container">
      <div class="notification-wrapper">
          <div class="bell-trigger" id="bellIcon">
              <span class="material-icons">notifications</span>
          </div>
      </div>
      <input type="text" id="searchInput" class="search-input" placeholder="Search">
    </div>
  </header>

  <div class="main-container">
    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="upload.php">Upload Documents</a></li>
          <li><a href="returned.php" class="active">Returned Files</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <section class="content-area">
      <div class="stats-grid">
        <div class="filter">
          <div class="filter-controls">
            <button type="button" class="filter-btn active" id="btnAll">All</button>
            <button type="button" class="filter-btn" id="btnOrg">Org</button>
            <button type="button" class="filter-btn" id="btnYear">Year</button>
            <button type="button" class="filter-btn" id="btnSDG">SDGs</button>
            <button type="button" class="filter-btn apply-btn" id="btnApply" style="margin-left: 20px; background-color: #28a745; color: white;">Apply</button>
            <button type="button" class="filter-btn clear-btn" id="btnClear" style="background-color: #dc3545; color: white;">Clear</button>
          </div>
        </div>

        <div class="stat-card">
          <p>Returned Total</p>
          <p class="stat-number" id="totalReturned">1</p>
        </div>
      </div>

      <div class="card combined-card" style="padding: 0; background: transparent; border: none;">
        <div class="table-header-row">
            <div class="header-item" style="flex: 4.5;">Activity Details</div>
            <div class="header-item" style="flex: 4;">Reason for Return</div>
            <div class="header-item" style="flex: 1.5; text-align: right; padding-right: 20px;">Actions</div>
        </div>

        <div class="tab-content" style="padding: 0; background: #fff; border: 1px solid #0E0465; border-top: none; border-radius: 0 0 8px 8px;">
          <div class="activities-content">
            <table class="returned-table">
                <tbody id="returnedItems">
    </tbody>

<script src="../js/returned.js"></script>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="../js/dashboard.js"></script>
</body>
</html>
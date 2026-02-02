<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}

// Determine the Dashboard Title based on Role
$userRole = $_SESSION['role'] ?? 'organization'; 
$dashboardTitle = $userRole === 'admin' ? "Admin Dashboard" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Returned Files - <?php echo $dashboardTitle; ?></title> 
  <link rel="stylesheet" href="../css/dashboardstyle.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .returned-table { width: 100%; border-collapse: collapse; background: #fff; }
    .returned-table td { padding: 20px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }

    .table-header-row {
        background-color: #0E0465;
        display: flex;
        padding: 12px 15px;
        border-radius: 8px 8px 0 0;
    }
    .header-item { color: white; font-weight: bold; font-size: 14px; }

    .activity-info-box { display: flex; flex-direction: column; gap: 6px; }
    .activity-two-col { display: flex; gap: 30px; }
    .activity-two-col div { font-size: 13px; color: #333; min-width: 150px; }

    .reason-box { 
        background-color: #fffafa; 
        border-left: 3px solid #dc3545; 
        padding: 12px; 
        border-radius: 4px; 
        color: #d93025; 
        font-size: 13px; 
        font-style: italic;
    }

    .action-btn-container { display: flex; gap: 5px; justify-content: flex-end; }
  </style>
</head>
<body>
<header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
      <span style="color: white; font-weight: bold; margin-left: 15px;"><?php echo $dashboardTitle; ?></span>
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
    <div class="card combined-card" style="padding: 0; background: transparent; border: none;">
      <div class="table-header-row">
          <div class="header-item" style="flex: 4.5;">Name / Details</div>
          <div class="header-item" style="flex: 4;">Reason for Return</div>
          <div class="header-item" style="flex: 1.5; text-align: right; padding-right: 20px;">Actions</div>
      </div>

      <div class="tab-content" style="padding: 0; background: #fff; border: 1px solid #0E0465; border-top: none; border-radius: 0 0 8px 8px;">
        <div class="activities-content">
          <table class="returned-table">
            <tbody id="returnedItems"></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Edit / Resubmit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background: rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:2000;">
  <div style="background:#fff; padding:20px; border-radius:10px; width:450px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
      <h3 id="editModalTitle" style="color: #0E0465; margin-bottom: 15px; border-bottom: 2px solid #0E0465;"></h3>
      <div id="editModalContent" style="line-height:1.6; color:#333;"></div>
      <button id="closeEditModal" style="margin-top:20px; padding:8px 20px; cursor:pointer; background:#0E0465; color:white; border:none; border-radius:4px;">Close</button>
  </div>
</div>

<script src="../js/dashboard.js"></script>
<script src="../js/returned.js"></script>
</body>
</html>

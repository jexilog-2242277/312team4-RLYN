<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}

// Determine the Dashboard Title based on Role
$userRole = $_SESSION['role'] ?? 'organization'; // Default to organization
$dashboardTitle = "Organization Dashboard";

if ($userRole === 'osas') {
    $dashboardTitle = "OSAS Dashboard";
} elseif ($userRole === 'admin') {
    $dashboardTitle = "Admin Dashboard";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $dashboardTitle; ?></title> 
  <link rel="stylesheet" href="../css/dashboardstyle.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
          <li><a href="dashboard.php" class="active">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="upload.php">Upload Documents</a></li>
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

          <div class="filter-panels">
            <div id="panelOrg" class="sub-panel" style="display: none; margin-top: 10px;">
              <select id="orgSelect" class="filter-select">
                <option value="">Choose Organization</option>
                <option value="SAMCIS">SAMCIS</option>
                <option value="SEA">SEA</option>
                <option value="SOM">SOM</option>
                <option value="SONAHBS">SONAHBS</option>
                <option value="SOL">SOL</option>
                <option value="STELA">STELA</option>
                <option value="University-Wide">University-Wide</option>
              </select>
            </div>

            <div id="panelYear" class="sub-panel" style="display: none; margin-top: 10px;">
              <select id="yearSelect" class="filter-select">
                <option value="">Choose Year</option>
                <option value="2024-2025">2024-2025</option>
                <option value="2023-2024">2023-2024</option>
                <option value="2022-2023">2022-2023</option>
              </select>
            </div>

            <div id="panelSDG" class="sub-panel" style="display: none; margin-top: 10px; background: white; border: 1px solid #0E0465; padding: 15px; border-radius: 8px; max-width: 350px;">
              <h4 style="margin-bottom: 10px; color: #0E0465; border-bottom: 1px solid #eee; padding-bottom: 5px;">SDG Filters</h4>
              <div class="sdg-list" style="max-height: 250px; overflow-y: auto;">
                <?php 
                $sdg_names = [
                  1 => "No Poverty", 2 => "Zero Hunger", 3 => "Good Health", 
                  4 => "Quality Education", 5 => "Gender Equality", 6 => "Clean Water",
                  7 => "Affordable Energy", 8 => "Decent Work", 9 => "Industry & Innovation",
                  10 => "Reduced Inequality", 11 => "Sustainable Cities", 12 => "Responsible Consumption",
                  13 => "Climate Action", 14 => "Life Below Water", 15 => "Life on Land",
                  16 => "Peace & Justice", 17 => "Partnerships"
                ];
                foreach($sdg_names as $num => $name): ?>
                  <div class="sdg-item" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 0;">
                    <label for="sdg<?php echo $num; ?>" style="font-size: 14px;">SDG <?php echo $num; ?>: <?php echo $name; ?></label>
                    <input type="checkbox" id="sdg<?php echo $num; ?>" value="<?php echo $num; ?>">
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <p>Total Activities</p>
          <p class="stat-number" id="totalActivities">0</p>
        </div>
        <div class="stat-card">
          <p>Total Documents</p>
          <p class="stat-number" id="totalDocuments">0</p>
        </div>
      </div>

      <div class="card combined-card">
        <div class="tab-header">
          <button type="button" class="tab-btn active" id="tabBtnActivities">Activities</button>
          <button type="button" class="tab-btn" id="tabBtnDocuments">Documents</button>
        </div>

        <div class="tab-content">
          <div id="panelActivities" class="tab-panel">
            <div class="activities-content" id="activities"></div>
          </div>

          <div id="panelDocuments" class="tab-panel" style="display: none;">
            <div class="documents-body" id="documents"></div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div id="modal" class="hidden" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index: 2000;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:450px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
      <h3 id="modalTitle" style="color: #0E0465; margin-bottom: 15px; border-bottom: 2px solid #0E0465;">Activity Details</h3>
      <div id="modalContent" style="line-height: 1.6; color: #333;"></div>
      <button id="closeModal" style="margin-top:20px; padding: 8px 20px; cursor: pointer; background: #0E0465; color: white; border: none; border-radius: 4px;">Close</button>
    </div>
  </div>

  <script src="../js/dashboard.js"></script>
</body>
</html>
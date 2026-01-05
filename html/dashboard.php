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
    <button type="button" class="filter-btn" id="btnYear">Year</button>
    <button type="button" class="filter-btn" id="btnSDG">SDGs</button>
    
    <button type="button" class="filter-btn apply-btn" id="btnApply" style="margin-left: 20px; background-color: #28a745; color: white;">Apply</button>
    <button type="button" class="filter-btn clear-btn" id="btnClear" style="background-color: #dc3545; color: white;">Clear</button>
  </div>

  <div class="filter-panels">
    <div id="panelYear" class="sub-panel" style="display: none;">
      <select id="yearSelect" class="filter-select">
        <option value="">Choose Year</option>
      </select>
    </div>

    <div id="panelSDG" class="sub-panel" style="display: none;">
      <div class="sdg-grid">
        <?php for($i = 1; $i <= 17; $i++): ?>
          <div class="sdg-item">
            <label for="sdg<?php echo $i; ?>"><?php echo $i; ?></label>
            <input type="checkbox" id="sdg<?php echo $i; ?>" value="<?php echo $i; ?>">
          </div>
        <?php endfor; ?>
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
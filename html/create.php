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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/createstyle.css" />
  <title>Project Submission | Student Org</title>
</head>
<body>
  <header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
    </div>
    <div class="search-container">
      <input type="text" class="search-input" placeholder="Search">
    </div>
  </header>

  <div class="main-container">
    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="create.php" class="active">Create Activity</a></li>
          <li><a href="upload.php">Upload Documents</a></li>
          <li><a href="view_accounts.php">View Accounts</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <main class="content-area">
      <h1 class="page-title">Project Submission Form</h1>

      <!-- FORM -->
      <form id="projectForm" method="POST">
        <section class="form-section">
          <h3>Project Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <p>Project Title</p>
              <input type="text" name="projectTitle" class="form-input" placeholder="Enter project title" required>
            </div>
            <div class="form-group">
              <p>Project Leader</p>
              <input type="text" name="projectLeader" class="form-input" placeholder="Enter leader’s name" required>
            </div>
            <div class="form-group">
              <p>Organization Name</p>
              <input type="text" name="orgName" class="form-input" placeholder="Enter organization name" required>
            </div>
            <div class="form-group">
              <p>Venue</p>
              <input type="text" name="venue" class="form-input" placeholder="Enter venue" required>
            </div>
            <div class="form-group">
              <p>Start Date</p>
              <input type="date" name="startDate" class="form-input" required>
            </div>
            <div class="form-group">
              <p>End Date</p>
              <input type="date" name="endDate" class="form-input" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
              <p>Project Description</p>
              <textarea name="description" class="form-input" rows="4" placeholder="Briefly describe your project"></textarea>
            </div>
          </div>
        </section>

        <section class="form-section">
          <h3>Aligned Sustainable Development Goals (SDGs)</h3>
          <div class="sdg-grid">
            <?php
            $sdgs = [
              "No Poverty", "Zero Hunger", "Good Health and Well-Being", "Quality Education",
              "Gender Equality", "Clean Water and Sanitation", "Affordable and Clean Energy",
              "Decent Work and Economic Growth", "Industry, Innovation, and Infrastructure",
              "Reduced Inequalities", "Sustainable Cities and Communities",
              "Responsible Consumption and Production", "Climate Action", "Life Below Water",
              "Life on Land", "Peace, Justice, and Strong Institutions", "Partnerships for the Goals"
            ];

            foreach ($sdgs as $sdg) {
              echo "<p class='sdg-option'><input type='checkbox' name='sdgs[]' value='$sdg'> $sdg</p>";
            }
            ?>
          </div>
        </section>

        <div class="form-actions">
          <button type="reset" class="btn btn-secondary">Clear</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </main>
  </div>

  <script src="../js/create.js"></script>
</body>
</html>

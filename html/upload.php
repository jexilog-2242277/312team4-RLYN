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
          <li><a href="create.php"  >Create Activity</a></li>
          <li><a href="upload.php" class="active">Upload Documents</a></li>
          <li><a href="view_accounts.php">View Accounts</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <main class="content-area">
      <h1 class="page-title">Upload a Document</h1>

            <form id="uploadForm" action="../php/upload_document.php" method="POST" enctype="multipart/form-data">
        <section class="form-section">
          <h3>Document Details</h3>
          
                    <div class="form-group">
            <p>Title</p>
            <input type="text" id="document-title" name="title" class="form-input" placeholder="Enter document title" required>
          </div>
          
            <div class="form-group file-upload-group">
            <p>Select File</p>
            
            <div class="file-input-container" >
                            <input type="file" id="document-file" name="document" style="display: none;" onchange="updateFileName(this)" required>
              
                            <button type="button" onclick="document.getElementById('document-file').click()" class="btn btn-secondary custom-file-button">
                Choose File
              </button>
              
                            <input type="text" id="file-name-display" class="form-input file-name-display" placeholder="No file chosen" readonly>
            </div>
          </div>
        </section>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Upload Document</button>
        </div>
      </form>
    </main>
  </div>

  <script>
    // Function to update the file name display when a file is selected
    function updateFileName(input) {
      var fileName = input.files.length > 0 ? input.files[0].name : 'No file chosen';
      document.getElementById('file-name-display').value = fileName;
    }
  </script>
  <script src="../js/create.js"></script>
</body>
</html>
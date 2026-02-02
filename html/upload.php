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
  <title>Document Upload | Student Org</title>
  <style>
    /* * IMPORTANT: This CSS ensures only the custom button is visible 
     * while the actual file input handles the click.
     */
    .file-input-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .file-overlay-wrapper {
        position: relative; /* Base for absolute positioning */
        overflow: hidden;
        display: inline-block; 
        
        /* Ensure it inherits the button's size for perfect coverage */
        height: 40px; 
        line-height: 40px;
    }

    .file-overlay-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        cursor: pointer;
        opacity: 0; /* Make the input invisible */
        width: 100%;
        height: 100%;
    }
    /* Add basic styling for form input to match existing style */
    .form-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    .form-group p {
        font-weight: bold;
        margin-bottom: 5px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    .btn-primary {
        background-color: #007bff; /* Example primary color */
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .success {
        color: green;
    }
    .error {
        color: red;
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="header-nav">
      <?php include '../includes/header.php'; ?>
    </div>
    <div class="search-container">
        <div class="notification-wrapper">
        <div class="bell-trigger" id="bellIcon">
            <span class="material-icons">notifications</span>
        </div>
    </div>
      <input type="text" class="search-input" placeholder="Search">
    </div>
  </header>

  <div class="main-container">
    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="upload.php" class="active">Upload Documents</a></li>
          <li><a href="returned.php">Returned Files</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <main class="content-area">
      <h1 class="page-title">Upload New Activity Document</h1>
      <div id="status-message" style="display: none; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid;"></div>

      <form id="uploadForm" action="../php/upload_logic.php" method="POST" enctype="multipart/form-data">
        
        <section class="form-section">
            <h3>Document Details</h3>

            <div class="form-group">
                <p>Activity Name for this Document</p>
                <input type="text" name="new_activity_name" class="form-input" placeholder="e.g., Q3 Financial Report Preparation" required>
            </div>

            <div class="form-group">
                <p>Select Document File</p>
                <div class="file-input-container">
                    <div class="file-overlay-wrapper">
                        <button type="button" class="btn btn-secondary">Choose File</button>
                        <input type="file" name="document" id="fileInput" required onchange="updateFileNameDisplay(this)">
                    </div>
                    <span id="fileNameDisplay">No file selected</span>
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
    // Function to show the selected file name and enable form submission
    function updateFileNameDisplay(input) {
        const form = document.getElementById('uploadForm');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const submitButton = form.querySelector('button[type="submit"]');

        if (input.files && input.files.length > 0) {
            fileNameDisplay.textContent = 'Selected: ' + input.files[0].name;
            fileNameDisplay.style.color = 'green';
            submitButton.disabled = false; // Enable submit button once file is chosen
        } else {
            fileNameDisplay.textContent = 'No file selected';
            fileNameDisplay.style.color = 'red';
            submitButton.disabled = true; // Disable if no file
        }
    }

    // Display backend status messages after redirection
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const message = urlParams.get('message');
        const statusMessageDiv = document.getElementById('status-message');
        
        // Initial state: Disable submit button until a file is selected
        document.getElementById('uploadForm').querySelector('button[type="submit"]').disabled = true;

        if (status && message) {
            statusMessageDiv.style.display = 'block';
            statusMessageDiv.textContent = decodeURIComponent(message);
            
            if (status === 'success') {
                statusMessageDiv.classList.add('success');
            } else {
                statusMessageDiv.classList.add('error');
            }

            // Clean the URL parameters and hide the message after a few seconds
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('status');
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.toString());
                statusMessageDiv.style.display = 'none';
            }, 5000);
        }
    });

  </script>
</body>
</html>

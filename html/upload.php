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
     * These rules should be in '../css/createstyle.css' 
     */
    .file-input-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .file-overlay-wrapper {
        position: relative; /* Base for absolute positioning */
        overflow: hidden;
        display: inline-block; 
        
        /* Ensure it inherits the button's size for perfect coverage */
        height: 40px; /* Adjust based on your actual button height */
        line-height: 40px;
    }

    .file-overlay-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        
        /* The key properties */
        opacity: 0;       /* Makes it invisible */
        cursor: pointer;
        
        /* Ensure it covers the entire button area */
        height: 100%;
        width: 100%;
        
        /* This is crucial to hide the default text/button of the file input */
        font-size: 100px; 
    }
    
    .status-message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
    }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
  </style>
</head>
<body>
  
  <div class="main-container">
    <aside class="sidebar">
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="create.php">Create Activity</a></li>
          <li><a href="upload.php" class="active">Upload Documents</a></li>
          <li><a href="view_accounts.php">View Accounts</a></li>
          <li><a href="../php/logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <main class="content-area">
      <h1 class="page-title">Upload Documents</h1>
      
      <form id="upload-form" action="../php/upload_logic.php" method="POST" enctype="multipart/form-data">
        <div class="form-section">
          <h3>File Upload</h3>
          
          <div class="form-group file-upload-group">
            <p>Select File to Upload</p>
            <div class="file-input-container">
                
                <div class="file-overlay-wrapper">
                    
                    <button 
                        type="button" 
                        class="btn btn-primary upload-button"
                    >
                        Choose File
                    </button>
                    
                    <input 
                        type="file" 
                        id="document-file" 
                        name="document" 
                        onchange="handleFileSelect(this)" 
                        required
                    >
                </div>
                
                <span id="file-name-display">No file selected</span>
            </div>
          </div>
        </div>
        
        <input type="hidden" name="activity_id" value="1"> 
      </form>

      <div id="status-message" class="status-message" style="display: none;"></div>
    </main>
  </div>

  <script>
    // Function to handle file selection and immediate form submission
    function handleFileSelect(input) {
        const fileNameDisplay = document.getElementById('file-name-display');
        const form = document.getElementById('upload-form');
        
        if (input.files && input.files.length > 0) {
            fileNameDisplay.textContent = 'Uploading: ' + input.files[0].name + '...';
            fileNameDisplay.style.color = 'orange';

            // Submit the form immediately
            form.submit(); 
        } else {
            fileNameDisplay.textContent = 'No file selected';
        }
    }

    // Display backend status messages after redirection
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const message = urlParams.get('message');
        const statusMessageDiv = document.getElementById('status-message');
        
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
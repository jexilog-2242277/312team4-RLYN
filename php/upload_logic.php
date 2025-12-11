<?php
session_start();
// Include the database connection file
include '../includes/db_connect.php'; 

// --- Configuration ---
$uploadDir = '../uploads/documents/'; 

// Function to handle redirection with status message
function redirectWithStatus($message, $status = 'error') {
    header("Location: ../html/upload.php?status=" . urlencode($status) . "&message=" . urlencode($message));
    exit;
}

// 0. Initial Checks
if (!isset($conn) || !$conn) {
    redirectWithStatus("CRITICAL: Database connection failed. Check db_connect.php configuration.");
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['org_id'])) {
    redirectWithStatus("Authentication required. Your session may have expired.");
}

$userId = $_SESSION['user_id'];
$orgId = $_SESSION['org_id'];
$activityId = (int)($_POST['activity_id'] ?? 1); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithStatus("Invalid request method.");
}

// 1. Check/Create Upload Directory
if (!is_dir($uploadDir)) {
    // Attempt to create the directory with broad permissions (0777)
    if (!mkdir($uploadDir, 0777, true)) {
        redirectWithStatus("FILE ERROR: Failed to create upload directory. Check file system permissions for: " . $uploadDir);
    }
}

// Handle file upload
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    
    // Check if file is actually writable
    if (!is_writable($uploadDir)) {
        redirectWithStatus("PERMISSION ERROR: The directory " . $uploadDir . " is not writable by the web server.");
    }

    $fileTmpPath = $_FILES['document']['tmp_name'];
    $originalFileName = basename($_FILES['document']['name']);
    $fileSize = $_FILES['document']['size'];
    $fileType = $_FILES['document']['type'];
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    // --- 2. Find the next sequential number (Hardened SQL) ---
    // We try to find the max number using a reliable pattern match for files like 'doc%.pdf'
    $maxNumQuery = "
        SELECT COALESCE(
            MAX(
                CAST(
                    SUBSTRING(document_file_path FROM 'doc(\d+)\.') 
                AS INTEGER)
            ), 0
        ) AS max_num
        FROM documents
        WHERE org_id = $1;
    ";
    
    $maxNumResult = pg_query_params($conn, $maxNumQuery, [$orgId]);
    $nextDocNumber = 1;

    if ($maxNumResult) {
        $row = pg_fetch_assoc($maxNumResult);
        $lastNumber = (int)($row['max_num'] ?? 0); 
        $nextDocNumber = $lastNumber + 1;
    } else {
        // Fallback or detailed error message if the query fails
        $dbError = pg_last_error($conn);
        redirectWithStatus("SQL ERROR: Failed to determine next sequential document number. Detail: " . $dbError);
    }

    // --- 3. Construct the new file name and paths ---
    $newFileName = 'doc' . $nextDocNumber . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;
    
    // Document title for the database (user-facing)
    $documentTitle = $fileBaseName;

    // --- 4. Move the uploaded file ---
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        
        // --- 5. Database Insertion (Checked against all FKs) ---
       // --- 5. Database Insertion ---
        $insertQuery = "
            INSERT INTO documents (
                org_id, 
                activity_id, 
                document_name, 
                document_file_path,  -- <-- ADD THIS COLUMN NAME
                document_type, 
                uploaded_by
            )
            VALUES ($1, $2, $3, $4, $5, $6);
        ";
        
        $result = pg_query_params($conn, $insertQuery, [
            $orgId,
            $activityId,
            $documentTitle, 
            $newFileName,   // <-- This is the value for document_file_path
            $fileType,
            $userId
        ]);

        if ($result) {
            pg_close($conn);
            redirectWithStatus("File uploaded successfully as: " . $newFileName, "success");
        } else {
            // DATABASE INSERT FAILED (Likely a foreign key violation, e.g., org_id or user_id are invalid)
            $dbError = pg_last_error($conn);
            
            // CLEANUP: Remove the file to prevent orphaned storage
            if (file_exists($destPath)) {
                 unlink($destPath); 
            }
            pg_close($conn);
            redirectWithStatus("DB INSERT FAILED: Metadata could not be saved. Check if logged-in user_id/org_id are valid in your database. Detail: " . $dbError);
        }

    } else {
        // move_uploaded_file failed
        pg_close($conn);
        redirectWithStatus("FILE MOVE FAILED: Check file permissions on the target directory: " . $uploadDir);
    }

} else {
    // Handle file upload errors
    $errorMessage = "Upload failed.";
    if (isset($_FILES['document']['error'])) {
        $uploadError = $_FILES['document']['error'];
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $errorMessage = "The file is too large to upload (check php.ini settings).";
        } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
            $errorMessage = "The file was only partially uploaded.";
        } elseif ($uploadError !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = "File upload failed with error code: " . $uploadError;
        } else {
             // User loaded the page, no file selected. Exit silently.
             pg_close($conn);
             exit; 
        }
    }
    pg_close($conn);
    redirectWithStatus($errorMessage);
}
?>
<?php
session_start();
// Set headers for JSON response in case of error, before any output
header('Content-Type: application/json');

// Assuming you have this file for database connection
include '../includes/db_connect.php';

// --- Configuration ---
// Define the directory where files will be stored.
// MAKE SURE THIS DIRECTORY EXISTS AND IS WRITABLE BY THE WEB SERVER!
$uploadDir = '../uploads/documents/'; 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['org_id'])) {
    echo json_encode(["success" => false, "message" => "Authentication failed. Please log in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Check if the directory exists and try to create it if not
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(["success" => false, "message" => "Failed to create upload directory."]);
        exit;
    }
}

// Collect form data
$userId = $_SESSION['user_id'];
$orgId = $_SESSION['org_id'];
$documentTitle = trim($_POST['title'] ?? '');
$activityId = (int)($_POST['activity_id'] ?? 0);

// Basic validation
if (empty($documentTitle) || $activityId === 0) {
    echo json_encode(["success" => false, "message" => "Please provide a title and select an activity."]);
    exit;
}

// Handle file upload
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['document']['tmp_name'];
    $originalFileName = basename($_FILES['document']['name']);
    $fileSize = $_FILES['document']['size'];
    $fileType = $_FILES['document']['type'];
    
    // Get the file extension
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    // Remove file extension from original name
    $baseName = pathinfo($originalFileName, PATHINFO_FILENAME);

    // Sanitize the base name
    $baseName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $baseName);

    // Append date and time
    $dateTimeSuffix = date("Ymd_His"); // e.g., 20251217_235959

    // Construct new file name
    $newFileName = $baseName . '_' . $dateTimeSuffix . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;


    // Move the uploaded file from temporary location to its permanent destination
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        
        // --- Database Insertion ---
        // Insert a new record into the documents table using parameterized query
        $insertQuery = "
            INSERT INTO documents (
                org_id, 
                activity_id, 
                document_name, 
                document_file_path, 
                document_type, 
                uploaded_by
            )
            VALUES ($1, $2, $3, $4, $5, $6)
            RETURNING document_id;
        ";
        
        // Note: document_name is the user-friendly title, document_file_path is the unique stored name
        $result = pg_query_params($conn, $insertQuery, [
            $orgId,
            $activityId,
            $documentTitle,
            $newFileName, // Store the unique file name/path
            $fileType,
            $userId
        ]);

        if ($result) {
            $documentId = pg_fetch_result($result, 0, 0);
            echo json_encode([
                "success" => true, 
                "message" => "Document uploaded and recorded successfully!", 
                "document_id" => $documentId,
                "file_name" => $newFileName
            ]);
        } else {
            // Log the error and remove the file since the DB insertion failed
            unlink($destPath); 
            // In a real application, you'd log pg_last_error($conn)
            echo json_encode(["success" => false, "message" => "Database error: Failed to record document metadata."]);
        }

    } else {
        echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
    }

} else {
    // Handle specific file upload errors
    $errorMessage = "No file uploaded or an upload error occurred.";
    if (isset($_FILES['document']['error'])) {
        switch ($_FILES['document']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = "The uploaded file exceeds the maximum file size limit.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = "No file was selected for upload.";
                break;
            // Add other cases as needed
        }
    }
    echo json_encode(["success" => false, "message" => $errorMessage]);
}

pg_close($conn);
?>
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
$newActivityName = trim($_POST['new_activity_name'] ?? ''); 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithStatus("Invalid request method.");
}

// --- NEW VALIDATION: Ensure Activity Name is provided ---
if (empty($newActivityName)) {
    redirectWithStatus("ERROR: You must provide a name for the new Activity related to this document.");
}


// 1. Insert New Activity and get ID
$insertActivityQuery = "
    INSERT INTO activities (org_id, created_by, name, description, academic_year, semester, date_started, date_ended, sdg_relation)
    VALUES ($1, $2, $3, $4, NULL, NULL, CURRENT_DATE, CURRENT_DATE, 'Document Upload - No SDG Specified')
    RETURNING activity_id;
";

// We set default values for required columns:
// Description is set to the name, Academic Year/Semester/Dates are set to NULL/CURRENT_DATE for simplicity.
$activityDescription = "Document Upload for: " . $newActivityName;

$activityResult = pg_query_params($conn, $insertActivityQuery, [
    $orgId,
    $userId,
    $newActivityName,
    $activityDescription,
]);

if ($activityResult && pg_num_rows($activityResult) > 0) {
    // Successfully created activity. Get the new ID.
    $activityRow = pg_fetch_assoc($activityResult);
    $activityId = $activityRow['activity_id'];
} else {
    $dbError = pg_last_error($conn);
    pg_close($conn);
    redirectWithStatus("DB ERROR: Failed to create new Activity record. Detail: " . $dbError);
}


// 2. File and Directory Checks
// Check/Create Upload Directory
$uploadDir = '../uploads/documents/'; 
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        pg_close($conn);
        redirectWithStatus("FILE ERROR: Failed to create upload directory. Check file system permissions for: " . $uploadDir);
    }
}
if (!is_writable($uploadDir)) {
    pg_close($conn);
    redirectWithStatus("PERMISSION ERROR: The directory " . $uploadDir . " is not writable by the web server.");
}


// 3. Handle file upload and sequential naming
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    
    $fileTmpPath = $_FILES['document']['tmp_name'];
    $originalFileName = basename($_FILES['document']['name']);
    $fileType = $_FILES['document']['type'];
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    
    // Find the next sequential number (Hardened SQL)
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
        $dbError = pg_last_error($conn);
        pg_close($conn);
        redirectWithStatus("SQL ERROR: Failed to determine next sequential document number. Detail: " . $dbError);
    }

    // Construct the new file name and paths
    $newFileName = 'doc' . $nextDocNumber . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;
    
    // Use the sequential file name for the database document_name
    $documentTitle = $newFileName;


    // 4. Move the uploaded file
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        
        // 5. Database Insertion (Document record)
        $insertDocumentQuery = "
            INSERT INTO documents (
                org_id, 
                activity_id, 
                document_name, 
                document_file_path,  
                document_type, 
                uploaded_by
            )
            VALUES ($1, $2, $3, $4, $5, $6);
        ";
        
        $documentResult = pg_query_params($conn, $insertDocumentQuery, [
            $orgId,
            $activityId, // Use the ID from the newly created activity
            $documentTitle, 
            $newFileName,   
            $fileType,
            $userId
        ]);

        if ($documentResult) {
            pg_close($conn);
            redirectWithStatus("File uploaded successfully and linked to new activity: '" . htmlspecialchars($newActivityName) . "'. File saved as: " . $newFileName, "success");
        } else {
            // DOCUMENT DB INSERT FAILED
            $dbError = pg_last_error($conn);
            
            // CLEANUP: Remove the file to prevent orphaned storage
            if (file_exists($destPath)) {
                 unlink($destPath); 
            }
            pg_close($conn);
            redirectWithStatus("DB INSERT FAILED: Document metadata could not be saved. Detail: " . $dbError);
        }

    } else {
        // move_uploaded_file failed
        pg_close($conn);
        redirectWithStatus("FILE MOVE FAILED: Check file permissions on the target directory: " . $uploadDir);
    }

} else {
    // Handle file upload errors
    $errorMessage = "Upload failed.";
    $uploadError = $_FILES['document']['error'] ?? UPLOAD_ERR_NO_FILE;
    
    if ($uploadError !== UPLOAD_ERR_NO_FILE) {
        switch ($uploadError) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = "The file is too large to upload (check php.ini settings).";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = "The file was only partially uploaded.";
                break;
            default:
                $errorMessage = "File upload failed with error code: " . $uploadError;
        }
        pg_close($conn);
        redirectWithStatus($errorMessage);
    }
    // If UPLOAD_ERR_NO_FILE and activity name was entered, we shouldn't fail silently here.
    // However, since we are now relying on a submission button, this case should be handled by the client-side 'required' attribute.
    pg_close($conn);
    exit; 
}
?>
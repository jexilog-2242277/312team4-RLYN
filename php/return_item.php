<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

require_once "../includes/db_connect.php";

// Only OSAS and admin can return items
$userRole = $_SESSION['role'] ?? 'guest';
if ($userRole !== 'osas' && $userRole !== 'admin') {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'] ?? 'activity';
$id = $input['id'] ?? null;
$name = $input['name'] ?? 'Unknown';
$note = $input['note'] ?? '';
$osas_user_id = $_SESSION['user_id'];

if (!$id || !$note) {
    echo json_encode(["success" => false, "error" => "Missing ID or note"]);
    exit;
}

try {
    // Create returns log directory if it doesn't exist
    $returnsLogDir = "../returns_log";
    if (!is_dir($returnsLogDir)) {
        mkdir($returnsLogDir, 0755, true);
    }

    // Get the organization and other details
    $orgId = null;
    $studentId = null;

    if ($type === "activity") {
        // Get activity details
        $actResult = pg_query_params($conn,
            "SELECT activity_id, org_id FROM activities WHERE activity_id = $1",
            [$id]
        );
        $actRow = pg_fetch_assoc($actResult);
        if ($actRow) {
            $orgId = $actRow['org_id'];
        }
    } elseif ($type === "document") {
        // Get document and activity details
        $docResult = pg_query_params($conn,
            "SELECT d.document_id, d.org_id, d.activity_id, a.org_id as activity_org_id 
             FROM documents d 
             LEFT JOIN activities a ON d.activity_id = a.activity_id 
             WHERE d.document_id = $1",
            [$id]
        );
        $docRow = pg_fetch_assoc($docResult);
        if ($docRow) {
            $orgId = $docRow['org_id'] ?: $docRow['activity_org_id'];
        }
    }

    // Create a return record
    $returnRecord = [
        'return_id' => uniqid('return_', true),
        'type' => $type,
        'item_id' => $id,
        'item_name' => $name,
        'org_id' => $orgId,
        'osas_user_id' => $osas_user_id,
        'note' => $note,
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'returned'
    ];

    // Log the return to a JSON file
    $logFile = "{$returnsLogDir}/returns_" . date('Y-m-d') . ".json";
    $returns = [];
    
    if (file_exists($logFile)) {
        $returns = json_decode(file_get_contents($logFile), true) ?: [];
    }
    
    $returns[] = $returnRecord;
    file_put_contents($logFile, json_encode($returns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Store return notification in session for the student dashboard
    $sessionFile = "{$returnsLogDir}/session_returns_" . date('Y-m-d') . ".json";
    $sessionReturns = [];
    if (file_exists($sessionFile)) {
        $sessionReturns = json_decode(file_get_contents($sessionFile), true) ?: [];
    }
    
    $sessionReturns[] = [
        'return_id' => $returnRecord['return_id'],
        'type' => $type,
        'item_id' => $id,
        'item_name' => $name,
        'note' => $note,
        'returned_by' => $osas_user_id,
        'timestamp' => $returnRecord['timestamp'],
        'org_id' => $orgId
    ];
    file_put_contents($sessionFile, json_encode($sessionReturns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    echo json_encode([
        "success" => true,
        "message" => "Item returned successfully",
        "return_id" => $returnRecord['return_id']
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>

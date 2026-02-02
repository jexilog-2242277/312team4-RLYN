<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$userRole = $_SESSION['role'] ?? 'guest';
$orgId = $_SESSION['org_id'] ?? null;

try {
    $returnsLogDir = "../returns_log";
    $returnedItems = [];

    if (!is_dir($returnsLogDir)) {
        echo json_encode([
            "userRole" => $userRole,
            "returnedItems" => [],
            "totalReturned" => 0
        ]);
        exit;
    }

    // Read all return log files from the last 30 days
    $files = glob("{$returnsLogDir}/session_returns_*.json");
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            foreach ($data as $return) {
                // Filter based on user role and organization
                if ($userRole === 'student' && $return['org_id'] === $orgId) {
                    $returnedItems[] = $return;
                } elseif ($userRole === 'admin' || $userRole === 'osas') {
                    // Admins/OSAS can see all returns
                    $returnedItems[] = $return;
                }
            }
        }
    }

    // Sort by timestamp descending
    usort($returnedItems, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    echo json_encode([
        "userRole" => $userRole,
        "returnedItems" => $returnedItems,
        "totalReturned" => count($returnedItems)
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

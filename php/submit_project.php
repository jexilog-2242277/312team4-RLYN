<?php
session_start();
header('Content-Type: application/json');
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success" => false, "message" => "Not logged in."]);
  exit;
}

$userId = $_SESSION['user_id'];
$orgId = $_SESSION['org_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect and sanitize form data
  $projectTitle = trim($_POST['projectTitle'] ?? '');
  $projectLeader = trim($_POST['projectLeader'] ?? '');
  $orgName = trim($_POST['orgName'] ?? '');
  $venue = trim($_POST['venue'] ?? '');
  $startDate = $_POST['startDate'] ?? null;
  $endDate = $_POST['endDate'] ?? null;
  $description = trim($_POST['description'] ?? '');
  $sdgs = $_POST['sdgs'] ?? [];

  if ($projectTitle === '' || $orgName === '') {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    exit;
  }

  // Combine selected SDGs into a single string
  $sdg_relation = implode(', ', $sdgs);

  // Determine org_id (use session if logged in). If no org in session, try to find by name or create.
  if (!$orgId) {
    $checkQuery = "SELECT org_id, name FROM organizations WHERE LOWER(name) = LOWER($1) LIMIT 1";
    $checkResult = pg_query_params($conn, $checkQuery, [$orgName]);

    if ($checkResult && pg_num_rows($checkResult) > 0) {
      $row = pg_fetch_assoc($checkResult);
      $orgId = $row['org_id'];
      $orgNameResolved = $row['name'];
    } else {
      // Create new organization if not found
      $insertOrg = "INSERT INTO organizations (name, status) VALUES ($1, 'active') RETURNING org_id, name";
      $orgResult = pg_query_params($conn, $insertOrg, [$orgName]);

      if ($orgResult && pg_num_rows($orgResult) > 0) {
        $orgRow = pg_fetch_assoc($orgResult);
        $orgId = $orgRow['org_id'];
        $orgNameResolved = $orgRow['name'];
      } else {
        echo json_encode(["success" => false, "message" => "Unable to create or retrieve organization."]);
        exit;
      }
    }

    // Update session so further actions use this org
    $_SESSION['org_id'] = $orgId;
    $_SESSION['org_name'] = $orgNameResolved ?? $orgName;
  }

  // Insert activity
  $insertQuery = "
    INSERT INTO activities (org_id, created_by, name, description, academic_year, semester, date_started, date_ended, sdg_relation)
    VALUES ($1, $2, $3, $4, NULL, NULL, $5, $6, $7)
    RETURNING activity_id;
  ";

  $result = pg_query_params($conn, $insertQuery, [
    $orgId,
    $userId,
    $projectTitle,
    $description,
    $startDate,
    $endDate,
    $sdg_relation
  ]);

  if ($result && pg_num_rows($result) > 0) {
    $newId = pg_fetch_result($result, 0, 0);
    echo json_encode(["success" => true, "message" => "Project submitted successfully!", "new_activity_id" => $newId]);
  } else {
    // Get pg_last_error for debugging (do not expose to users in production)
    $err = pg_last_error($conn);
    error_log("Insert activity failed: " . $err);
    echo json_encode(["success" => false, "message" => "Error inserting project."]);
  }

} else {
  echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>

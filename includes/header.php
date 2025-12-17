<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default labels
$roleLabel = 'Guest';
$orgLabel = 'Office of Student Affairs';

if (!empty($_SESSION['role'])) {
    $roleLabel = htmlspecialchars($_SESSION['role']);
    // nice formatting: capitalize first letter
    $roleLabel = ucfirst($roleLabel);
}

if (!empty($_SESSION['org_name'])) {
    $orgLabel = htmlspecialchars($_SESSION['org_name']);
}
?>

<div class="header-nav">
  <img src="../images/logo.png" alt="School Logo" class="logo">
  <p><?php echo $roleLabel; ?></p>
  <p>|</p>
  <p><?php echo $orgLabel; ?></p>
</div>

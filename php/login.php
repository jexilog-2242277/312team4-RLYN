<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Optional redirect parameter
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : null;

    // Parameterized query
    $query = "SELECT * FROM users WHERE email = $1 LIMIT 1";
    $result = pg_query_params($conn, $query, array($email));

    if ($result && pg_num_rows($result) === 1) {
        $user = pg_fetch_assoc($result);

        if ($user['role'] === 'admin' || $user['role'] === 'osas') {
            header("Location: ../html/login.html?error=Admins are not allowed to log in here");
            exit;
        }
        // Plain password compare
        if ($password === $user['password']) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['org_id'] = $user['org_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Fetch organization name
            if (!empty($user['org_id'])) {
                $orgQuery = "SELECT name FROM organizations WHERE org_id = $1 LIMIT 1";
                $orgRes = pg_query_params($conn, $orgQuery, array($user['org_id']));
                if ($orgRes && pg_num_rows($orgRes) === 1) {
                    $_SESSION['org_name'] = pg_fetch_result($orgRes, 0, 0);
                }
            }

        

            // Default dashboard for non-admins or no redirect
            header("Location: ../html/dashboard.php");
            exit;
        } else {
            header("Location: ../html/login.html?error=Incorrect password");
            exit;
        }
    } else {
        header("Location: ../html/login.html?error=Email not found");
        exit;
    }
} else {
    header("Location: ../html/login.html");
    exit;
}
?>

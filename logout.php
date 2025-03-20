<?php
include_once 'includes/connection.php';
// Get current user id
$user_id = $_SESSION['id'];

// Update last seen date
$date = date('Y-m-d\TH:i:s');
$stmt = $pdo->prepare('UPDATE accounts SET last_seen = :last_seen WHERE id = :id');
$stmt->bindParam(':last_seen', $date);
$stmt->bindValue(':id', (int) $user_id, PDO::PARAM_INT);
$stmt->execute();

// Destroy the session associated with the user
session_destroy();

// If the user is remembered, delete the cookie
if (isset($_COOKIE['rememberme'])) {
    unset($_COOKIE['rememberme']);
    setcookie('rememberme', '', time() - 3600);
}
// Redirect to the login page:
header('Location: /');

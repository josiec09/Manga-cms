<?php
include __DIR__ . '/main.inc.php';
include __DIR__ . '/user.inc.php';

if (isset($_SESSION['googleCode'])) {
    header('Location: /');
    exit;
}

require_once 'includes/GoogleAuthenticator.php';
$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $ga->createSecret();
$user_id = intval($_SESSION['id']);

$stmt = $pdo->prepare('SELECT id FROM accounts WHERE id = :user_id');
$stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SESSION['id'] == $account['id']) {
    $stmt = $pdo->prepare('UPDATE accounts SET 2fcode = :2fcode WHERE id = :user_id');
    $stmt->bindParam(':2fcode', $secret);
    $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $_SESSION['2fcode'] = $secret;
    echo 'success';
} else {
    echo 'error';
}

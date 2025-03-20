<?php
include __DIR__ . '/main.inc.php';
include __DIR__ . '/user.inc.php';

$postID = $_POST['postID'];
$stmt = $pdo->prepare("SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.id = :postID");
$stmt->bindValue(':postID', (int) $postID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($result);

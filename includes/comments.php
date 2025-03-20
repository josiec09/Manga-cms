<?php
include_once __DIR__ . '/connection.php';
include __DIR__ . '/user.inc.php';
if ($_GET["act"] == 'comment') {
    if (!isset($_POST['comment'])) {
        echo "no_comment";
    } else if (!isset($_SESSION['loggedin'])) {
        echo "no_login";
    } else {
        $parent_id = htmlspecialchars($_POST['parent_id']);
        $manga_id = htmlspecialchars($_POST['manga_id']);
        $user_id = $_SESSION['id'];
        $comment = addslashes(htmlspecialchars($_POST['comment']));
        $stmt = $pdo->prepare("INSERT INTO comments (parent_id, manga_id, user_id, comment) VALUES (:parent_id, :manga_id, :user_id, :comment)");
        $stmt->bindValue(':parent_id', (int) $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment',  $comment);
        $stmt->execute();
        echo "success";
    }
}

if ($_GET["act"] == 'up') {
    $comment_id = intval($_POST['id']); // sanitize the input
    $user_id = intval($_SESSION['id']); // sanitize the input

    // Check if user has already voted on this comment
    $check_vote = $pdo->prepare("SELECT * FROM votes WHERE comment_id = :comment_id AND user_id = :user_id");
    $check_vote->bindValue(':comment_id', (int) $comment_id, PDO::PARAM_INT);
    $check_vote->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $check_vote->execute();

    if ($check_vote->rowCount() > 0) {
        echo "error";
        exit();
    }

    $upvote = $pdo->prepare("UPDATE comments SET upvote = upvote + 1 WHERE id = :id");
    $upvote->bindValue(':id', (int) $comment_id, PDO::PARAM_INT);
    $upvote->execute();

    // Add vote to votes table
    $add_vote = $pdo->prepare("INSERT INTO votes (comment_id, user_id) VALUES (:comment_id, :user_id)");
    $add_vote->bindValue(':comment_id', (int) $comment_id, PDO::PARAM_INT);
    $add_vote->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $add_vote->execute();

    echo "success";
}

if ($_GET["act"] == 'down') {
    $comment_id = intval($_POST['id']); // sanitize the input
    $user_id = intval($_SESSION['id']); // sanitize the input

    // Check if user has already voted on this comment
    $check_vote = $pdo->prepare("SELECT * FROM votes WHERE comment_id = :comment_id AND user_id = :user_id");
    $check_vote->bindValue(':comment_id', (int) $comment_id, PDO::PARAM_INT);
    $check_vote->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $check_vote->execute();

    if ($check_vote->rowCount() > 0) {
        echo "error";
        exit();
    }

    $downvote = $pdo->prepare("UPDATE comments SET downvote = downvote + 1 WHERE id = :id");
    $downvote->bindValue(':id', (int) $comment_id, PDO::PARAM_INT);
    $downvote->execute();

    // Add vote to votes table
    $add_vote = $pdo->prepare("INSERT INTO votes (comment_id, user_id) VALUES (:comment_id, :user_id)");
    $add_vote->bindValue(':comment_id', (int) $comment_id, PDO::PARAM_INT);
    $add_vote->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $add_vote->execute();

    echo "success";
}

if ($_GET["act"] == 'post') {
    if (!isset($_POST['post'])) {
        echo "no_post";
    } else if (!isset($_POST['title'])) {
        echo "no_title";
    } else if (!isset($_SESSION['loggedin'])) {
        echo "no_login";
    } else {
        $forum_id = htmlspecialchars($_POST['forum_id']);
        $user_id = $_SESSION['id'];
        $title = addslashes(htmlspecialchars($_POST['title']));
        $post = addslashes(htmlspecialchars($_POST['post']));

        $stmt = $pdo->prepare('INSERT INTO posts (forum_id, user_id, parent_id, pinned, locked, title, post, date) VALUES (:forum_id, :user_id, 0, :pinned, :locked, :title, :post, NOW())');
        $stmt->bindValue(':forum_id', $forum_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':pinned', $_POST['pinned']);
        $stmt->bindValue(':locked', $_POST['locked']);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':post', $post);
        $stmt->execute();
        echo "success";
    }
}

if ($_GET["act"] == 'reply') {
    if (!isset($_POST['reply'])) {
        echo "no_reply";
    } else if (!isset($_SESSION['loggedin'])) {
        echo "no_login";
    } else {
        $parent_id = htmlspecialchars($_POST['parent_id']);
        $forum_id = htmlspecialchars($_POST['forum_id']);
        $title = htmlspecialchars($_POST['title']);
        $user_id = $_SESSION['id'];
        $reply = addslashes(htmlspecialchars($_POST['reply']));

        $stmt = $pdo->prepare("INSERT INTO posts (parent_id, forum_id, user_id, title, post) VALUES (:parent_id, :forum_id, :user_id, :title, :reply)");
        $stmt->bindValue(':parent_id', (int) $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title',  $title);
        $stmt->bindParam(':reply',  $reply);
        $stmt->execute();
        echo "success";
    }
}

if ($_GET["act"] == 'update') {
    if (!isset($_POST['post'])) {
        echo "no_post";
    } else if (!isset($_SESSION['loggedin'])) {
        echo "no_login";
    } else {
        $topic_id = htmlspecialchars($_POST['topic_id']);
        $title = htmlspecialchars($_POST['title']);
        $post = addslashes(htmlspecialchars($_POST['post']));

        $stmt = $pdo->prepare("INSERT INTO posts (parent_id, forum_id, user_id, title, post) VALUES (:parent_id, :forum_id, :user_id, :title, :reply)");
        $stmt->bindValue(':topic_id', (int) $topic_id, PDO::PARAM_INT);
        $stmt->bindParam(':title',  $title);
        $stmt->bindParam(':post',  $post);
        $stmt->execute();
        echo "success";
    }
}

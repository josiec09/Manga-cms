<?php
// Pull forums name for breadcrumb
function ForumName($pdo, $forum_id)
{
    $forums = $pdo->prepare('SELECT forum FROM forums WHERE id = :id');
    $forums->bindValue(':id', (int) $forum_id, PDO::PARAM_INT);
    $forums->execute();
    return $forums->fetchColumn();
}
// Count the topic for the main forums page
function CountTopics($pdo, $forum_id)
{

    $topics_total = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE parent_id = 0 AND forum_id = :forum_id');
    $topics_total->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $topics_total->execute();
    return $topics_total->fetchColumn();
}
// Count the replies for the main forums page
function CountReplies($pdo, $forum_id)
{
    $reply_total1 = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE parent_id != 0 AND forum_id = :forum_id');
    $reply_total1->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $reply_total1->execute();
    return $reply_total1->fetchColumn();
}
// Count the replies for the topics page
function CountTopicReplies($pdo, $topic_id)
{
    $reply_total = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE parent_id = :topic_id');
    $reply_total->bindValue(':topic_id', (int) $topic_id, PDO::PARAM_INT);
    $reply_total->execute();
    return $reply_total->fetchColumn();
}

// Count total posts
function CountTotalPosts($pdo)
{
    $post_total = $pdo->prepare('SELECT COUNT(*) AS total FROM posts');
    $post_total->execute();
    return $post_total->fetchColumn();
}

// Count accounts posts
function CountTotalAccounts($pdo)
{
    $accounts_total = $pdo->prepare('SELECT COUNT(*) AS total FROM accounts');
    $accounts_total->execute();
    return $accounts_total->fetchColumn();
}


// Pull the last posters info
function lastPost($pdo, $forum_id)
{
    $output = '';
    $lastPost = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.forum_id = :forum_id ORDER BY posts.date DESC LIMIT 1');
    $lastPost->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $lastPost->execute();
    if ($lastPost->rowCount() > 0) {
        foreach ($lastPost as $post) {
            $output .= '' . date("F j, g:i a", strtotime($post['date'])) . '<br>';
            $output .= '<a href="/user/' . $post['username'] . '/">' . $post['username'] . '</a>';
        }
    } else {
        $output = "No posts in this forum";
    }
    return $output;
}
// Check if there are new post for the user
function newPostsCheck($pdo, $forum_id)
{
    $user_id = $_SESSION['id'];
    $stmt = $pdo->prepare("SELECT last_seen FROM accounts WHERE id = :user_id");
    $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $last_seen = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id,date FROM posts WHERE date > :last_seen AND user_id != :user_id AND forum_id = :forum_id ORDER BY posts.date DESC LIMIT 1");
    $stmt->bindParam(':last_seen', $last_seen);
    $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "There are new posts for the user";
    } else {
        echo "No new posts for the user";
    }
}
// Display the list of forums
function displayForums($pdo, $category_id)
{
    if (isset($_SESSION['loggedin'])) {
        $user_id = $_SESSION['id'];
        $accounts = $pdo->prepare("SELECT last_seen FROM accounts WHERE id = :user_id");
        $accounts->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
        $accounts->execute();
        $last_seen = $accounts->fetchColumn();

        $forums = $pdo->prepare("SELECT * FROM forums WHERE category_id = :category_id ORDER BY id");
        $forums->bindValue(':category_id', (int) $category_id, PDO::PARAM_INT);
        $forums->execute();

        foreach ($forums as $forum) {
            echo '<tr class="forum">';
            $new_post = $pdo->prepare("SELECT * FROM posts WHERE date > :last_seen AND forum_id = :forum_id ORDER BY posts.date DESC LIMIT 1");
            $new_post->bindParam(':last_seen', $last_seen);
            $new_post->bindValue(':forum_id', (int) $forum['id'], PDO::PARAM_INT);
            $new_post->execute();
            
            if ($new_post->rowCount() > 0) {
                echo '<td class="new_post"><i title="There are new posts." class="fa fa-envelope" aria-hidden="true"></i></td>';
            } else {
                echo '<td class="new_post"><i title="There are no new posts." class="fa fa-envelope-open" aria-hidden="true"></i></td>';
            }
            echo '<td class="forum_name"><a href="/forum/' . $forum['id'] . '/">' . $forum['forum'] . '</a><br>';
            echo '' . $forum['description'] . '';
            echo '</td>';
            echo '<td class="center">' . CountTopics($pdo, $forum['id']) . '<br><span class="topic_count">Topics</span></td>';
            echo '<td class="center">' . CountReplies($pdo, $forum['id']) . '<br><span class="reply_count">Replies</span></td>';
            echo '<td class="post_date">' . lastPost($pdo, $forum['id']) . '</td>';
            echo '</tr>';
        }
    } else {
        $forums = $pdo->prepare("SELECT * FROM forums WHERE category_id = :category_id ORDER BY id");
        $forums->bindValue(':category_id', (int) $category_id, PDO::PARAM_INT);
        $forums->execute();

        foreach ($forums as $forum) {
            echo '<tr class="forum">';
            echo '<td class="new_post"><i title="There are no new posts." class="fa fa-envelope-open" aria-hidden="true"></i></td>';
            echo '<td class="forum_name"><a href="/forum/' . $forum['id'] . '/">' . $forum['forum'] . '</a><br>';
            echo '' . $forum['description'] . '';
            echo '</td>';
            echo '<td class="center">' . CountTopics($pdo, $forum['id']) . '<br><span class="topic_count">Topics</span></td>';
            echo '<td class="center">' . CountReplies($pdo, $forum['id']) . '<br><span class="reply_count">Replies</span></td>';
            echo '<td class="post_date">' . lastPost($pdo, $forum['id']) . '</td>';
            echo '</tr>';
        }
    }
}
// Display the list of topics
function displayTopics($pdo, $forum_id, $per_page = 1, $page = 1)
{
    $calc_page = ($page - 1) * $per_page;

    $stmt = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.forum_id = :forum_id AND posts.parent_id = 0 AND pinned = 0 ORDER BY posts.date DESC LIMIT :limit, :offset');
    $stmt->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $topics = $stmt->fetchAll();

    // Get total records
    $stmt = $pdo->prepare('SELECT count(id) AS id FROM posts WHERE posts.forum_id = :forum_id AND posts.parent_id = 0 AND pinned = 0');
    $stmt->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $stmt->execute();
    $sql = $stmt->fetchAll();
    $total_pages = $sql[0]['id'];

    return array($topics, $total_pages);
}
// Display the list of pinned topics
function displayPinned($pdo, $forum_id)
{
    $stmt = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.forum_id = :forum_id AND posts.parent_id = 0 AND pinned = 1 ORDER BY posts.date ASC');
    $stmt->bindValue(':forum_id', (int) $forum_id, PDO::PARAM_INT);
    $stmt->execute();
    $pinned = $stmt->fetchAll();

    if (count($pinned) == !0) {
        foreach ($pinned as $pin) {
            echo '<tr class="forum">';
            if ($pin['locked'] == 1) {
                echo '<td class="new_post"><i title="PINNED & LOCKED" class="fa fa-lock" aria-hidden="true"></i></td>';
            } else {
                echo '<td class="new_post"><i title="PINNED" class="fa fa-thumb-tack" aria-hidden="true"></i></td>';
            }
            echo '<td class="topic_name">PINNED: <a href="/topic/' . $pin['id'] . '/">' . $pin['title'] . '</a><br>';
            echo '' . shapeSpace_truncate_text($pin['post']) . '&nbsp;|&nbsp;By <a href="/user/' . $pin['username'] . '/">' . $pin['username'] . '</a>';
            echo '</td>';
            echo '<td class="center"></td>';
            echo '<td class="center">' . CountTopicReplies($pdo, $pin['id']) . '<br><span class="reply_count">Replies</span></td>';
            echo '<td class="topic_date">';
            $lastPost = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.parent_id = :parent_id ORDER BY posts.date DESC LIMIT 1');
            $lastPost->bindValue(':parent_id', (int) $pin['id'], PDO::PARAM_INT);
            $lastPost->execute();
            if ($lastPost->rowCount() > 0) {
                foreach ($lastPost as $post) {
                    echo '' . date("F j, g:i a", strtotime($post['date'])) . '<br>';
                    echo '<a href="/user/' . $post['username'] . '/">' . $post['username'] . '</a>';
                }
            } else {
                echo '' . date("F j, g:i a", strtotime($pin['date'])) . '<br><a href="/user/' . $pin['username'] . '/">' . $pin['username'] . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
    }
}

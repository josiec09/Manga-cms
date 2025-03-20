<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/forums.inc.php';
include 'includes/template.php';

$forum_id = $_GET['id'];
$per_page = 15;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
list($topics, $total_pages) = displayTopics($pdo, $forum_id, $per_page, $page);

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

$stmt = $pdo->prepare("SELECT mod_only FROM forums WHERE id=:id");
$stmt->bindValue(':id', (int) $forum_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

template_header('' . ForumName($pdo, $forum_id) . '');
?>
<div id="header">
    <div class="wrap_second">
        <div class="logo"></div>
        <div class="nav">
            <ul>
                <?= random_manga($pdo); ?>
                <li><a href="/">Home</a></li>
                <li class="active"><a href="/forums/">Forums</a></li>
                <li><a href="/tags/">Tags</a></li>
                <li><a href="/artists/">Artists</a></li>
                <li><a href="/characters/">Characters</a></li>
                <li><a href="/info/">Info</a></li>
                <div class="clear"></div>
            </ul>
        </div>
        <div class="right">
            <div class="search">
                <form action="/search/" method="GET">
                    <input type="text" name="q" id="q" value="" placeholder="Search by titles, tags, artists, or characters.">
                    <button class="sbtn" type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>
            <button type="button" class="navbar-toggle collapsed" id="nav_btn">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar_links" style="display: none;">
                <ul>
                    <?= random_manga($pdo); ?>
                    <li><a href="/">Home</a></li>
                    <li><a href="/forums/">Forums</a></li>
                    <li><a href="/tags/">Tags</a></li>
                    <li><a href="/artists/">Artists</a></li>
                    <li><a href="/characters/">Characters</a></li>
                    <li><a href="/info/">Info</a></li>
                    <?php if (!isset($_SESSION['loggedin'])) { ?>
                        <li><a href="/login/">Login</a></li>
                        <li><a href="/register/"></i>Register</a></li>
                    <? } else { ?>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/favorites/">Favorites</a></li>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/"><span class="username"><?= $_SESSION['name'] ?></span></a></li>
                        <?php if ($_SESSION['role'] == '1') : ?>
                            <li><a href="/admincp/">AdminCP</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == '2') : ?>
                            <li><a href="/modcp/">ModCP</a></li>
                        <?php endif; ?>
                        <li><a href="/logout/">Logout</a></li>
                    <? } ?>
                </ul>
            </div>
            <button type="button" class="drop_btn" id="drop_btn"><i class="fa fa-arrow-down"></i></button>
            <div id="dropdown_menu" style="display: none;">
                <ul>
                    <?= random_manga($pdo); ?>
                    <li><a href="/">Home</a></li>
                    <li><a href="/forums/">Forums</a></li>
                    <li><a href="/tags/">Tags</a></li>
                    <li><a href="/artists/">Artists</a></li>
                    <li><a href="/characters/">Characters</a></li>
                    <li><a href="/info/">Info</a></li>
                </ul>
            </div>
            <div class="nav sec">
                <ul>
                    <?php if (!isset($_SESSION['loggedin'])) { ?>
                        <li><a href="/login/"><i class="fa fa-sign-in"></i>Login</a></li>
                        <li><a href="/register/"><i class="fa fa-user"></i>Register</a></li>
                    <? } else { ?>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/favorites/"><i class="fa fa-heart"></i>Favorites</a></li>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/"><i class="fa fa-user"></i><span class="username"><?= $_SESSION['name'] ?></span></a></li>
                        <?php if ($_SESSION['role'] == '1') : ?>
                            <li><a href="/admincp/"><i class="fa fa-cog"></i>AdminCP</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == '2') : ?>
                            <li><a href="/modcp/"><i class="fa fa-cog"></i>ModCP</a></li>
                        <?php endif; ?>
                        <li><a href="/logout/"><i class="fa fa-sign-out"></i>Logout</a></li>
                    <? } ?>
                    <div class="clear"></div>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div id="content">
    <div class="wrap">
        <div class="inner_content">
            <div class="container" id="info-container">
                <?php if (isset($_SESSION['loggedin'])) : ?>
                    <?php if ($row['mod_only'] == 0) { ?>
                        <div class="replyBtn">
                            <a href="#" class="btn btn-forum" id="drop_post"><i class="fa fa-book"></i> New Topic</a>
                        </div>
                        <table class="postform" id="postform">
                            <thead>
                                <tr class="category">
                                    <th colspan="2" class="start"><i class="fa fa-pencil" aria-hidden="true"></i>&nbsp;&nbsp;START A NEW DISCUSSION</th>
                                </tr>
                            </thead>
                            <form method="POST" id="post_form">
                                <tr>
                                    <td colspan="2"><input type="text" name="title" id="title" placeholder="Enter a title"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><textarea class="post-input" name="post" id="post" minlength="10" placeholder="Say something"></textarea></td>
                                </tr>
                                <?php if ($_SESSION['role'] == '1' || $_SESSION['role'] == '2') : ?>
                                    <tr>
                                        <td class="pinned">Pinned: <input type="checkbox" name="pinned" id="pinned" value="0"></td>
                                        <td class="locked">Locked: <input type="checkbox" name="locked" id="locked" value="0"></td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td><button class="btn btn-post" type="submit">Post</button></td>
                                </tr>
                                <input type="hidden" name="forum_id" id="forum_id" value="<?= $forum_id ?>" />
                            </form>
                        </table>
                    <? } else { ?>
                        <?php if ($row['mod_only'] == 1 && ($_SESSION['role'] == "1" || $_SESSION['role'] == "2")) { ?>
                            <div class="replyBtn">
                                <a href="#" class="btn btn-forum" id="drop_post"><i class="fa fa-book"></i> New Topic</a>
                            </div>
                            <table class="postform" id="postform">
                                <thead>
                                    <tr class="category">
                                        <th colspan="2" class="start"><i class="fa fa-pencil" aria-hidden="true"></i>&nbsp;&nbsp;START A NEW DISCUSSION</th>
                                    </tr>
                                </thead>
                                <form method="POST" id="post_form">
                                    <tr>
                                        <td colspan="2"><input type="text" name="title" id="title" placeholder="Enter a title"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><textarea class="post-input" name="post" id="post" minlength="10" placeholder="Say something"></textarea></td>
                                    </tr>
                                    <?php if ($_SESSION['role'] == '1' || $_SESSION['role'] == '2') : ?>
                                        <tr>
                                            <td class="pinned">Pinned: <input type="checkbox" name="pinned" id="pinned" value="0"></td>
                                            <td class="locked">Locked: <input type="checkbox" name="locked" id="locked" value="0"></td>
                                        </tr>
                                    <? endif; ?>
                                    <tr>
                                        <td><button class="btn btn-post" type="submit">Post</button></td>
                                    </tr>
                                    <input type="hidden" name="forum_id" id="forum_id" value="<?= $forum_id ?>" />
                                </form>
                            </table>
                        <? } ?>
                        <?php if ($row['mod_only'] == 1 && ($_SESSION['role'] == "3")) { ?>
                            <div class="alert alert-danger"><i class="fa fa-lock" aria-hidden="true"></i> Post threads in locked here!</div>
                        <? } ?>
                    <? } ?>
                <? endif; ?>
                <table class="forums" id="forums">
                    <thead>
                        <tr class="category">
                            <th colspan="2" class="category_name"><i class="fa fa-book color-icon"></i>&nbsp;&nbsp;<a href="/forums/">Forums</a> &raquo; <?= ForumName($pdo, $forum_id) ?></th>
                            <th></th>
                            <th></th>
                            <th class="latest_post">Latest Post</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= displayPinned($pdo, $forum_id) ?>
                        <? if (count($topics) == !0) { ?>
                            <?php
                            if (isset($_SESSION['loggedin'])) {
                                $user_id = $_SESSION['id'];
                                $accounts = $pdo->prepare("SELECT last_seen FROM accounts WHERE id = :user_id");
                                $accounts->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                                $accounts->execute();
                                $last_seen = $accounts->fetchColumn();
                            ?>
                                <?php foreach ($topics as $topic) : ?>
                                    <tr class="forum">
                                        <?php
                                        if ($topic['locked'] == 1) {
                                            echo '<td class="new_post"><i class="fa fa-lock" aria-hidden="true" title="LOCKED"></i></td>';
                                        } else {
                                            $new_post = $pdo->prepare("SELECT * FROM posts WHERE date > :last_seen AND user_id != :user_id AND parent_id = :topic_id ORDER BY posts.date DESC LIMIT 1");
                                            $new_post->bindParam(':last_seen', $last_seen);
                                            $new_post->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                                            $new_post->bindValue(':topic_id', (int) $topic['id'], PDO::PARAM_INT);
                                            $new_post->execute();
                                            if ($new_post->rowCount() > 0) {
                                                echo '<td class="new_post"><i title="There are new replies." class="fa fa-envelope" aria-hidden="true"></i></td>';
                                            } else {
                                                echo '<td class="new_post"><i title="There are no new replies." class="fa fa-envelope-open" aria-hidden="true"></i></td>';
                                            }
                                        }
                                        ?>
                                        <td class="topic_name"><a href="/topic/<?= $topic['id'] ?>/"><?= $topic['title'] ?></a><br>
                                            <?= shapeSpace_truncate_text($topic['post']) ?>&nbsp;|&nbsp;Started By: <a href="/user/<?= $topic['username'] ?>/"><?= $topic['username'] ?></a>
                                        </td>
                                        <td class="center"></td>
                                        <td class="center"><?= CountTopicReplies($pdo, $topic['id']) ?><br><span class="reply_count">Replies</span></td>
                                        <td class="topic_date">
                                            <?php
                                            $lastPost = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.parent_id = :parent_id ORDER BY posts.date DESC LIMIT 1');
                                            $lastPost->bindValue(':parent_id', (int) $topic['id'], PDO::PARAM_INT);
                                            $lastPost->execute();
                                            if ($lastPost->rowCount() > 0) {
                                                foreach ($lastPost as $post) {
                                                    echo '' . date("F j, g:i a", strtotime($post['date'])) . '<br>';
                                                    echo '<a href="/user/' . $post['username'] . '/">' . $post['username'] . '</a>';
                                                }
                                            } else {
                                                echo '' . date("F j, g:i a", strtotime($topic['date'])) . '<br><a href="/user/' . $topic['username'] . '/">' . $topic['username'] . '</a>';
                                            }
                                            ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <? } else { ?>
                                <?php foreach ($topics as $topic) : ?>
                                    <tr class="forum">
                                        <td class="new_post"><i title="There are no new replies." class="fa fa-envelope-open" aria-hidden="true"></i></td>
                                        <td class="topic_name"><? if ($topic['locked'] == 1) {
                                                                    echo '<i class="fa fa-lock" aria-hidden="true" title="LOCKED"></i>';
                                                                } ?> <a href="/topic/<?= $topic['id'] ?>/"><?= $topic['title'] ?></a><br>
                                            <?= shapeSpace_truncate_text($topic['post']) ?>&nbsp;|&nbsp;Started By: <a href="/user/<?= $topic['username'] ?>/"><?= $topic['username'] ?></a>
                                        </td>
                                        <td class="center"></td>
                                        <td class="center"><?= CountTopicReplies($pdo, $topic['id']) ?><br><span class="reply_count">Replies</span></td>
                                        <td class="topic_date">
                                            <?php
                                            $lastPost = $pdo->prepare('SELECT posts.*, accounts.username FROM posts JOIN accounts ON posts.user_id = accounts.id WHERE posts.parent_id = :parent_id ORDER BY posts.date DESC LIMIT 1');
                                            $lastPost->bindValue(':parent_id', (int) $topic['id'], PDO::PARAM_INT);
                                            $lastPost->execute();
                                            if ($lastPost->rowCount() > 0) {
                                                foreach ($lastPost as $post) {
                                                    echo '' . date("F j, g:i a", strtotime($post['date'])) . '<br>';
                                                    echo '<a href="/user/' . $post['username'] . '/">' . $post['username'] . '</a>';
                                                }
                                            } else {
                                                echo '' . date("F j, g:i a", strtotime($topic['date'])) . '<br><a href="/user/' . $topic['username'] . '/">' . $topic['username'] . '</a>';
                                            }
                                            ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?  } ?>
                        <? } else { ?>
                            <tr class="forum">
                                <td class="error" colspan="5">No Topics found! Why not post one?</td>
                            </tr>
                        <?  } ?>
                    </tbody>
                </table>
                <? pagination($total_pages, $per_page, $page, $prev, $next, '/forums/' . $forum_id . '/'); ?>
            </div>
        </div>
    </div>
</div>
<?= template_footer() ?>
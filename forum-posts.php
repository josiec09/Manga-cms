<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Retrieve additional account info from the database because we don't have them stored in sessions
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :username');
$stmt->bindParam(':username', $_GET['user']);
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($account) {
    // Fetch comments for pagination.
    $per_page = 10;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
    $calc_page = ($page - 1) * $per_page;

    $stmt = $pdo->prepare('SELECT * FROM posts WHERE user_id = :user_id ORDER BY posts.date DESC LIMIT :limit, :offset');
    $stmt->bindValue(':user_id', (int)  $account['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Get total records
    $stmt = $pdo->prepare('SELECT count(id) AS id FROM posts WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', (int)  $account['id'], PDO::PARAM_INT);
    $stmt->execute();
    $sql = $stmt->fetchAll();
    $total_pages = $sql[0]['id'];

    // Prev + Next
    $prev = $page - 1;
    $next = $page + 1;
}

if ($account) {
    template_header('' . $account['username'] . '\'s - Posts');
} else {
    template_header('No Profile Found');
}

?>
<div id="header">
    <div class="wrap_second">
        <div class="logo"></div>
        <div class="nav">
            <ul>
                <?= random_manga($pdo); ?>
                <li><a href="/">Home</a></li>
                <li><a href="/forums/">Forums</a></li>
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
                        <li <? if ($_GET['user'] == $_SESSION['name']) : ?> class="active" <? endif; ?>><a href="/user/<?= $_SESSION['name'] ?>/"><i class="fa fa-user"></i><span class="username"><?= $_SESSION['name'] ?></span></a></li>
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
            <?php if ($account) { ?>
                <div class="user_info">
                    <div class="profile-comments">
                        <h3><?= $account['username'] ?>'s Forum Posts</h3>
                    </div>
                    <div id="comments">
                        <?php foreach ($posts as $post) { ?>
                            <div class="comment" id="comment-<?= $post['id'] ?>">
                                <a class="avatar" href="/user/<?= $account['username'] ?>/"><img src="/uploads/<?= $account['avatar'] ?>"></a>
                                <div class="body-wrapper">
                                    <div class="header">
                                        <div class="left">
                                            <b><a href="/user/<?= $account['username'] ?>/"><?= $account['username'] ?></a></b>
                                            <time datetime="<?= $post['date'] ?>"><?= time_elapsed_string($post['date']) ?></time>
                                        </div>
                                        <div class="right">
                                            <?php if ($post['parent_id'] != '0') { ?>
                                                <a href="/topic/<?= $post['parent_id'] ?>/#post-<?= $post['id'] ?>">View original Post</a>
                                            <?php } else { ?>
                                                <a href="/topic/<?= $post['id'] ?>/#post-<?= $post['id'] ?>">View original Post</a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="body"><?= bbcode(nl2br($post['post'])) ?></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php pagination($total_pages, $per_page, $page, $prev, $next, '/user/' . $_GET['user'] . '/posts/'); ?>
                </div>
            <?php } else { ?>
                <div class="user_info">
                    <div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?= $_GET['user'] ?> is not a user here!</div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Retrieve additional account info from the database because we don't have them stored in sessions
//$stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :username');
$stmt = $pdo->prepare('
    SELECT accounts.*, roles.role 
    FROM accounts 
    JOIN roles ON accounts.role_id = roles.id 
    WHERE accounts.username = :username
');
$stmt->bindParam(':username', $_GET['user']);
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);
if ($account) {
$stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = :user_id');
$stmt->bindValue(':user_id', (int) $account['id'], PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE user_id = :user_id');
$stmt->bindValue(':user_id', (int) $account['id'], PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchColumn();
}


if ($account) {
    template_header('' . $account['username'] . '');
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
                    <div class="left">
                        <div class="cover">
                            <img src="/uploads/<?= $account['avatar'] ?>" />
                        </div>
                        <?php if (isset($_SESSION['loggedin'])) : ?>
                            <?php if ($_SESSION['name'] == $account['username']) : ?>
                                <button onClick="parent.location='/user/<?= $account['username'] ?>/edit/'" id="settings"><i class="fa fa-cog"></i> Settings</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="right">
                        <div class="info">
                            <h1><?= $account['username'] ?></h1>
                            <h2><?= bbcode(nl2br($account['about'] ?? '')) ?></h2>
                            <div class="profile-list">
                                <h3>Role: <?= $account['role'] ?></h3>
                                <div class="clear"></div>
                            </div>
                            <div class="profile-list">
                                <h3>Joined: <?= time_elapsed_string($account['registered']) ?></h3>
                                <div class="clear"></div>
                            </div>
                            <div class="profile-list">
                                <h3>Last Seen: <?= time_elapsed_string($account['last_seen']) ?></h3>
                                <div class="clear"></div>
                            </div>
                            <div class="profile-list">
                                <h3>Forum Posts: (<a class="link" href="/user/<?= $account['username'] ?>/posts/"><?= number_format_short($posts) ?></a>)</h3>
                                <div class="clear"></div>
                            </div>
                            <div class="profile-list last">
                                <h3>Favorite Galleries (<a class="link" href="/user/<?= $account['username'] ?>/favorites/"><?= number_format_short($favorites) ?></a>)</h3>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <? } else { ?>
                <div class="user_info">
                    <div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?= $_GET['user'] ?> is not a user here!</div>
                </div>
            <? } ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
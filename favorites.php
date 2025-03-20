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
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', (int) $account['id'], PDO::PARAM_INT);
    $stmt->execute();
    $favorite_count = $stmt->fetchColumn();

    // Number of results to show on each page.
    $per_page = manga_per_page;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
    $calc_page = ($page - 1) * $per_page;

    $stmt = $pdo->prepare('SELECT * FROM favorites LEFT JOIN accounts ON accounts.id = favorites.user_id JOIN mangas ON mangas.id = favorites.manga_id WHERE favorites.user_id = :user_id LIMIT :limit, :offset');
    $stmt->bindValue(':user_id', (int) $account['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $favorites = $stmt->fetchAll();

    // Get total records
    $stmt = $pdo->prepare('SELECT count(id) AS id FROM favorites WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', (int) $account['id'], PDO::PARAM_INT);
    $stmt->execute();
    $sql = $stmt->fetchAll();
    $total_pages = $sql[0]['id'];

    // Prev + Next
    $prev = $page - 1;
    $next = $page + 1;
}

if ($account) {
    template_header('' . $account['username'] . '\'s favorites');
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
                        <li <? if ($_GET['user'] == $_SESSION['name']) : ?> class="active" <? endif; ?>><a href="/user/<?= $_SESSION['name'] ?>/favorites/"><i class="fa fa-heart"></i>Favorites</a></li>
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
            <?php if ($account) { ?>
                <div class="profile_edit">
                    <h3><?= $_GET['user'] ?>'s favorites (<span class="total_favs"><?= number_format_short($favorite_count) ?></span>)</h3>
                    <?php if ($favorite_count == !0) { ?>
                        <div class="favorites">
                            <div id="msg_favs"></div>
                            <div id="favorites_list">
                                <div class="favs_list">
                                    <?php foreach ($favorites as $favorite) : ?>
                                        <div class="preview_item">
                                            <?php if (isset($_SESSION['loggedin']) && $account['id'] == $_SESSION['id']) : ?>
                                                <button class="remove_fav" id="<?= $favorite['manga_id'] ?>">Remove</button>
                                            <? endif; ?>
                                            <div class="image">
                                                <a href="/g/<?= $favorite['manga_id'] ?>/">
                                                    <img class="cover_image" src="<?= $favorite['cover_image'] ?>" alt="<?= $favorite['title'] ?>">
                                                    <h2 class="title"><?= $favorite['title'] ?></h2>
                                                </a>
                                            </div>
                                        </div>
                                    <? endforeach; ?>
                                </div>
                            </div>
                        </div>
                </div>
                <? pagination($total_pages, $per_page, $page, $prev, $next, '/user/' . $_GET['user'] . '/favorites/'); ?>
            <?php } else { ?>
                <? if ($_GET['user'] == $_SESSION['display']) { ?>
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You don't have any favorited galleries. You can add one by clicking "Favorite" button found on each gallery page.</div>
                <?php } else { ?>
                    <div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?= $_GET['user'] ?> has no favorites yet!</div>
                <?php } ?>
            <?php } ?>
        <?php } else { ?>
            <div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?= $_GET['user'] ?> is not a user here!</div>
        <?php } ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
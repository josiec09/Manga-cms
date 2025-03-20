<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Number of results to show on each page.
$per_page = $settings['manga_per_page'];
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
$calc_page = ($page - 1) * $per_page;

$stmt = $pdo->prepare('SELECT * FROM mangas ORDER BY submit_date DESC LIMIT :limit, :offset');
$stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
$stmt->execute();
$mangas = $stmt->fetchAll();

// Get total records
$sql = $pdo->query('SELECT count(id) AS id FROM mangas')->fetchAll();
$total_pages  = $sql[0]['id'];

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

template_header('Home');
?>
<div id="header">
    <div class="wrap_second">
        <div class="logo"></div>
        <div class="nav">
            <ul>
                <?= random_manga($pdo); ?>
                <li class="active"><a href="/">Home</a></li>
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
                        <li>
                            <div class="notification-container">
                                <a href="#" id="notification-bell">
                                    <i class="fa fa-bell" aria-hidden="true"></i>
                                    <span id="notification-count">3</span> <!-- Add this line for the notification count -->
                                </a>
                                <div id="notification-box" style="display: none;">
                                    <ul id="notification-list">
                                        <li>No new notifications</li>
                                        <li>No new notifications</li>
                                        <li>No new notifications</li>
                                        <li>No new notifications</li>
                                        <!-- Add more notifications here -->
                                    </ul>
                                    <button id="mark-all-read" class="mark-all-read">Mark All as Read</button> <!-- Add this line for the button -->
                                </div>
                            </div>
                        </li>
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
            <div class="ov_item">
                <?php foreach ($mangas as $manga) : ?>
                    <div class="preview_item">
                        <div class="image">
                            <a href="/g/<?= $manga['id'] ?>/">
                                <img class="cover_image" src="<?= $manga['cover_image'] ?>" alt="<?= $manga['title'] ?>" />
                                <h2 class="title"><?= $manga['title'] ?></h2>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <? pagination($total_pages, $per_page, $page, $prev, $next, '/'); ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
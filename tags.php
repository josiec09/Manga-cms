<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Show all the managa with the tag
if (isset($_GET["tag"])) {
    // Number of results to show on each page.
    $per_page = manga_per_page;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
    $calc_page = ($page - 1) * $per_page;
    $tag = $_GET['tag'];

    $stmt = $pdo->prepare('SELECT mangas.id, mangas.title, mangas.cover_image FROM mangas LEFT JOIN manga_tags ON manga_tags.manga_id = mangas.id JOIN tags ON tags.id = manga_tags.tag_id WHERE tags.tag = :tag LIMIT :limit, :offset');
    $stmt->bindParam(':tag', $tag);
    $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $mangas = $stmt->fetchAll();

    // Get total records
    $stmt = $pdo->prepare('SELECT count(mangas.id) AS id, mangas.title, mangas.cover_image FROM mangas LEFT JOIN manga_tags ON manga_tags.manga_id = mangas.id JOIN tags ON tags.id = manga_tags.tag_id WHERE tags.tag = :tag');
    $stmt->bindParam(':tag', $tag);
    $stmt->execute();
    $sql = $stmt->fetchAll();
    $total_pages  = $sql[0]['id'];

    //Get tag count
    $stmt = $pdo->prepare('SELECT count FROM tags WHERE tag = :tag');
    $stmt->bindParam(':tag', $tag);
    $stmt->execute();
    $tagcount= $stmt->fetchColumn();
} else {
    // Number of results to show on each page.
    $msg = '';
    $char = '';
    $tags_per_page = 120;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
    $calc_page = ($page - 1) * $tags_per_page;
    if (isset($_GET["char"])) {

        $char = preg_replace('#[^a-z]#i', '', $_GET['char']) . '%';

        $stmt = $pdo->prepare('SELECT * FROM tags WHERE tag LIKE :charrr ORDER BY count DESC LIMIT :limit, :offset');
        $stmt->bindParam(':charrr', $char);
        $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $tags_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $tags = $stmt->fetchAll();


        // Get total records
        $stmt = $pdo->prepare('SELECT count(id) AS id FROM tags WHERE tag LIKE :charrr');
        $stmt->bindParam(':charrr', $char);
        $stmt->execute();
        $sql = $stmt->fetchAll();
        $total_pages = $sql[0]['id'];

        if ($total_pages > 0) {
        } else {
            $msg = '<div style="margin: auto; width:70%;" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> The letter ' . $_GET['char'] . ' has no tags!</div>';
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM tags ORDER BY count DESC LIMIT :limit, :offset');
        $stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $tags_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $tags = $stmt->fetchAll();

        // Get total records
        $sql = $pdo->query("SELECT count(id) AS id FROM tags")->fetchAll();
        $total_pages  = $sql[0]['id'];
    }
}

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

template_header('Tags');
?>
<div id="header">
    <div class="wrap_second">
        <div class="logo"></div>
        <div class="nav">
            <ul>
                <?= random_manga($pdo); ?>
                <li><a href="/">Home</a></li>
                <li><a href="/forums/">Forums</a></li>
                <li class="active"><a href="/tags/">Tags</a></li>
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
            <?php if (!isset($_GET["tag"])) { ?>
                <h1 class="tag_info">Tags</h1>
                <div class="tags_page">
                    <div class="alphabet">
                        <ul>
                            <?php if (isset($_GET["char"])) { ?>
                                <li><a class="badge letter" href="/tags/">All</a></li>
                            <? } else { ?>
                                <li><a class="badge letter active" href="/tags/">All</a></li>
                            <? } ?>
                            <?php
                            foreach (range('A', 'Z') as $letter) {
                                $classInfo = isset($_GET["char"]) && $_GET["char"] == $letter
                                    ? ' active'
                                    : '';
                                echo '<li><a class="badge letter' . $classInfo . '" href="/tags/' . $letter . '/">' . $letter . '</a></li>';
                            }
                            ?>
                            <div class="clear"></div>
                        </ul>
                    </div>
                    <ul class="tags">
                        <?php foreach ($tags as $tag) : ?>
                            <li><a class="badge tag" href="/tag/<?= $tag['tag'] ?>/"><?= str_replace("-", " ", $tag['tag']) ?> <span class="galleries_count">(<?= $tag['count'] ?>)</span></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="clear"></div>
                    <?php if (ceil($total_pages / $tags_per_page) > 0) : ?>
                        <ul class='pagination'>
                            <?php if ($page > 1) { ?>
                                <li class='page-item'><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $prev ?>/">Previous</i></a></li>
                            <? } else { ?>
                                <li class='page-item disabled'><a class="page-link" href="#">Previous</i></a></li>
                            <? } ?>

                            <?php if ($page > 3) : ?>
                                <li class="page-item'"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?>1/">1</a></li>
                                <li class="page-item dots"><a class="page-link">...</a></li>
                            <?php endif; ?>

                            <?php if ($page - 2 > 0) : ?><li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $page - 2 ?>/"><?php echo $page - 2 ?></a></li><?php endif; ?>
                            <?php if ($page - 1 > 0) : ?><li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $page - 1 ?>/"><?php echo $page - 1 ?></a></li><?php endif; ?>

                            <li class="page-item active"><a class="page-link" href="#"><?php echo $page ?></a></li>

                            <?php if ($page + 1 < ceil($total_pages / $tags_per_page) + 1) : ?><li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $page + 1 ?>/"><?php echo $page + 1 ?></a></li><?php endif; ?>
                            <?php if ($page + 2 < ceil($total_pages / $tags_per_page) + 1) : ?><li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $page + 2 ?>/"><?php echo $page + 2 ?></a></li><?php endif; ?>

                            <?php if ($page < ceil($total_pages / $tags_per_page) - 2) : ?>
                                <li class="page-item dots"><a class="page-link">...</a></li>
                                <li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo ceil($total_pages / $tags_per_page) ?>/"><?php echo ceil($total_pages / $tags_per_page) ?></a></li>
                            <?php endif; ?>

                            <?php if ($page < ceil($total_pages / $tags_per_page)) { ?>
                                <li class="page-item"><a class="page-link" href="/tags/<?php if (isset($_GET["char"])) { echo $_GET['char'] . '/'; } ?><?php echo $next ?>/">Next</a></li>
                            <? } else { ?>
                                <li class='page-item disabled'><a class="page-link" href="#">Next</a></li>
                            <? } ?>
                        </ul>
                    <?php endif; ?>
                    <?= $msg ?>
                </div>
            <? } else { ?>
                <div class='results_info'>
                    <h1 class='detail'>Tag: <b><?= ucfirst(str_replace("-", " ", $tag)) ?></b> - (<?= $tagcount ?>) results</h1>
                </div>
                <div class='tag_gl'>
                    <?php foreach ($mangas as $manga) : ?>
                        <div class="preview_item">
                            <div class="image">
                                <a href="/g/<?= $manga['id'] ?>/">
                                    <img class="lazy" src="<?= $manga['cover_image'] ?>" alt="<?= $manga['title'] ?>" />
                                    <h2 class="title"><?= $manga['title'] ?></h2>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <? pagination($total_pages, $per_page, $page, $prev, $next, '/tag/' . $_GET['tag'] . '/'); ?>
            <? } ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
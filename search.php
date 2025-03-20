<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

$search_term = addslashes(htmlspecialchars($_GET['q']));
$per_page = manga_per_page;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
list($results, $total_pages) = searchManga($pdo, $search_term, $per_page, $page);

$total_records = count($results);

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

template_header('Search - ' . $search_term . '');
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
            <?php if ($_GET["q"] == '') { ?>
                <div class="results_info">
                    <h1 class="detail">No search term provided</h1>
                </div>
            <? } else { ?>
                <? if (count($results) == 0) { ?>
                    <div class="results_info">
                        <h1 class="detail">No results found for your search.</h1>
                    </div>
                <? } else { ?>
                    <div class="results_info">
                        <h1 class="detail">You are searching for <?= $search_term ?> - <?= $total_records ?> galleries</h1>
                    </div>
                    <div class="ov_item">
                        <? foreach ($results as $manga) { ?>
                            <div class="preview_item">
                                <div class="image">
                                    <a href="/g/<?= $manga['id'] ?>/">
                                        <img src="<?= $manga['cover_image'] ?>" alt="<?= $manga['title'] ?>">
                                        <h2 class="title"><?= $manga['title'] ?></h2>
                                    </a>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                    <?php if (ceil($total_pages / $per_page) > 0) : ?>
                        <!--Pagination-->
                        <ul class="pagination">
                            <?php if ($page > 1) { ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $prev ?>">Previous</i></a></li>
                            <?php } else { ?>
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</i></a></li>
                            <?php } ?>

                            <?php if ($page > 3) : ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=1">1</a></li>
                                <li class="page-item dots"><a class="page-link">...</a></li>
                            <?php endif; ?>

                            <?php if ($page - 2 > 0) : ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $page - 2 ?>"><?php echo $page - 2 ?></a></li>
                            <?php endif; ?>
                            <?php if ($page - 1 > 0) : ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $page - 1 ?>"><?php echo $page - 1 ?></a></li>
                            <?php endif; ?>

                            <li class="page-item active"><a class="page-link" href="#"><?= $page ?></a></li>

                            <?php if ($page + 1 < ceil($total_pages / $per_page) + 1) : ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                            <?php endif; ?>
                            <?php if ($page + 2 < ceil($total_pages / $per_page) + 1) : ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                            <?php endif; ?>

                            <?php if ($page < ceil($total_pages / $per_page) - 2) : ?>
                                <li class="page-item dots"><a class="page-link">...</a></li>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= ceil($total_pages / $per_page) ?>"><?= ceil($total_pages / $per_page) ?></a></li>
                            <?php endif; ?>

                            <?php if ($page < ceil($total_pages / $per_page)) { ?>
                                <li class="page-item"><a class="page-link" href="/search/?q=<?= $_GET["q"] ?>&page=<?= $next ?>">Next</a></li>
                            <?php } else { ?>
                                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                            <?php } ?>
                            <div class="ios-mobile-webkit-bottom-spacing">
                                &nbsp;
                                &nbsp;
                            </div>
                            </section>
                            <!--Pagination End-->
                        <?php endif; ?>
                    <? } ?>
                <? } ?>
        </div>
    </div>
</div>
<?= template_footer() ?>
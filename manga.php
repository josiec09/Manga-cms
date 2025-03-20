<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/manga.inc.php';
include 'includes/template.php';

// Fetch the manga and display it.
$stmt = $pdo->prepare('SELECT * FROM mangas WHERE id = :id');
$stmt->bindValue(':id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the manga and display it.
$stmt = $pdo->prepare('SELECT mangas.id, mangas.title, mangas.readcount FROM mangas WHERE `collection` = :collections');
$stmt->bindValue(':collections', (int) $manga['collection'], PDO::PARAM_INT);
$stmt->execute();
$collections = $stmt->fetchAll();

// Fetch comments for pagination.
$per_page = 5;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
$calc_page = ($page - 1) * $per_page;

//$stmt = $pdo->prepare('SELECT comments.*, accounts.avatar, accounts.username, accounts.role_id FROM comments JOIN accounts ON comments.user_id = accounts.id WHERE comments.parent_id = 0 AND comments.manga_id = :manga_id ORDER BY comments.date_added DESC LIMIT :limit, :offset');
$stmt = $pdo->prepare('SELECT comments.*, accounts.avatar, accounts.username, accounts.role_id, roles.role FROM comments JOIN accounts ON comments.user_id = accounts.id JOIN roles ON accounts.role_id = roles.id WHERE comments.parent_id = 0 AND comments.manga_id = :manga_id ORDER BY comments.date_added DESC LIMIT :limit, :offset');
$stmt->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll();

// Get total records
$stmt = $pdo->prepare('SELECT count(id) AS id FROM comments WHERE parent_id = 0 AND manga_id = :manga_id');
$stmt->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$sql = $stmt->fetchAll();
$total_pages = $sql[0]['id'];

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

// Count comments
$stmt = $pdo->prepare('SELECT COUNT(*) AS total_comments FROM comments WHERE manga_id = :manga_id');
$stmt->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$comments_info = $stmt->fetch(PDO::FETCH_ASSOC);

template_header('' . $manga['title'] . '');

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
            <div class="book_page">
                <div class="left">
                    <div class="cover">
                        <img src="<?= $manga['cover_image'] ?>" data-src="<?= $manga['cover_image'] ?>" alt="<?= $manga['title'] ?>" />
                    </div>
                </div>
                <div class="right">
                    <div class="info">
                        <h1><?= $manga['title'] ?></h1>
                        <ul>
                            <div class="tags">
                                <h3>Tags:</h3>
                                <div class="tag_list">
                                    <?= ShowTags($pdo, $manga['id']) ?>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </ul>
                        <ul>
                            <div class="tags">
                                <h3>Artists:</h3>
                                <div class="tag_list">
                                    <?= ShowArtists($pdo, $manga['id']) ?>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </ul>
                        <ul>
                            <div class="tags">
                                <h3>Characters:</h3>
                                <div class="tag_list">
                                    <?= ShowCharacters($pdo, $manga['id']) ?>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </ul>
                        <ul>
                            <div class="tags">
                                <h3>Languages:</h3>
                                <div class="tag_list">
                                    <?= ShowLanguages($pdo, $manga['id']) ?>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </ul>
                        <ul>
                            <div class="tags">
                                <h3>Category:</h3>
                                <div class="tag_list">
                                    <?= ShowCategories($pdo, $manga['id']) ?>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </ul>
                        <div class="pages">
                            <h3>Pages: <?= CountPages($pdo, $manga['id']) ?></h3>
                            <div class="clear"></div>
                        </div>
                        <div class="pages">
                            <h3>Read: <?= number_format_short($manga['readcount']) ?></h3>
                            <div class="clear"></div>
                        </div>
                        <div class="pages">
                            <h3>Uploaded: <?= time_elapsed_string($manga['submit_date']) ?></h3>
                            <div class="clear"></div>
                        </div>
                        <div class="pages last">
                            <?php if (isset($_SESSION['loggedin'])) { ?>
                                <?php
                                // Check if the user has already added this gallery to their favorites
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND manga_id = :gallery_id");
                                $stmt->bindValue(':user_id', (int) $_SESSION['id'], PDO::PARAM_INT);
                                $stmt->bindValue(':gallery_id', (int) $manga['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                $count = $stmt->fetchColumn();

                                if ($count > 0) { ?>
                                    <button class="add_fav" id="remove_fav"><i id="spinner_fv2" class="fa fa-circle-o-notch fa-spin" style="display:none;margin-right:5px;"></i><i class="fa fa-heart-o"></i> Unfavorite (<span class="count"><?= number_format_short($manga['favorites']) ?></span>)</button>
                                <? } else { ?>
                                    <button class="add_fav" id="add_fav"><i id="spinner_fv1" class="fa fa-circle-o-notch fa-spin" style="display:none;margin-right:5px;"></i><i class="fa fa-heart"></i> Favorite (<?= number_format_short($manga['favorites']) ?>)</button>
                                <? } ?>
                            <? } ?>
                            <input type="hidden" name="gallery_id" id="gallery_id" value="<?= $manga['id'] ?>" />
                            <div id="msg_gallery"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <? if ($manga['collection'] == 0) { ?>

                <? } else { ?>
                    <table class="collections">
                        <thead class="collectionHeader">
                            <th class="header-id">#</th>
                            <th class="header-name">Name</th>
                            <th class="header-read">Read</th>
                            <th class="header-pages">Pages</th>
                        </thead>
                        <tbody>
                            <?
                            $i = 0;
                            foreach ($collections as $collection) { ?>
                                <tr class="collection<? if ($collection['id'] == $_GET['id']) {
                                                            echo '-active';
                                                        } ?>">
                                    <td class="collection-item-id"><?= $i += 1 ?></td>
                                    <td class="collection-item-name"><a href="/g/<?= $collection['id'] ?>/"><?= $collection['title'] ?></a></td>
                                    <td class="collection-item-read"><?= number_format_short($collection['readcount']) ?></td>
                                    <td class="collection-item-pages"><?= CountPages($pdo, $collection['id']) ?></td>
                                </tr>
                            <? } ?>
                        </tbody>
                    </table>
                <? } ?>
                <div class="gallery">
                    <div class="append_thumbs" id="append_thumbs">
                        <?= ShowPages($pdo, $manga['id']) ?>
                    </div>
                </div>
                <div class="related">
                    <h3>Comments (<?= number_format_short($comments_info['total_comments']) ?>)</h3>
                </div>
                <?php if (!isset($_SESSION['loggedin'])) { ?>
                    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <a href="/login/">Login</a> or <a href="/register/">Register</a> to post a comment.</div>
                <? } else { ?>
                    <div class="write_comment">
                        <form method="POST" id="comment_form">
                            <input type="hidden" name="parent_id" id="parent_id" value="0" />
                            <input type="hidden" name="manga_id" id="manga_id" value="<?= $manga['id'] ?>" />
                            <textarea class="comment-input" maxlength="1000" minlength="10" name="comment" id="comment" placeholder="Type your comment here..."></textarea>
                            <button class="btn btn-comment" type="submit">Post Comment</button> <button type="button" style="display: none;" class="btn btn-comment" id="cancel-reply">Cancel Reply</button>
                        </form>
                    </div>
                <? } ?>
                <div class="clear"></div>
                <div id="msg_comments"></div>
                <div id="comments">
                    <?php if ($comments_info['total_comments'] == '0') : ?>
                        <div style="margin: 0px;" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No one has posted anything yet. Got something to say?</div>
                    <? endif; ?>
                    <?php foreach ($comments as $comment) {
                        echo '<div class="comment" id="comment-' . $comment['id'] . '">';
                        echo '<a class="avatar" href="/user/' . $comment['username'] . '/"><img src="/uploads/' . $comment['avatar'] . '"></a>';
                        echo '<div class="body-wrapper">';
                        echo '<div class="header">';
                        echo '<div class="left"><b><a href="/user/' . $comment['username'] . '/">' . $comment['username'] . '</a></b> (' . $comment['role'] . ') <time datetime="' . $comment['date_added'] . '">' . time_elapsed_string($comment['date_added']) . '</time></div>';
                        echo '<div class="right"><i class="fa fa-flag"></i></div>';
                        echo '</div>';
                        echo '<div class="body">' . bbcode(nl2br($comment['comment'])) . '</div>';
                        if (isset($_SESSION['loggedin'])) {
                            echo '<button type="button" class="reply" id="' . $comment["id"] . '">Reply</button>
                        &nbsp;&nbsp;&nbsp;<button type="button" class="upvote" id="' . $comment["id"] . '"><i class="fa fa-thumbs-up" aria-hidden="true"></i> (<span class="upvote-count">' . $comment["upvote"] . '</span>)</button>
        &nbsp;&nbsp;<button type="button" class="downvote" id="' . $comment["id"] . '"><i class="fa fa-thumbs-down" aria-hidden="true"></i> (<span class="downvote-count">' . $comment["downvote"] . '</span>)</button>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="replies">';
                        displayReplies($comment['id'], $pdo);
                        echo ' </div>';
                    }
                    ?>
                </div>
                <? paginationManga($total_pages, $per_page, $page, $prev, $next, '/g/' . $_GET['id'] . '/p/', '#comments'); ?>
            </div>
        </div>
    </div>
</div>
<?= template_footer() ?>
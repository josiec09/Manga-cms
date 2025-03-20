<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/forums.inc.php';
include 'includes/template.php';
// Pull the topic info and display it
$stmt = $pdo->prepare('SELECT posts.*, accounts.username,avatar,role_id, forums.forum, roles.role
FROM posts 
JOIN accounts ON posts.user_id = accounts.id 
JOIN forums ON posts.forum_id = forums.id 
JOIN roles ON accounts.role_id = roles.id 
WHERE posts.id = :id AND posts.parent_id = 0');
$stmt->bindValue(':id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Pull the replies for the topic and use the pagination system
$per_page = 15;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
$calc_page = ($page - 1) * $per_page;

$stmt = $pdo->prepare('SELECT posts.*, accounts.username,avatar,role_id, forums.forum, roles.role
FROM posts 
JOIN accounts ON posts.user_id = accounts.id 
JOIN forums ON posts.forum_id = forums.id
JOIN roles ON accounts.role_id = roles.id 
WHERE posts.parent_id = :id ORDER BY posts.date LIMIT :limit, :offset');
$stmt->bindValue(':id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $per_page, PDO::PARAM_INT);
$stmt->execute();
$replies = $stmt->fetchAll();

// Get total records
$stmt = $pdo->prepare('SELECT count(id) AS id FROM posts WHERE parent_id = :id');
$stmt->bindValue(':id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$sql = $stmt->fetchAll();
$total_pages = $sql[0]['id'];

// Prev + Next
$prev = $page - 1;
$next = $page + 1;

// Update last seen date
if (isset($_SESSION['loggedin'])) {
    $date = date('Y-m-d\TH:i:s');
    $stmt = $pdo->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
    $stmt->execute([$date, $_SESSION['id']]);
}

template_header('' . $post['title'] . '');
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
            <div class="container">
                <? if ($post['pinned'] == 1) {
                    echo '<div class="alert alert-success"><i class="fa fa-thumb-tack" aria-hidden="true"></i> This topic has been pinned, so it\'s probably important!</div>';
                }
                if ($post['locked'] == 1) {
                    echo '<div class="alert alert-danger"><i class="fa fa-lock" aria-hidden="true"></i> This topic has been locked.</div>';
                }
                ?>
                <?php if (isset($_SESSION['loggedin'])) : ?>
                    <? if ($post['locked'] == 0) : ?>
                        <div class="replyBtn">
                            <a href="#" class="btn btn-forum" id="replybtn"><i class="fa fa-reply"></i> Reply</a>
                        </div>
                    <? endif; ?>
                <? endif; ?>
                <table class="forums">
                    <thead>
                        <tr class="category" id="cat-1">
                            <th class="category_name"><i class="fa fa-book color-icon"></i>&nbsp;&nbsp;<a href="/forums/">Forums</a> &raquo; <a href="/forum/<?= $post['forum_id'] ?>/"><?= $post['forum'] ?></a> &raquo; Topic Details</th>
                        </tr>
                    <tbody>
                        <tr>
                            <td class="post" id="post-<?= $post['id'] ?>">
                                <div class="postbody">
                                    <a class="avatar" href="/user/<?= $post['username']; ?>/"><img src="/uploads/<?= $post['avatar'] ?>"></a>
                                    <div class="body-wrapper">
                                        <div class="header">
                                            <div class="left"><b><a href="#" class="drop_user" data-id="<?= $post['id'] ?>"><span class="display_name"><?= $post['username'] ?></span> <i class="fa fa-caret-down" aria-hidden="true"></i></a></b> <time datetime="<?= $post['date'] ?>"><?= time_elapsed_string($post['date']) ?></time>
                                                <div class="user_menu" data-id="<?= $post['id']; ?>" style="display: none;">
                                                    <ul>
                                                        <li><a href="/user/<?= $post['username'] ?>/">View Profile</a></li>
                                                        <li><a href="/user/<?= $post['username'] ?>/posts/">View Posts</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="right"><? if ($post['locked'] == 0 && isset($_SESSION['loggedin'])) { ?><a title="Quote this post in your reply" class="quote" data-post-id="<?= $post['id']; ?>" href="#"><i class="fa fa-reply" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<? } ?><a title="Report this post" href="#" data-bs-toggle="modal" data-bs-target="#flag-<?= $post['id']; ?>"><i class="fa fa-flag" aria-hidden="true"></i></a>
                                                    <? if ($post['locked'] == 0 && isset($_SESSION['loggedin']) && $_SESSION['id'] == $post['user_id']) : ?>&nbsp;&nbsp;&nbsp;&nbsp;<a title="Edit this post" href="#" data-bs-toggle="modal" data-bs-target="#postModal"><i class="fa fa-edit" aria-hidden="true"></i></a><? endif; ?>&nbsp;&nbsp;&nbsp;&nbsp;<a title="Delete this post" href="#"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                            </div>

                                        </div>
                                        <div class="title"><?= $post['title'] ?></div>
                                        <div class="body"><?= bbcode(nl2br($post['post'])) ?></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="forums" id="replies">
                    <tbody>
                        <?php foreach ($replies as $reply) : ?>
                            <tr>
                                <td class="post" id="post-<?= $reply['id'] ?>">
                                    <div class="postbody">
                                        <a class="avatar" href="/user/<?= $reply['username']; ?>/"><img src="/uploads/<?= $reply['avatar']; ?>"></a>
                                        <div class="body-wrapper">
                                            <div class="header">
                                                <div class="left"><b><a href="#" class="drop_user" data-id="<?= $reply['id'] ?>"><span class="display_name"><?= $reply['username'] ?></span> <i class="fa fa-caret-down" aria-hidden="true"></i></a></b> <time datetime="<?= $reply['date'] ?>"><?= time_elapsed_string($reply['date']) ?></time>
                                                    <div class="user_menu" data-id="<?= $reply['id']; ?>" style="display: none;">
                                                        <ul>
                                                            <li><a href="/user/<?= $reply['username'] ?>/">View Profile</a></li>
                                                            <li><a href="/user/<?= $reply['username'] ?>/posts/">View Posts</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="right"><? if ($post['locked'] == 0 && isset($_SESSION['loggedin'])) { ?><a title="Quote this post in your reply" class="quote" data-post-id="<?= $reply['id'] ?>" href="#"><i class="fa fa-reply" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<? } ?><a title="Report this post" href="#" data-bs-toggle="modal" data-bs-target="#flag-<?= $reply['id']; ?>"><i class="fa fa-flag" aria-hidden="true"></i></a>
                                                        <? if ($post['locked'] == 0 && isset($_SESSION['loggedin']) && $_SESSION['id'] == $reply['user_id']) : ?>&nbsp;&nbsp;&nbsp;&nbsp;<a title="Edit this post" href="#" data-bs-toggle="modal" data-bs-target="#reply-<?= $reply['id']; ?>"><i class="fa fa-edit" aria-hidden="true"></i></a><? endif; ?>&nbsp;&nbsp;&nbsp;&nbsp;<a title="Delete this post" href="#"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="body"><?= bbcode(nl2br($reply['post'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Flag Modal for reply -->
                            <div class="modal fade" id="flag-<?= $reply['id']; ?>" role="dialog">
                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header d-block" style="padding:15px;">
                                            <h4 class="modal-title"><i class="fa fa-flag" aria-hidden="true"></i> Flag Post</h4>
                                        </div>
                                        <div class="modal-body" style="padding:40px 50px;">
                                            <form role="form" method="post">
                                                <textarea class="edit-input" maxlength="1000" minlength="10" name="edit-reply" id="edit-reply" placeholder="Enter more info"></textarea>
                                                <button type="submit" class="btn btn-edit-reply">Flag it</button>&nbsp;&nbsp;<button type="button" class="btn btn-edit-cancel" data-bs-dismiss="modal">Cancel</button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- Flag Modal for starter -->
                            <div class="modal fade" id="flag-<?= $post['id']; ?>" role="dialog">
                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header d-block" style="padding:15px;">
                                            <h4 class="modal-title"><i class="fa fa-flag" aria-hidden="true"></i> Flag Post</h4>
                                        </div>
                                        <div class="modal-body" style="padding:40px 50px;">
                                            <form role="form" method="post">
                                                <textarea class="edit-input" maxlength="1000" minlength="10" name="edit-reply" id="edit-reply" placeholder="Enter more info"></textarea>
                                                <button type="submit" class="btn btn-edit-reply">Flag it</button>&nbsp;&nbsp;<button type="button" class="btn btn-edit-cancel" data-bs-dismiss="modal">Cancel</button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <? if ($post['locked'] == 0 && isset($_SESSION['loggedin']) && $_SESSION['id'] == $post['user_id']) : ?>
                                <!-- Edit starter Modal -->
                                <div class="modal fade" id="postModal" role="dialog">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header d-block" style="padding:15px;">
                                                <h4 class="modal-title"><i class="fa fa-edit" aria-hidden="true"></i> Edit Post</h4>
                                            </div>
                                            <div class="modal-body" style="padding:40px 50px;">
                                                <form role="form" method="post">
                                                    <input name="title" id="title" type="text" value="<?= $post['title'] ?>">
                                                    <textarea class="edit-input" maxlength="1000" minlength="10" name="edit" id="edit"><?= $post['post'] ?></textarea>
                                                    <button type="submit" class="btn btn-edit-post">Edit Post</button>&nbsp;&nbsp;<button type="button" class="btn btn-edit-cancel" data-bs-dismiss="modal">Cancel</button>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <? endif; ?>
                            <? if ($post['locked'] == 0 && isset($_SESSION['loggedin']) && $_SESSION['id'] == $reply['user_id']) : ?>
                                <!-- Edit reply Modal -->
                                <div class="modal fade" id="reply-<?= $reply['id']; ?>" role="dialog">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header d-block" style="padding:15px;">
                                                <h4 class="modal-title"><i class="fa fa-edit" aria-hidden="true"></i> Edit Post</h4>
                                            </div>
                                            <div class="modal-body" style="padding:40px 50px;">
                                                <form role="form" method="post">
                                                    <input name="title" id="title" type="hidden" value="RE: <?= $post['title'] ?>">
                                                    <textarea class="edit-input" maxlength="1000" minlength="10" name="edit" id="edit"><?= $reply['post'] ?></textarea>
                                                    <button type="submit" class="btn btn-edit-post">Edit Post</button>&nbsp;&nbsp;<button type="button" class="btn btn-edit-cancel" data-bs-dismiss="modal">Cancel</button>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <? endif; ?>
                        <? endforeach; ?>
                    </tbody>
                </table>
                <? pagination($total_pages, $per_page, $page, $prev, $next, '/topic/' . $_GET['id'] . '/'); ?>
                <div class="clear"></div>
                <? if ($post['locked'] == 0) { ?>
                    <?php if (!isset($_SESSION['loggedin'])) { ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <a href="/login/">Login</a> or <a href="/register/">Register</a> to reply.</div>
                    <? } else { ?>
                        <div id="msg_replies"></div>
                        <div class="write_reply">
                            <form method="POST" id="reply_form">
                                <input type="hidden" name="parent_id" id="parent_id" value="<?= $_GET['id'] ?>" />
                                <input type="hidden" name="forum_id" id="forum_id" value="<?= $post['forum_id'] ?>" />
                                <input type="hidden" name="title" id="title" value="RE: <?= $post['title'] ?>" />
                                <textarea class="reply-input" maxlength="1000" minlength="10" name="reply" id="reply" placeholder="Type your reply here..."></textarea>
                                <button class="btn btn-reply" type="submit">Post Reply</button>
                            </form>
                        </div>
                    <? } ?>
                <? } ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
<?= template_footer() ?>
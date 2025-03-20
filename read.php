<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Will break if set to anything other then 1!
$num_results_on_page = 1;

$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
$calc_page = ($page - 1) * $num_results_on_page;

$stmt = $pdo->prepare('SELECT * FROM manga_pages WHERE manga_id = :manga_id LIMIT :limit, :offset');
$stmt->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', (int) $calc_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $num_results_on_page, PDO::PARAM_INT);
$stmt->execute();
$mangas = $stmt->fetchAll();


// Get total records
$stmt = $pdo->prepare('SELECT count(id) AS id FROM manga_pages WHERE manga_id = :manga_id');
$stmt->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$sql = $stmt->fetchAll();
$total_pages = $sql[0]['id'];


// Prev + Next
$prev = $page - 1;
$next = $page + 1;

// Update read count
if ($_GET['page'] == $total_pages) {
    $stmt = $pdo->prepare('UPDATE mangas SET readcount = readcount + 1 WHERE id = :id');
    $stmt->bindValue(':id', (int) $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
}

template_header('Home');
?>
<?php if (ceil($total_pages / $num_results_on_page) > 0) : ?>
    <!--Pagination-->
    <section class="reader-bar">
        <div class="reader-buttons-left">
            <a class="go-back" href="/g/<?= $_GET['id'] ?>/"><i class="fa fa-reply"></i></a>
        </div>
        <div class="reader-pagination">
            <?php if ($page > 1) : ?>
                <a href="/g/<?= $_GET['id'] ?>/1/" class="first"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a>
                <a href="/g/<?= $_GET['id'] ?>/<?php echo $prev ?>/" class="previous"><i class="fa fa-chevron-left"></i></a>
            <?php endif; ?>

            <button class="page-number btn btn-unstyled" onclick="GoPage(<?= $_GET['id'] ?>,<?= $total_pages ?>)">
                <span class="current"><?= $page ?></span>
                <span class="divider">&nbsp;of&nbsp;</span>
                <span class="num-pages"><?= $total_pages ?></span>
            </button>

            <?php if ($page < ceil($total_pages / $num_results_on_page)) : ?>
                <a href="/g/<?= $_GET['id'] ?>/<?php echo $next ?>/" class="next"><i class="fa fa-chevron-right"></i></a>
                <a href="/g/<?= $_GET['id'] ?>/<?= $total_pages ?>/" class="last"><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </section>
    <!--Pagination End-->
<?php endif; ?>
<?php foreach ($mangas as $manga) : ?>
    <section id="image-container">
        <?php if ($page < ceil($total_pages / $num_results_on_page)) { ?>
            <a href="/g/<?= $_GET['id'] ?>/<?php echo $next ?>/">
                <img src="/<?= $manga['page_file'] ?>" width="850" height="1000" />
            </a>
        <? } else { ?>
            <a href="/g/<?= $_GET['id'] ?>/" title="Back to Manga">
                <img src="/<?= $manga['page_file'] ?>" width="850" height="1000" />
            </a>
        <? } ?>
    </section>
<?php endforeach; ?>
<?php if (ceil($total_pages / $num_results_on_page) > 0) : ?>
    <!--Pagination-->
    <section class="reader-bar">
        <div class="reader-buttons-left">
            <a class="go-back" href="/g/<?= $_GET['id'] ?>/"><i class="fa fa-reply"></i></a>
        </div>
        <div class="reader-pagination">
            <?php if ($page > 1) : ?>
                <a href="/g/<?= $_GET['id'] ?>/1/" class="first"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a>
                <a href="/g/<?= $_GET['id'] ?>/<?php echo $prev ?>/" class="previous"><i class="fa fa-chevron-left"></i></a>
            <?php endif; ?>

            <button class="page-number btn btn-unstyled" onclick="GoPage(<?= $_GET['id'] ?>,<?= $total_pages ?>)">
                <span class="current"><?= $page ?></span>
                <span class="divider">&nbsp;of&nbsp;</span>
                <span class="num-pages"><?= $total_pages ?></span>
            </button>

            <?php if ($page < ceil($total_pages / $num_results_on_page)) : ?>
                <a href="/g/<?= $_GET['id'] ?>/<?php echo $next ?>/" class="next"><i class="fa fa-chevron-right"></i></a>
                <a href="/g/<?= $_GET['id'] ?>/<?= $total_pages ?>/" class="last"><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </section>
    <!--Pagination End-->
<?php endif; ?>
<script>
    function GoPage(y, x) {
        var page = Number(prompt("Please enter a page between 1 and " + x));
        if (page < 1) {
            window.location.href = "https://halloweeb.town/g/" + y + "/1/";
        } else if (page > x) {
            window.location.href = "https://halloweeb.town/g/" + y + "/" + x + "/";
        } else if (page != null) {
            window.location.href = "https://halloweeb.town/g/" + y + "/" + page + "/";
        }
    }
</script>
</body>

</html>
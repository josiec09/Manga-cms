<?php

// Pull the tags
function ShowTags($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM tags LEFT JOIN manga_tags ON manga_tags.tag_id = tags.id JOIN mangas ON mangas.id = manga_tags.manga_id WHERE mangas.id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    while ($tag = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<a href="/tag/' . $tag['tag'] . '/"><span class="badge tag">' . ucfirst(str_replace("-", " ", $tag['tag'])) . ' <span class="gallery_count">(' . $tag['count'] . ')</span></span></a>';
    }
    return $output;
}
// Pull the artists
function ShowArtists($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM artists LEFT JOIN manga_artists ON manga_artists.artist_id = artists.id JOIN mangas ON mangas.id = manga_artists.manga_id WHERE mangas.id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    while ($artist = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<a href="/artist/' . $artist['artist'] . '/"><span class="badge tag">' . ucfirst(str_replace("-", " ", $artist['artist'])) . ' <span class="gallery_count">(' . $artist['count'] . ')</span></span></a>';
    }
    return $output;
}
// Pull the characters
function ShowCharacters($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM characters LEFT JOIN manga_characters ON manga_characters.character_id = characters.id JOIN mangas ON mangas.id = manga_characters.manga_id WHERE mangas.id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    while ($character = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<a href="/character/' . $character['character'] . '/"><span class="badge tag">' . ucfirst(str_replace("-", " ", $character['character'])) . ' <span class="gallery_count">(' . $character['count'] . ')</span></span></a>';
    }
    return $output;
}
// Pull the languages
function ShowLanguages($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM languages LEFT JOIN manga_languages ON manga_languages.language_id = languages.id JOIN mangas ON mangas.id = manga_languages.manga_id WHERE mangas.id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    while ($language = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<a href="/search/?q=:language ' . $language['language'] . '"><span class="badge tag">' . ucfirst(str_replace("-", " ", $language['language'])) . ' <span class="gallery_count">(' . $language['count'] . ')</span></span></a>';
    }
    return $output;
}
// Pull the categories
function ShowCategories($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM categories LEFT JOIN manga_categories ON manga_categories.category_id = categories.id JOIN mangas ON mangas.id = manga_categories.manga_id WHERE mangas.id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<a href="/search/?q=:category ' . $category['category'] . '"><span class="badge tag">' . ucfirst(str_replace("-", " ", $category['category'])) . ' <span class="gallery_count">(' . $category['count'] . ')</span></span></a>';
    }
    return $output;
}
// Total number of pages the manga has
function CountPages($pdo, $manga_id)
{
    $stmt = $pdo->prepare('SELECT COUNT(id) AS total_pages FROM manga_pages WHERE manga_id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_images = $images['total_pages'];

    return $total_images;
}
// Count the favorites
function CountFavorites($pdo, $manga_id)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total_favorites FROM favorites WHERE manga_id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    $favorites = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_favorites = $favorites['total_favorites'];

    return number_format_short($total_favorites);
}
// Pull the list of manga pages
function ShowPages($pdo, $manga_id)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT * FROM manga_pages WHERE manga_id = :manga_id');
    $stmt->bindValue(':manga_id', (int) $manga_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll();
    $total_images = $stmt->rowCount();
    if ($total_images === 0) {
        $output .= '<div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No pages found, check back later!</div>';
    }
    if ($total_images > 0) {
        $i = 1;
        foreach ($images as $image) {
            $output .= '<div class="preview_thumb">
            <a href="/g/' . $_GET['id'] . '/' . $i . '/"><img src="/' . $image['page_file'] . '" width="200" height="282" /></a>
        </div>';
            $i++;
        }
    }
    return $output;
}
// Display the list of replies for the comments
function displayReplies($parent_id, $pdo)
{
    //$replies = $pdo->prepare("SELECT * FROM comments WHERE parent_id = :parent_id AND manga_id = :manga_id ORDER BY date_added DESC");
    $replies = $pdo->prepare("SELECT comments.*, accounts.avatar, accounts.username, accounts.role_id, roles.role FROM comments JOIN accounts ON comments.user_id = accounts.id JOIN roles ON accounts.role_id = roles.id WHERE comments.parent_id = :parent_id AND comments.manga_id = :manga_id ORDER BY comments.date_added DESC");
    $replies->bindValue(':parent_id', (int) $parent_id, PDO::PARAM_INT);
    $replies->bindValue(':manga_id', (int) $_GET['id'], PDO::PARAM_INT);
    $replies->execute();
    foreach ($replies as $reply) {
        echo '<div class="comment" id="comment-' . $reply['id'] . '">';
        echo '<a class="avatar" href="/user/' . $reply['username'] . '/"><img src="/uploads/' . $reply['avatar'] . '"></a>';
        echo '<div class="body-wrapper">';
        echo '<div class="header">';
        echo '<div class="left"><b><a href="/user/' . $reply['username'] . '/">' . $reply['username'] . '</a></b> (' . $reply['role'] . ') <time datetime="' . $reply['date_added'] . '">' . time_elapsed_string($reply['date_added']) . '</time></div>';
        echo '<div class="right"><i class="fa fa-flag"></i></div>';
        echo '</div>';
        echo '<div class="body">' . bbcode(nl2br($reply['comment'])) . '</div>';
        if (isset($_SESSION['loggedin'])) {
            echo '<button type="button" class="reply" id="' . $reply["id"] . '">Reply</button>
        &nbsp;&nbsp;&nbsp;<button type="button" class="upvote" id="' . $reply["id"] . '"><i class="fa fa-thumbs-up" aria-hidden="true"></i> (<span class="upvote-count">' . $reply["upvote"] . '</span>)</button>
&nbsp;&nbsp;<button type="button" class="downvote" id="' . $reply["id"] . '"><i class="fa fa-thumbs-down" aria-hidden="true"></i> (<span class="downvote-count">' . $reply["downvote"] . '</span>)</button>';
        }
        echo '</div>';
        echo '</div>';
        echo '<div class="replies">';
        displayReplies($reply['id'], $pdo);
        echo ' </div>';
    }
}
// Pagination for manga comments
function paginationManga($total_pages, $per_page, $page, $prev, $next, $path ,$scrollto)
{
	if (ceil($total_pages / $per_page) > 0) {
		echo '<ul class="pagination">';
		if ($page > 1) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $prev . '/' . $scrollto . '">Previous</i></a></li>';
		} else {
			echo '<li class="page-item disabled"><a class="page-link" href="' . $scrollto . '">Previous</i></a></li>';
		}
		if ($page > 3) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '1/' . $scrollto . '">1</a></li>';
			echo '<li class="page-item dots"><a class="page-link">...</a></li>';
		}
		if ($page - 2 > 0) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page - 2 . '/' . $scrollto . '">' . $page - 2 . '</a></li>';
		}
		if ($page - 1 > 0) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page - 1 . '/' . $scrollto . '">' . $page - 1 . '</a></li>';
		}
		echo '<li class="page-item active"><a class="page-link" href="' . $scrollto . '">' . $page . '</a></li>';
		if ($page + 1 < ceil($total_pages / $per_page) + 1) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page + 1 . '/' . $scrollto . '">' . $page + 1 . '</a></li>';
		}
		if ($page + 2 < ceil($total_pages / $per_page) + 1) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page + 2 . '/' . $scrollto . '">' . $page + 2 . '</a></li>';
		}
		if ($page < ceil($total_pages / $per_page) - 2) {
			echo '<li class="page-item dots"><a class="page-link">...</a></li>';
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . ceil($total_pages / $per_page) . '/' . $scrollto . '">' . ceil($total_pages / $per_page) . '</a></li>';
		}
		if ($page < ceil($total_pages / $per_page)) {
			echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $next . '/' . $scrollto . '">Next</a></li>';
		} else {
			echo '<li class="page-item disabled"><a class="page-link" href="' . $scrollto . '">Next</a></li>';
		}
		echo '</ul>';
	}
}

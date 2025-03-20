<?php
include_once __DIR__ . '/connection.php';

// Shorten number function
function number_format_short($n, $precision = 1)
{
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } else if ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } else if ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } else if ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }

    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }

    return $n_format . $suffix;
}
// Convert date to elapsed string function
//function time_elapsed_string($datetime, $full = false)
//{
    //$now = new DateTime;
    //$ago = new DateTime($datetime);
    //$diff = $now->diff($ago);
    //$weeks = floor($diff->d / 7);
    //$diff->d -= $weeks * 7;
    //$string = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
    //foreach ($string as $k => &$v) {
        //if ($k === 'w' && $weeks) {
         //   $v = $weeks . ' ' . $v . ($weeks > 1 ? 's' : '');
      //  } elseif ($diff->$k) {
       //     $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
       // } else {
       //     unset($string[$k]);
      //  }
  //  }
   // if (!$full) $string = array_slice($string, 0, 1);
   // return $string ? implode(', ', $string) . ' ago' : 'just now';
//}
function time_elapsed_string($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $interval = $now->diff($past);

    if ($interval->y > 0) {
        return $interval->y . ' years ago';
    }
    if ($interval->m > 0) {
        return $interval->m . ' months ago';
    }
    if ($interval->d >= 7) {
        return floor($interval->d / 7) . ' weeks ago';
    }
    if ($interval->d > 0) {
        return $interval->d . ' days ago';
    }
    if ($interval->h > 0) {
        return $interval->h . ' hours ago';
    }
    if ($interval->i > 0) {
        return $interval->i . ' minutes ago';
    }
    return $interval->s . ' just now';
}
// Pull a random manga for the nav link
function random_manga($pdo)
{
    $output = '';
    $stmt = $pdo->prepare('SELECT id FROM mangas order by RAND() limit 1');
    $stmt->execute();
    $rand = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach ($rand as $random) {
        $output .= '<li><a href="/g/' . $random . '/">Random</a></li>';
    }
    return $output;
}
// Redirect function
function Redirect($time, $topage)
{
    echo "<meta http-equiv=\"refresh\" content=\"{$time}; url={$topage}\" /> ";
}
// Shorten text used on the forums
function shapeSpace_truncate_text($text, $max = 50, $append = '…')
{
    if (strlen($text) <= $max) return $text;
    $return = substr($text, 0, $max);
    if (strpos($text, ' ') === false) return $return . $append;
    return preg_replace('/\w+$/', '', $return) . $append;
}
// bbcode for comments on forums
function bbcode($text)
{
    // Quotes
    $numquotes = substr_count($text, "[quote=");
    for ($i = 0; ($i < $numquotes); $i++) {
        $text = preg_replace("/\[quote\=(.+?)](.+?)\[\/quote\]/s", '<span class="quotebody"><span class="originallyby">Originally posted by: <a href="/user/$1/">$1</a></span> <br />$2</span>', $text);
    }
    $text = preg_replace("/\[url\=(.+?)](.+?)\[\/url\]/s", '<a class="link" href="$1">$2</a>', $text);
    $text = preg_replace("/\[url\](.+?)\[\/url\]/s", '<a class="link" href="$1">$1</a>', $text);
    $text = preg_replace("/\[img\](.+?)\[\/img\]/s", '<img src="$1"/>', $text);
    $text = preg_replace("/\[b\](.+?)\[\/b\]/s", '<b>$1</b>', $text);
    $text = preg_replace("/\[i\](.+?)\[\/i\]/s", '<i>$1</i>', $text);
    $text = preg_replace("/\[u\](.+?)\[\/u\]/s", '<u>$1</u>', $text);
    $text = preg_replace("/\[center\](.+?)\[\/center\]/s", '<center>$1</center>', $text);
    $text = preg_replace("/\[size\=(.+?)](.+?)\[\/size\]/s", '<span style="font-size:$1px;">$2</span>', $text);
    $text = preg_replace("/\[color\=(.+?)](.+?)\[\/color\]/s", '<span style="color:$1;">$2</span>', $text);
    $text = preg_replace("/\[spoiler\](.+?)\[\/spoiler\]/s", 'Spoiler:<br><div class="spoiler">$1</div>', $text);


    return $text;
}
// Display pagination
function pagination($total_pages, $per_page, $page, $prev, $next, $path)
{
    if (ceil($total_pages / $per_page) > 0) {
        echo '<ul class="pagination">';
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $prev . '/">Previous</i></a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">Previous</i></a></li>';
        }
        if ($page > 3) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '1/">1</a></li>';
            echo '<li class="page-item dots"><a class="page-link">...</a></li>';
        }
        if ($page - 2 > 0) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page - 2 . '/">' . $page - 2 . '</a></li>';
        }
        if ($page - 1 > 0) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page - 1 . '/">' . $page - 1 . '</a></li>';
        }
        echo '<li class="page-item active"><a class="page-link" href="#">' . $page . '</a></li>';
        if ($page + 1 < ceil($total_pages / $per_page) + 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page + 1 . '/">' . $page + 1 . '</a></li>';
        }
        if ($page + 2 < ceil($total_pages / $per_page) + 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $page + 2 . '/">' . $page + 2 . '</a></li>';
        }
        if ($page < ceil($total_pages / $per_page) - 2) {
            echo '<li class="page-item dots"><a class="page-link">...</a></li>';
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . ceil($total_pages / $per_page) . '/">' . ceil($total_pages / $per_page) . '</a></li>';
        }
        if ($page < ceil($total_pages / $per_page)) {
            echo '<li class="page-item"><a class="page-link" href="' . $path . '' . $next . '/">Next</a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
        }
        echo '</ul>';
    }
}
// Crazy search query that i spend to much time on
function searchManga($pdo, $search_term, $per_page = 1, $page = 1)
{

    $calc_page = ($page - 1) * $per_page;

    $tag_search = false;
    $artist_search = false;
    $character_search = false;
    $language_search = false;
    $category_search = false;
    $tag = null;
    $artist = null;
    $character = null;
    $language = null;
    $category = null;

    if (strpos($search_term, ':tag ') !== false) {
        $tag_search = true;
        $tag = str_replace(':tag ', '', $search_term);
    }
    if (strpos($search_term, ':artist ') !== false) {
        $artist_search = true;
        $artist = str_replace(':artist ', '', $search_term);
    }
    if (strpos($search_term, ':character ') !== false) {
        $character_search = true;
        $character = str_replace(':character ', '', $search_term);
    }
    if (strpos($search_term, ':language ') !== false) {
        $language_search = true;
        $language = str_replace(':language ', '', $search_term);
    }
    if (strpos($search_term, ':category ') !== false) {
        $category_search = true;
        $category = str_replace(':category ', '', $search_term);
    }

    $query = "SELECT DISTINCT mangas.* FROM mangas
LEFT JOIN manga_tags ON manga_tags.manga_id = mangas.id
LEFT JOIN tags ON manga_tags.tag_id = tags.id
LEFT JOIN manga_artists ON manga_artists.manga_id = mangas.id
LEFT JOIN artists ON manga_artists.artist_id = artists.id
LEFT JOIN manga_characters ON manga_characters.manga_id = mangas.id
LEFT JOIN characters ON manga_characters.character_id = characters.id
LEFT JOIN manga_languages ON manga_languages.manga_id = mangas.id
LEFT JOIN languages ON manga_languages.language_id = languages.id
LEFT JOIN manga_categories ON manga_categories.manga_id = mangas.id
LEFT JOIN categories ON manga_categories.category_id = categories.id
WHERE ";

    $query_conditions = '';

    if ($tag_search) {
        $tag_terms = explode(',', $tag);
        $i = 0;
        foreach ($tag_terms as $tag_term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "tags.tag LIKE :tag_term$i";
            $i++;
        }
    }
    if ($artist_search) {
        if ($tag_search) {
            $query_conditions .= " AND ";
        }
        $artist_terms = explode(',', $artist);
        $i = 0;
        foreach ($artist_terms as $artist_term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "artists.artist LIKE :artist_term$i";
            $i++;
        }
    }
    if ($character_search) {
        if ($tag_search || $artist_search) {
            $query_conditions .= " AND ";
        }
        $character_terms = explode(',', $character);
        $i = 0;
        foreach ($character_terms as $character_term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "characters.character LIKE :character_term$i";
            $i++;
        }
    }

    if ($language_search) {
        if ($tag_search || $artist_search || $character_search) {
            $query_conditions .= " AND ";
        }
        $language_terms = explode(',', $language);
        $i = 0;
        foreach ($language_terms as $language_term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "languages.language LIKE :language_term$i";
            $i++;
        }
    }
    if ($category_search) {
        if ($tag_search || $artist_search || $character_search || $language_search) {
            $query_conditions .= " AND ";
        }
        $category_terms = explode(',', $category);
        $i = 0;
        foreach ($category_terms as $category_term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "categories.category LIKE :category_term$i";
            $i++;
        }
    }
    if (!$tag_search && !$artist_search && !$character_search && !$language_search && !$category_search) {
        $search_terms = explode(',', $search_term);
        $i = 0;
        foreach ($search_terms as $term) {
            if ($i > 0) {
                $query_conditions .= " OR ";
            }
            $query_conditions .= "mangas.title LIKE :search_term$i OR tags.tag LIKE :search_term$i OR artists.artist LIKE :search_term$i OR characters.character LIKE :search_term$i OR languages.language LIKE :search_term$i OR categories.category LIKE :search_term$i";
            $i++;
        }
    }
    $query .= $query_conditions;
    $query .= " ORDER BY submit_date DESC LIMIT $calc_page, $per_page";
    $stmt = $pdo->prepare($query);

    if ($tag_search) {
        $i = 0;
        foreach ($tag_terms as $tag_term) {
            $stmt->bindValue(":tag_term$i", "%$tag_term%");
            $i++;
        }
    }
    if ($artist_search) {
        $i = 0;
        foreach ($artist_terms as $artist_term) {
            $stmt->bindValue(":artist_term$i", "%$artist_term%");
            $i++;
        }
    }
    if ($character_search) {
        $i = 0;
        foreach ($character_terms as $character_term) {
            $stmt->bindValue(":character_term$i", "%$character_term%");
            $i++;
        }
    }
    if ($language_search) {
        $i = 0;
        foreach ($language_terms as $language_term) {
            $stmt->bindValue(":language_term$i", "%$language_term%");
            $i++;
        }
    }
    if ($category_search) {
        $i = 0;
        foreach ($category_terms as $category_term) {
            $stmt->bindValue(":category_term$i", "%$category_term%");
            $i++;
        }
    }
    if (!$tag_search && !$artist_search && !$character_search && !$language_search && !$category_search) {
        $i = 0;
        foreach ($search_terms as $term) {
            $stmt->bindValue(":search_term$i", "%$term%");
            $i++;
        }
    }

    $stmt->execute();
    $results = $stmt->fetchAll();


    $query1 = "SELECT count(DISTINCT mangas.id) AS id FROM mangas
  LEFT JOIN manga_tags ON manga_tags.manga_id = mangas.id
  LEFT JOIN tags ON manga_tags.tag_id = tags.id
  LEFT JOIN manga_artists ON manga_artists.manga_id = mangas.id
  LEFT JOIN artists ON manga_artists.artist_id = artists.id
  LEFT JOIN manga_characters ON manga_characters.manga_id = mangas.id
  LEFT JOIN characters ON manga_characters.character_id = characters.id
  LEFT JOIN manga_languages ON manga_languages.manga_id = mangas.id
  LEFT JOIN languages ON manga_languages.language_id = languages.id
  LEFT JOIN manga_categories ON manga_categories.manga_id = mangas.id
  LEFT JOIN categories ON manga_categories.category_id = categories.id
  WHERE " . $query_conditions;
    $stmt = $pdo->prepare($query1);
    if ($tag_search) {
        $tag_terms = explode(',', $tag);
        $i = 0;
        foreach ($tag_terms as $tag_term) {
            $stmt->bindValue(":tag_term$i", '%' . $tag_term . '%', PDO::PARAM_STR);
            $i++;
        }
    }
    if ($artist_search) {
        $artist_terms = explode(',', $artist);
        $i = 0;
        foreach ($artist_terms as $artist_term) {
            $stmt->bindValue(":artist_term$i", '%' . $artist_term . '%', PDO::PARAM_STR);
            $i++;
        }
    }
    if ($character_search) {
        $character_terms = explode(',', $character);
        $i = 0;
        foreach ($character_terms as $character_term) {
            $stmt->bindValue(":character_term$i", '%' . $character_term . '%', PDO::PARAM_STR);
            $i++;
        }
    }
    if ($language_search) {
        $language_terms = explode(',', $language);
        $i = 0;
        foreach ($language_terms as $language_term) {
            $stmt->bindValue(":language_term$i", '%' . $language_term . '%', PDO::PARAM_STR);
            $i++;
        }
    }
    if ($category_search) {
        $category_terms = explode(',', $category);
        $i = 0;
        foreach ($category_terms as $category_term) {
            $stmt->bindValue(":category_term$i", '%' . $category_term . '%', PDO::PARAM_STR);
            $i++;
        }
    }
    if (!$tag_search && !$artist_search && !$character_search && !$language_search && !$category_search) {
        $i = 0;
        foreach ($search_terms as $term) {
            $stmt->bindValue(":search_term$i", "%$term%");
            $i++;
        }
    }
    $stmt->execute();
    $sql = $stmt->fetchAll();
    $total_pages = $sql[0]['id'];

    return array($results, $total_pages);
}
// Pull website settings
$stmt = $pdo->prepare('SELECT setting_name, value FROM settings');
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

<?php
include __DIR__ . '/main.inc.php';
include __DIR__ . '/user.inc.php';

if (isset($_GET["act"])) {
    if ($_GET["act"] == 'unfav') {
        if (isset($_SESSION['loggedin'])) { // check if user is logged in
            $user_id = intval($_SESSION['id']); // sanitize the input
            $gallery_id = intval($_POST['gallery_id']); // sanitize the input
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = :user_id AND manga_id = :gallery_id");
            $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':gallery_id', (int) $gallery_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE mangas SET favorites = favorites - 1 WHERE id = :id");
                $stmt->bindValue(':id', (int) $gallery_id, PDO::PARAM_INT);
                $stmt->execute();
                echo 'success';
            } else {
                echo 'not_exists';
            }
        } else {
            echo 'not_logged';
        }
    }

    if ($_GET["act"] == 'addfav') {
        // Check if the user is logged in
        if (!isset($_SESSION['loggedin'])) {
            echo 'not_logged';
        } else {
            // Check if the user has already added this gallery to their favorites
            $user_id = intval($_SESSION['id']); // sanitize the input
            $gallery_id = intval($_POST['gallery_id']); // sanitize the input
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND manga_id = :gallery_id");
            $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':gallery_id', (int) $gallery_id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                echo 'already';
            } else {
                // Check if the user has exceeded the number of allowed favorites
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id");
                $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->fetchColumn();
                if ($count >= 100) {
                    echo 'exceeded';
                } else {
                    // Add the gallery to the user's favorites
                    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, manga_id) VALUES (:user_id, :gallery_id)");
                    $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(':gallery_id', (int) $gallery_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = $pdo->prepare("UPDATE mangas SET favorites = favorites + 1 WHERE id = :id");
                    $stmt->bindValue(':id', (int) $gallery_id, PDO::PARAM_INT);
                    $stmt->execute();
                    echo 'success';
                }
            }
        }
    }

    if ($_GET["act"] == 'savepass') {
        // Check if the user is logged in
        if (!isset($_SESSION['loggedin'])) {
            echo 'not_logged';
        } else {
            $user_id = intval($_SESSION['id']); // sanitize the input
            $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = :user_id');
            $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($_POST['old_password'], $_POST['password'], $_POST['cpassword'])) {
                if (!empty($_POST['password']) && (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5)) {
                    echo 'not_long';
                } else if ($_POST['cpassword'] != $_POST['password']) {
                    echo 'no_match';
                } else {
                    if (password_verify($_POST['old_password'], $account['password'])) {
                        $user_id = intval($_SESSION['id']); // sanitize the input
                        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $account['password'];
                        $stmt = $pdo->prepare('UPDATE accounts SET password = :pass WHERE id = :user_id');
                        $stmt->bindParam(':pass', $password);
                        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo 'success';
                    } else {
                        // Incorrect password
                        echo 'Incorrect username or password!';
                    }
                }
            }
        }
    }

    if ($_GET["act"] == 'saveinfo') {
        // Check if the user is logged in
        if (!isset($_SESSION['loggedin'])) {
            echo 'not_logged';
        } else {
            $user_id = intval($_SESSION['id']); // sanitize the input
            $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = :user_id');
            $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($_POST['email_address'])) {
                if (empty($_POST['email_address'])) {
                    echo 'no_email';
                } else if (!filter_var($_POST['email_address'], FILTER_VALIDATE_EMAIL)) {
                    echo 'valid_email';
                } else {
                    // Check if new username or email already exists in database
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM accounts WHERE (email = :postemail) AND email != :accountemail');
                    $stmt->bindParam(':postemail', $_POST['email_address']);
                    $stmt->bindParam(':accountemail', $account['email']);
                    $stmt->execute();
                    // Account exists? Output error...
                    if ($result = $stmt->fetchColumn()) {
                        echo 'exists';
                    }
                    if (account_activation && $account['email'] != $_POST['email_address']) {
                        $user_id = intval($_SESSION['id']); // sanitize the input
                        $uniqid = uniqid();
                        $stmt = $pdo->prepare('UPDATE accounts SET email = :email, about = :bio, age = :age, gender = :gender, activation_code = :uniqid WHERE id = :user_id');
                        $stmt->bindParam(':email', addslashes(htmlspecialchars($_POST['email_address'])));
                        $stmt->bindParam(':bio', addslashes(htmlspecialchars($_POST['bio'])));
                        $stmt->bindValue(':age', (int) $_POST['age'], PDO::PARAM_INT);
                        $stmt->bindParam(':gender', addslashes(htmlspecialchars($_POST['gender'])));
                        $stmt->bindParam(':uniqid', $uniqid);
                        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        send_activation_email($_POST['email_address'], $uniqid);
                        unset($_SESSION['loggedin']);
                        echo 'activate';
                    } else {
                        $user_id = intval($_SESSION['id']); // sanitize the input
                        $stmt = $pdo->prepare('UPDATE accounts SET email = :email, about = :bio, age = :age, gender = :gender WHERE id = :user_id');
                        $stmt->bindParam(':email', addslashes(htmlspecialchars($_POST['email_address'])));
                        $stmt->bindParam(':bio', addslashes(htmlspecialchars($_POST['bio'])));
                        $stmt->bindValue(':age', (int) $_POST['age'], PDO::PARAM_INT);
                        $stmt->bindParam(':gender', addslashes(htmlspecialchars($_POST['gender'])));
                        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo 'success';
                    }
                }
            }
        }
    }

    if ($_GET["act"] == 'upload') {
        // Check if the user is logged in
        if (!isset($_SESSION['loggedin'])) {
            echo 'not_logged';
        } else {
            $user_id = intval($_SESSION['id']); // sanitize the input
            $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = :user_id');
            $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            $filename = $_FILES["file"]["name"];
            $file_basename = substr($filename, 0, strripos($filename, '.')); // get file extention
            $file_ext = substr($filename, strripos($filename, '.')); // get file name
            $filesize = $_FILES["file"]["size"];
            $allowed_file_types = array('.jpg', '.JPG', '.jpeg', '.JPEG', '.gif', '.GIF', '.png', '.PNG');

            if (in_array($file_ext, $allowed_file_types) && ($filesize < 200000)) {
                // Rename file
                $newfilename = $_SESSION['name'] . "-" . md5($file_basename) . $file_ext;
                if (file_exists("/home/reaper/public_html/uploads/" . $newfilename)) {
                    // file already exists error
                    echo 'exists';
                } else {
                    if ($account['avatar'] == 'no.jpg') {
                        $user_id = intval($_SESSION['id']); // sanitize the input
                        move_uploaded_file($_FILES["file"]["tmp_name"], "/home/reaper/public_html/uploads/" . $newfilename);
                        $stmt = $pdo->prepare("UPDATE accounts SET avatar = :avatar WHERE id = :user_id");
                        $stmt->bindParam(':avatar', $newfilename);
                        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo 'success';
                    } else {
                        $user_id = intval($_SESSION['id']); // sanitize the input
                        $remove_old = "/home/reaper/public_html/uploads/" . $account['avatar'];
                        unlink($remove_old);
                        move_uploaded_file($_FILES["file"]["tmp_name"], "/home/reaper/public_html/uploads/" . $newfilename);
                        $stmt = $pdo->prepare("UPDATE accounts SET avatar = :avatar WHERE id = :user_id");
                        $stmt->bindParam(':avatar', $newfilename);
                        $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo 'success';
                    }
                }
            } elseif (empty($file_basename)) {
                // file selection error
                echo 'no_file';
            } elseif ($filesize > 9200000) {
                // file size error
                echo 'filesize';
            } else {
                // file type error
                echo 'file_type';
                unlink($_FILES["file"]["tmp_name"]);
            }
        }
    }
}

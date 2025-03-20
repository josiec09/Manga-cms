<?php

// The below function will check if the user is logged-in and also check the remember me cookie
function check_loggedin($pdo, $redirect_file = '/login/')
{
	// If you want to update the "last seen" column on every page load, you can uncomment the below code
	/*
	if (isset($_SESSION['loggedin'])) {
		$date = date('Y-m-d\TH:i:s');
		$stmt = $pdo->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
		$stmt->execute([ $date, $_SESSION['id'] ]);
	}
	*/
	// Check for remember me cookie variable and loggedin session variable
	if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme']) && !isset($_SESSION['loggedin'])) {
		// If the remember me cookie matches one in the database then we can update the session variables.
		$stmt = $pdo->prepare('SELECT * FROM accounts WHERE rememberme = :rememberme');
		$stmt->bindParam(':rememberme', $_COOKIE['rememberme']);
		$stmt->execute();
		$account = $stmt->fetch(PDO::FETCH_ASSOC);
		// If account exists...
		if ($account) {
			// Found a match, update the session variables and keep the user logged-in
			session_regenerate_id();
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['name'] = $account['username'];
			$_SESSION['id'] = $account['id'];
			$_SESSION['role'] = $account['role_id'];
			$_SESSION['last_seen'] = $account['last_seen'];
			// Update last seen date
			//$date = date('Y-m-d\TH:i:s');
			//$stmt = $pdo->prepare('UPDATE accounts SET last_seen = :last_seen WHERE id = :id');
			//$stmt->bindParam(':last_seen', $date);
			//$stmt->bindValue(':id', (int) $account['id'], PDO::PARAM_INT);
			//$stmt->execute();
		} else {
			// If the user is not remembered redirect to the login page.
			header('Location: ' . $redirect_file);
			exit;
		}
	} else if (!isset($_SESSION['loggedin'])) {
		// If the user is not logged in redirect to the login page.
		header('Location: ' . $redirect_file);
		exit;
	} else if ($_SESSION['2fcode'] !== '-1') {
		if (!isset($_SESSION['googleCode'])) :
			header("location: ' . $redirect_file");
			exit();
		endif;
	}
}
// Send activation email function
function send_activation_email($email, $code)
{
	$stmt = $pdo->prepare('SELECT setting_name, value FROM settings');
	$stmt->execute();
	$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
	// Email Subject
	$subject = 'Account Activation Required';
	// Email Headers
	$headers = 'From: ' . $settings['mail_from'] . "\r\n" . 'Reply-To: ' . $settings['mail_from']  . "\r\n" . 'Return-Path: ' . $settings['mail_from']  . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
	// Activation link
	$activate_link = $settings['activation_link']  . '/' . $email . '/' . $code . '/';
	// Read the template contents and replace the "%link" placeholder with the above variable
	$email_template = str_replace('%link%', $activate_link, file_get_contents('/home/reaper/public_html/activation-email-template.html'));
	// Send email to user
	mail($email, $subject, $email_template, $headers);
}

function login_attempts($pdo, $update = TRUE)
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$now = date('Y-m-d H:i:s');
	if ($update) {
		$stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, `date`) VALUES (:ip,:noww) ON DUPLICATE KEY UPDATE attempts_left = attempts_left - 1, `date` = VALUES(`date`)');
		$stmt->bindParam(':ip', $ip);
		$stmt->bindParam(':noww', $now);
		$stmt->execute();
		//$stmt->execute([$ip, $now]);
	}
	$stmt = $pdo->prepare('SELECT * FROM login_attempts WHERE ip_address = :ip');
	$stmt->bindParam(':ip', $ip);
	$stmt->execute();
	$login_attempts = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($login_attempts) {
		// The user can try to login after 1 day... change the "+1 day" if you want to increase/decrease this date.
		$expire = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($login_attempts['date'])));
		if ($now > $expire) {
			$stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = :ip');
			$stmt->bindParam(':ip', $ip);
			$stmt->execute();
			$login_attempts = array();
		}
	}
	return $login_attempts;
}

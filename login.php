<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// No need for the user to see the login form if they're logged-in, so redirect them to the home page
if (isset($_SESSION['loggedin'])) {
	// If the user is not logged in, redirect to the home page.
	header('Location: /');
	exit;
}

$msg = '';

if (isset($_POST['username'], $_POST['password'])) {

	$login_attempts = login_attempts($pdo, FALSE);
	if ($login_attempts && $login_attempts['attempts_left'] <= 0) {
		$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You cannot login right now! Please try again later!</div>';
	}
	// Now we check if the data from the login form was submitted, isset() will check if the data exists.
	if (!isset($_POST['username'], $_POST['password'])) {
		$login_attempts = login_attempts($pdo);
		// Could not retrieve the data that should have been sent
		$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please fill both the username and password field!</div>';
	}
	// Prepare our SQL query and find the account associated with the login details
	// Preparing the SQL statement will prevent SQL injection
	$stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :username');
	$stmt->bindParam(':username', $_POST['username']);
	$stmt->execute();
	$account = $stmt->fetch(PDO::FETCH_ASSOC);
	// Check if the account exists
	if ($account) {
		// Account exists... Verify the password
		if (password_verify($_POST['password'], $account['password'])) {
			// Check if the account is activated
			if ($settings['account_activation'] == 'true' && $account['activation_code'] != 'activated') {
				// User has not activated their account, output the message
				$msg = '<div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please activate your account to login! Click <a href="/activate/resend/">here</a> to resend the activation email.</div>';
			} else {
				// Verification success! User has loggedin!
				// Declare the session variables, which will basically act like cookies, but will store the data on the server as opposed to the client
				session_regenerate_id();
				$_SESSION['name'] = $account['username'];
				$_SESSION['id'] = $account['id'];
				$_SESSION['role'] = $account['role_id'];
				$_SESSION['2fcode'] = $account['2fcode'];

				// Update last seen date
				//$date = date('Y-m-d\TH:i:s');
				//$stmt = $pdo->prepare('UPDATE accounts SET last_seen = :last_seen WHERE id = :id');
				//$stmt->bindParam(':last_seen', $date);
				//$stmt->bindValue(':id', (int) $account['id'], PDO::PARAM_INT);
				//$stmt->execute();
				
				$ip = $_SERVER['REMOTE_ADDR'];
				$stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = :ip');
				$stmt->bindParam(':ip', $ip);
				$stmt->execute();
				if ($account['2fcode'] == '-1') {
					$_SESSION['loggedin'] = TRUE;
					header('Location: /');
				} else {
					header('Location: /two-factor/');
				}
			}
		} else {
			// Incorrect password
			$login_attempts = login_attempts($pdo, TRUE);
			$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Incorrect username and/or password! You have ' . $login_attempts['attempts_left'] . ' attempts remaining!</div>';
		}
	} else {
		// Incorrect username
		$login_attempts = login_attempts($pdo, TRUE);
		$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Incorrect username and/or password! You have ' . $login_attempts['attempts_left'] . ' attempts remaining!</div>';
	}
}
template_header('Login');

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
						<li class="active"><a href="/login/"><i class="fa fa-sign-in"></i>Login</a></li>
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
			<h1 class="tag_info">Login</h1><br>
			<div class="login_page">
				<div class="login_form">
					<?= $msg ?><br>
					<form action="/login/" method="post">
						<div class="input_group">
							<label for="name"><span class="input_addon"><i class="fa fa-user"></i></span></label>
							<input type="text" name="username" placeholder="Username" id="username" required>
						</div>
						<div class="input_group">
							<label for="password"><span class="input_addon"><i class="fa fa-key"></i></span></label>
							<input type="password" name="password" placeholder="Password" id="password" required>
						</div>
						<div class="input_group">
							Don't have an account? <a class="link" href="/register/">Register</a><br>
							Forgot your password? <a class="link" href="/password/reset/">Reset it</a><br>
							Need activation code? <a class="link" href="/activate/resend/">Send it</a>
						</div>
						<div class="clear"></div>
						<div class="input_group">
							<input class="btn btn-log" type="submit" name="go_login" value="Login">
						</div>
						<div class="clear"></div>
					</form>
				</div>
				<div class="clear"></div>

			</div>
		</div>
	</div>
</div>
<?= template_footer() ?>
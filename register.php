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
// Also check if they are "remembered"
if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme'])) {
	// If the remember me cookie matches one in the database then we can update the session variables and the user will be logged-in.
	$stmt = $pdo->prepare('SELECT * FROM accounts WHERE rememberme = :rememberme');
	$stmt->bindParam(':rememberme', $_COOKIE['rememberme']);
	$stmt->execute();
	$account = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($account) {
		// Authenticate the user
		session_regenerate_id();
		$_SESSION['loggedin'] = TRUE;
		$_SESSION['name'] = $account['username'];
		$_SESSION['id'] = $account['id'];
		$_SESSION['role'] = $account['role_id'];
		// Update last seen date
		$date = date('Y-m-d\TH:i:s');
		//$stmt = $pdo->prepare('UPDATE accounts SET last_seen = :last_seen WHERE id = :id');
		//$stmt->bindParam(':last_seen', $date);
		//$stmt->bindValue(':id', (int) $account['id'], PDO::PARAM_INT);
		//$stmt->execute();
		// Redirect to home page
		header('Location: /');
		exit;
	}
}

$msg = '';

if (isset($_POST['username'], $_POST['password'], $_POST['cpassword'], $_POST['email'], $_POST['captcha'], $_SESSION['captcha'])) {
	// Make sure the submitted registration values are not empty.
	if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
		// One or more values are empty.
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please complete the registration form!</div>');
	} else if ($_SESSION['captcha'] !== $_POST['captcha']) {
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Incorrect captcha code!</div>');
	}
	// Check to see if the email is valid.
	else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please provide a valid email address!</div>');
	}
	// Username must contain only characters and numbers.
	else if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['username'])) {
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Username must contain only letters and numbers!</div>');
	}
	// Password must be between 5 and 20 characters long.
	else if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Password must be between 5 and 20 characters long!</div>');
	}
	// Check if both the password and confirm password fields match
	else if ($_POST['cpassword'] != $_POST['password']) {
		$msg = ('<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Passwords do not match!</div>');
	} else {
		// Check if the account with that username already exists
		$stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :username OR email = :email');
		$stmt->bindParam(':username', $_POST['username']);
		$stmt->bindParam(':email', $_POST['email']);
		$stmt->execute();
		$account = $stmt->fetch(PDO::FETCH_ASSOC);
		// Store the result, so we can check if the account exists in the database.
		if ($account) {
			// Username already exists
			$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Username and/or email exists!</div>';
		} else {
			// Username doesn't exist, insert new account
			// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			// Generate unique activation code
			//$uniqid = $settings['account_activation'] ? uniqid() : 'activated';
			$uniqid = ($settings['account_activation'] == 'true') ? uniqid() : 'activated';
			// Default role
			$role = '3';
			// Current date
			$date = date('Y-m-d\TH:i:s');
			// Prepare query; prevents SQL injection CHANGE LATER!!!!
			$stmt = $pdo->prepare('INSERT INTO accounts (username, password, email, activation_code, role, registered, last_seen) VALUES (:username, :pass, :email, :uniqid, :accountrole, :registered, :last_seen)');
			$stmt->bindParam(':username', $_POST['username']);
			$stmt->bindParam(':pass', $password);
			$stmt->bindParam(':email', $_POST['email']);
			$stmt->bindParam(':uniqid', $uniqid);
			$stmt->bindParam(':accountrole', $role);
			$stmt->bindParam(':registered', $date);
			$stmt->bindParam(':last_seen', $date);
			$stmt->execute();
			// If account activation is required, send activation email
			if ($settings['account_activation'] == 'true') {
				// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
				send_activation_email($_POST['email'], $uniqid);
				$msg = '<div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please check your email to activate your account!</div>';
			} else {
				// Automatically authenticate the user if the option is enabled
				if ($settings['auto_login_after_register'] == 'true') {
					// Regenerate session ID
					session_regenerate_id();
					// Declare session variables
					$_SESSION['loggedin'] = TRUE;
					$_SESSION['name'] = $_POST['username'];
					$_SESSION['id'] = $pdo->lastInsertId();
					$_SESSION['role'] = $role;
					header('Location: /');
				} else {
					$msg = '<div style="margin:0" class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> You have successfully registered! You can now login!</div>';
				}
			}
		}
	}
}

template_header('Register');

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
						<li class="active"><a href="/register/"><i class="fa fa-user"></i>Register</a></li>
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
			<h1 class="tag_info">Register</h1><br>
			<div class="register_page">
				<div class="register_form">
					<?= $msg ?><br>
					<form action="/register/" method="post">
						<div class="input_group">
							<label for="email"><span class="input_addon"><i class="fa fa-envelope-o"></i></span></label>
							<input type="email" name="email" placeholder="Email" id="email" required>
						</div>
						<div class="input_group">
							<label for="name"><span class="input_addon"><i class="fa fa-user"></i></span></label>
							<input type="text" name="username" placeholder="Username" id="username" required>
						</div>
						<div class="input_group">
							<label for="password"><span class="input_addon"><i class="fa fa-key"></i></span></label>
							<input type="password" name="password" placeholder="Password" id="password" required>
						</div>
						<div class="input_group">
							<label for="cpassword"><span class="input_addon"><i class="fa fa-key"></i></span></label>
							<input type="password" name="cpassword" placeholder="Confirm Password" id="cpassword" required>
						</div>
						<div class="input_group">
							<img src="/includes/captcha.php?rand=<?php echo rand(); ?>" id='captcha_image'><br>
							Need another security code? <a href="javascript:void(0)" class="link" id="reloadCaptcha">Click Here</a>
							<!--<img src="../includes/captcha.php" width="150" height="50">-->
						</div>
						<div class="input_group">
							<label for="captcha"><span class="input_addon"><i class="fa fa-user-secret"></i></span></label>
							<input type="text" id="captcha" name="captcha" placeholder="Enter security code" title="Please enter the security code!" required>
						</div>
						<div class="input_group">
							Already have an account? <a class="link" href="/login/">Login</a><br>
						</div>
						<div class="clear"></div>
						<div class="input_group">
							<input class="btn btn-reg" type="submit" name="go_register" value="Register"><br>
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
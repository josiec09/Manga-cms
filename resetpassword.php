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

// Output message
$msg = '';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
	// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
	$stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = :email AND reset = :code');
	$stmt->bindParam(':email', $_GET['email']);
	$stmt->bindParam(':code', $_GET['code']);
	$stmt->execute();
	$account = $stmt->fetch(PDO::FETCH_ASSOC);
	// If the account exists with the email and code
	if ($account) {
		if (isset($_POST['npassword'], $_POST['cpassword'])) {
			if (strlen($_POST['npassword']) > 20 || strlen($_POST['npassword']) < 5) {
				$msg = '<div style="margin:0" class="alert alert-danger">Password must be between 5 and 20 characters long!</div>';
			} else if ($_POST['npassword'] != $_POST['cpassword']) {
				$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Passwords must match!</div>';
			} else {
				$stmt = $pdo->prepare('UPDATE accounts SET password = :pass, reset = "" WHERE email = :email');
				$password = password_hash($_POST['npassword'], PASSWORD_DEFAULT);
				$stmt->bindParam(':pass', $password);
				$stmt->bindParam(':email', $_GET['email']);
				$stmt->execute();
				$msg = '<div style="margin:0" class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> Password has been reset! You can now <a href="/login/">login</a>!</div>';
			}
		}
	} else {
		$msg = '<div style="margin:0" class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Incorrect email and/or code!</div>';
	}
} else {
	$msg = '<div style="margin:0" class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please provide the email and code!</div>';
}

template_header('Reset Password');

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
			<h1 class="tag_info">Reset Password</h1><br>
			<div class="login_page">
				<div class="login_form">
					<?= $msg ?><br>
					<?php if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
						// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
						$stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ? AND reset = ?');
						$stmt->execute([$_GET['email'], $_GET['code']]);
						$account = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($account) {
					?>
							<form action="/password/reset/<?= $_GET['email'] ?>/<?= $_GET['code'] ?>/" method="post">
								<div class="input_group">
									<label for="npassword"><span class="input_addon"><i class="fa fa-key"></i></span></label>
									<input type="password" name="npassword" placeholder="New Password" id="npassword" required>
								</div>
								<div class="input_group">
									<label for="cpassword"><span class="input_addon"><i class="fa fa-key"></i></span></label>
									<input type="password" name="cpassword" placeholder="Confirm Password" id="cpassword" required>
								</div>
								<div class="clear"></div>
								<div class="input_group">
									<input class="btn btn-log" type="submit" name="go_reset" value="Reset"><br>
								</div>
								<div class="clear"></div>
							</form>
						<? } else { ?>

					<? }
					} ?>
				</div>
				<div class="clear"></div>

			</div>
		</div>
	</div>
</div>
<?= template_footer() ?>
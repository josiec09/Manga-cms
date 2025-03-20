<?php
// Template header; feel free to customize it, but do not indent the PHP code or it will throw an error
function template_header($title)
{
    echo <<<EOT
	<!DOCTYPE html>
	<html lang="en">
		<head>
		<title>Halloweeb.Town - $title</title>
		<!-- META  -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover" />
		<meta name="description" content="Latest free manga." />

		<!-- Google Fonts -->
		<link type="text/css" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600&display=swap" rel="stylesheet">

		<!-- CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="/css/style.css" />
		<link rel="stylesheet" href="/css/index.css" />
		<link rel="stylesheet" href="/css/jquery-ui.min.css" />

		<!-- JS -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script src="/js/main.js"></script>
		</head>
		<body>
	EOT;
}

// Template footer
function template_footer()
{
    echo <<<EOT
	<div class="footer_center">
	<a href="/">Home</a>
	<a href="/forums/">Forums</a>
	<a href="/tags/">Tags</a>
	<a href="/artists/">Artists</a>
	<a href="/characters/">Characters</a>
	<a href="/info/">Info</a>
	</div>
	<script src="/js/user.js"></script>
	<script src="/js/comments.js"></script>
	<script src="/js/btnloadmore.js"></script>
		</body>
	</html>
	EOT;
}

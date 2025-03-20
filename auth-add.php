<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';

$checkResult="";
if($_POST['code']){
$code = htmlspecialchars($_POST['code']);	
$secret = $_SESSION['2fcode'];

require_once 'includes/GoogleAuthenticator.php';
$ga = new PHPGangsta_GoogleAuthenticator();
$checkResult = $ga->verifyCode($secret, $code, 2);    // 2 = 2*30sec clock tolerance


if ($checkResult){
	$_SESSION['googleCode']	= $code;
	header("location:/");
    exit;

} 
else{
	header("location:/two-factor/add/?error=1");
    exit;
}

}

?>
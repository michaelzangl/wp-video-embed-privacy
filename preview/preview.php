<?php
// License: GPLv2

if (!isset($_REQUEST['s']) || !isset($_REQUEST['s'])) {
	die("v");
}

$v = $_REQUEST["v"];
$s = $_REQUEST["s"];
preg_match("/^[\w-]+$/", $v) or die("invalid: $v");

try {
	define('__ACCESS_VEP_SETTINGS__', 1);
	$settings = include(dirname(__FILE__) . '/settings.php');
} catch (Throwable $e) {
	die('Settings missing');
}

if ($s !== hash('sha256', $settings['key'] . $v)) {
	die('Wrong key');
}

@header('Content-type: image/jpeg');

$url = "http://img.youtube.com/vi/$v/hqdefault.jpg";

$c = file_get_contents($url);

// Set this to allow local caching of images.
if ($settings['cache']) {
	@file_put_contents(dirname(__FILE__) . "/$v.jpg", $c);
}
echo $c;

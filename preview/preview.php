<?php
// License: GPLv2

if (!isset($_REQUEST['s']) || !isset($_REQUEST['v'])) {
	die("v");
}

$t = $_REQUEST["t"];
$v = $_REQUEST["v"];
$s = $_REQUEST["s"];
preg_match("/^[\w-]+$/", $v) or die("invalid v");

try {
	define('__ACCESS_VEP_SETTINGS__', 1);
	$settings = include(dirname(__FILE__) . '/settings.php');
} catch (Throwable $e) {
	die('Settings missing');
}

if ($s !== hash('sha256', $settings['key'] . $t . '/' . $v)) {
	die('Wrong key');
}

require_once('guzzle/autoloader.php');

use \GuzzleHttp\Client;

// determine url to fetch image from
if ($t === 'yt') {
	$url = "http://img.youtube.com/vi/$v/hqdefault.jpg";
} else if ($t == 'vimeo') {
	$vimeoApiResponse = (new Client())->request('GET', "https://vimeo.com/api/oembed.json?url=https://vimeo.com/video/$v");
	$url = json_decode($vimeoApiResponse->getBody())->thumbnail_url;
} else {
	die('Illegal type: ' . $t);
}

// real request
$res = (new Client())->request('GET', $url);
if ($settings['cache']) {
	@mkdir(dirname(__FILE__) . "/$t");
	@file_put_contents(dirname(__FILE__) . "/$t/$v.jpg", $res->getBody());
}

@header('Content-type: ' . $res->getHeaderLine('content-type'));
echo $res->getBody();

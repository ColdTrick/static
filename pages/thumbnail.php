<?php
/**
 * Show the thumbnail
 */

// won't be able to serve anything if no joindate or guid
if (!isset($_GET['guid'])) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

$icontime = (int) $_GET['icontime'];
$size = strtolower($_GET['size']);
$guid = (int) $_GET['guid'];

// If is the same ETag, content didn't changed.
$etag = md5($icontime . $size . $guid);
if (isset($_SERVER["HTTP_IF_NONE_MATCH"])) {
	list ($etag_header) = explode("-", trim($_SERVER["HTTP_IF_NONE_MATCH"], "\""));
	if ($etag_header === $etag) {
		header("HTTP/1.1 304 Not Modified");
		exit;
	}
}

// 'traditional' Elgg installation
$autoload_root = dirname(dirname(dirname(dirname(__DIR__))));
if (!is_file("$autoload_root/vendor/autoload.php")) {
	// installation through composer
	$autoload_root = dirname(dirname(dirname(__DIR__)));
}

require_once "$autoload_root/vendor/autoload.php";

$data_root = \Elgg\Application::getDataPath();
$locator = new \Elgg\EntityDirLocator($guid);
$entity_path = $data_root . $locator->getPath();

$filename = $entity_path . "thumb{$size}.jpg";
$filecontents = @file_get_contents($filename);

// try fallback size
if (!$filecontents && $size !== "medium") {
	$filename = $entity_path . "thumbmedium.jpg";
	$filecontents = @file_get_contents($filename);
}

if ($filecontents) {
	$filesize = strlen($filecontents);
	
	header("Content-type: image/jpeg");
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
	header("Pragma: public");
	header("Cache-Control: public");
	header("Content-Length: $filesize");
	header("ETag: \"$etag\"");
	
	echo $filecontents;
	exit;
}

// something went wrong so 404
header("HTTP/1.1 404 Not Found");
exit;

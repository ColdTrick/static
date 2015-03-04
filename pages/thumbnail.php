<?php
/**
 * Show the thumbnail
 */

// won't be able to serve anything if no joindate or guid
$guid = (int) get_input("guid");
if (empty($guid)) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

$icontime = (int) get_input("icontime");
$size = strtolower(get_input("size", "medium"));

// If is the same ETag, content didn't changed.
$etag = md5($icontime . $size . $guid);
if (isset($_SERVER["HTTP_IF_NONE_MATCH"])) {
	list ($etag_header) = explode("-", trim($_SERVER["HTTP_IF_NONE_MATCH"], "\""));
	if ($etag_header === $etag) {
		header("HTTP/1.1 304 Not Modified");
		exit;
	}
}

$fh = new ElggFile();
$fh->owner_guid = $guid;
$fh->setFilename("thumb{$size}.jpg");

$filecontents = $fh->grabFile();

// try fallback size
if (!$filecontents && $size !== "medium") {
	$fh->setFilename("thumbmedium.jpg");
	$filecontents = $fh->grabFile();
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

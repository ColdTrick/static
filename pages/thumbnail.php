<?php
/**
 * Show the icon of a blog
 */

$guid = (int) get_input("guid");
$size = strtolower(get_input("size"));

$icon_sizes = elgg_get_config("icon_sizes");
if (!array_key_exists($size, $icon_sizes)) {
	$size = "medium";
}

$success = false;
$contents = "";

$entity = get_entity($guid);
if (!empty($entity) && elgg_instanceof($entity, "object", "static")) {
	
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $entity->getGUID();
	$filehandler->setFilename("thumb" . $size . ".jpg");
	
	if ($filehandler->exists()) {
		if ($contents = $filehandler->grabFile()) {
			$success = true;
		}
	}
}

if (!$success) {
	$contents = @file_get_contents(elgg_get_root_path() . "_graphics/icons/default/" . $size . ".png");
}

header("Content-type: image/jpeg");
header("Expires: " . date("r", time() + 864000));
header("Pragma: public");
header("Cache-Control: public");
header("Content-Length: " . strlen($contents));

echo $contents;

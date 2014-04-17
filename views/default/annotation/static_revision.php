<?php

$annotation = $vars['annotation'];

$owner = get_entity($annotation->owner_guid);
if (!$owner) {
	return true;
}

$owner_link = "<a href=\"{$owner->getURL()}\">$owner->name</a>";

$friendlytime = elgg_view_friendly_time($annotation->time_created);

echo "<div>" . $owner_link . "<span class='elgg-subtext'>" . $friendlytime . "</span></div>";

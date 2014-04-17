<?php

$entity = $vars["entity"];
if ($entity) {
	$annotations = elgg_list_annotations(array("guid" => $entity->getGUID(), "annotation_name" => "static_revision", "limit" => false));
	if ($annotations) {
		echo elgg_view_module("aside", elgg_echo("static:revisions"), $annotations);
	}
}
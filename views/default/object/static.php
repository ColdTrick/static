<?php 

$entity = $vars["entity"];

$edit_link = elgg_view("output/url", array(
    "href" => $vars["url"] . "admin/static/edit?guid=" . $entity->getGUID(),
    "text" => elgg_echo("edit")));
$delete_link = elgg_view("output/confirmlink", array(
    "href" => $vars["url"] . "action/static/delete?guid=" . $entity->getGUID(),
    "text" => elgg_echo("delete")));

echo "<div class='contentWrapper'><a href='" . $entity->getURL() . "'>" . $entity->title . "</a> [ " . $edit_link . " | " . $delete_link . " ]</div>";

<?php 

global $CONFIG;

$guid = get_input("guid");
if($guid){
    $entity = get_entity($guid);
}

if(! $entity || ($entity->getSubtype() != "static")) {
    forward();
}

// show content
$content = $entity->description;

if ($entity->canEdit()){
    $edit_link = elgg_view(
        "output/url",
        array(
             "href" => "admin/static/edit?guid=" . $entity->getGUID(),
             "text" => elgg_echo("edit")));

    $delete_link = elgg_view(
        "output/confirmlink",
        array(
             "href" => "action/static/delete?guid=" . $entity->getGUID(),
             "text" => elgg_echo("delete")));

    $content .= $edit_link . " | " . $delete_link;
}

$title = $entity->title;

$body = elgg_view_layout('content', array(
    'filter' => '',
    'content' => $content,
    'title' => $title,
));

echo elgg_view_page($title, $body);

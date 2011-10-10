<?php

$guid = get_input("guid");
if ($guid) {
    $content = get_entity($guid);
    if ($content && ($content->getSubtype() == "static")) {
        echo elgg_view_form(
            "static/edit",
            array('action' => $CONFIG->wwwroot . "action/static/edit"),
            array('entity' => $content));
        return;
    }
}
forward();

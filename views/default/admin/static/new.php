<?php

$vars = array(
    'entity' => null,
);
echo elgg_view_form("static/edit", array('action' => $CONFIG->wwwroot . "action/static/edit"), $vars);

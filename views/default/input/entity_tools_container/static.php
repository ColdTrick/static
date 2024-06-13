<?php

$vars['add_site'] = true;
$vars['add_groups'] = static_group_enabled();

echo elgg_view('input/entity_tools_container', $vars);

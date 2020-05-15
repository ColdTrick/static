<?php

$vars['add_site'] = true;
$vars['add_groups'] = elgg_get_plugin_setting('enable_groups', 'static') === 'yes';

echo elgg_view('input/entity_tools_container', $vars);

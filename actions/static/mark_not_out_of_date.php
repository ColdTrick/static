<?php

$guid = (int) get_input('guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$entity = get_entity($guid);
if (!$entity instanceof StaticPage || !$entity->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$entity->save();

return elgg_ok_response('', elgg_echo('save:success'));

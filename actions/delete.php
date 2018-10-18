<?php

$guid = (int) get_input('guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}
	
$ia = elgg_set_ignore_access(true);
$entity = get_entity($guid);
elgg_set_ignore_access($ia);

if (!$entity instanceof StaticPage || !$entity->canDelete()) {
	return elgg_error_response(elgg_echo('entity:delete:permission_denied'));
}

$title = $entity->getDisplayName();
$container = $entity->getContainerEntity();

if (!$entity->delete()) {
	return elgg_error_response(elgg_echo('entity:delete:fail', [$title]));
}

$forward_url = 'static/all';
if ($container instanceof ElggGroup) {
	$forward_url = "static/group/{$container->guid}";
}

return elgg_ok_response('', elgg_echo('entity:delete:success', [$title]), $forward_url);

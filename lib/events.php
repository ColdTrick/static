<?php
/**
 * All event handlers are bundled in this file
 */

/**
 * Make sure the last editor of a static page gets notified about a comment
 *
 * @param string     $event  'create'
 * @param string     $type   'object'
 * @param ElggObject $object the object that was just created
 *
 * @return void
 */
function static_create_comment_handler($event, $type, ElggObject $object) {

	// check of this is a comment
	if (empty($object) || !elgg_instanceof($object, "object", "comment")) {
		return;
	}

	// is it a comment on a static page
	$container = $object->getContainerEntity();
	if (empty($container) || !elgg_instanceof($container, "object", "static")) {
		return;
	}

	$comment_owner = $object->getOwnerEntity();

	// get last revisor
	$ia = elgg_set_ignore_access(true);
	$revisions = $container->getAnnotations(array(
		"annotation_name" => "static_revision",
		"limit" => 1,
		"reverse_order_by" => true
	));

	$static_owner = $revisions[0]->getOwnerEntity();

	elgg_set_ignore_access($ia);

	// @see actions/comment/save
	$subject = elgg_echo("generic_comment:email:subject");
	$message = elgg_echo("generic_comment:email:body", array(
		$container->title,
		$comment_owner->name,
		$object->description,
		$container->getURL(),
		$comment_owner->name,
		$comment_owner->getURL()
	));

	$params = array(
		"object" => $object,
		"action" => "create",
	);

	// don't notify yourself
	if ($static_owner->getGUID() != $comment_owner->getGUID()) {
		notify_user($static_owner->getGUID(), $comment_owner->getGUID(), $subject, $message, $params);
	}
}

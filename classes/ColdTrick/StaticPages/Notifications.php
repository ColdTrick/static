<?php

namespace ColdTrick\StaticPages;

/**
 * Notifications
 */
class Notifications {
	
	/**
	 * Make sure the last editor of a static page gets notified about a comment
	 *
	 * @param string     $event  'create'
	 * @param string     $type   'object'
	 * @param ElggObject $object the object that was just created
	 *
	 * @return void
	 */
	public static function notifyLastEditor($event, $type, ElggObject $object) {
	
		// check of this is a comment
		if (empty($object) || !elgg_instanceof($object, 'object', 'comment')) {
			return;
		}
	
		// is it a comment on a static page
		$container = $object->getContainerEntity();
		if (empty($container) || !elgg_instanceof($container, 'object', 'static')) {
			return;
		}
	
		$comment_owner = $object->getOwnerEntity();
	
		// get last revisor
		$ia = elgg_set_ignore_access(true);
		$revisions = $container->getAnnotations([
			'annotation_name' => 'static_revision',
			'limit' => 1,
			'reverse_order_by' => true,
		]);
	
		$static_owner = $revisions[0]->getOwnerEntity();
	
		elgg_set_ignore_access($ia);
		
		// don't notify yourself
		if ($static_owner->getGUID() == $comment_owner->getGUID()) {
			return;
		}
			
		// @see actions/comment/save
		$subject = elgg_echo('generic_comment:email:subject');
		$message = elgg_echo('generic_comment:email:body', [
			$container->title,
			$comment_owner->name,
			$object->description,
			$container->getURL(),
			$comment_owner->name,
			$comment_owner->getURL(),
		]);
	
		$params = [
			'object' => $object,
			'action' => 'create',
		];
	
		notify_user($static_owner->getGUID(), $comment_owner->getGUID(), $subject, $message, $params);
	}
}
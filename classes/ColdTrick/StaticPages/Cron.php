<?php

namespace ColdTrick\StaticPages;

/**
 * Cron
 */
class Cron {

	/**
	 * Notify users about out-of-date content
	 *
	 * @param string $hook         'cron'
	 * @param string $type         'daily'
	 * @param string $return_value optional output
	 * @param array  $params       supplied params
	 *
	 * @return void
	 */
	public static function outOfDateNotification($hook, $type, $return_value, $params) {
		
		if (!static_out_of_date_enabled()) {
			return;
		}
		
		$time = elgg_extract('time', $params, time());
		$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
		
		$compare_ts = $time - ($days * 24 * 60 * 60);
		$users = [];
		
		$options = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'limit' => false,
			'modified_time_upper' => $compare_ts,
			'modified_time_lower' => $compare_ts - (24 * 60 * 60),
			'order_by' => 'e.time_updated DESC',
		];
		
		// ignore access
		$ia = elgg_set_ignore_access(true);
		
		$batch = new \ElggBatch('elgg_get_entities', $options);
		/* @var $entity \StaticPage */
		foreach ($batch as $entity) {
			
			// allows a hook to influence entities being notified
			if (!$entity->isOutOfDate()) {
				continue;
			}
			
			$last_editor = $entity->getLastEditor();
			if (empty($last_editor)) {
				continue;
			}
			
			// make sure we need to notify this user
			$recipient = self::checkRecipient($entity, $last_editor);
			if (!($recipient instanceof \ElggUser)) {
				continue;
			}
			
			if (!isset($users[$recipient->getGUID()])) {
				$users[$recipient->getGUID()] = [
					'new' => [],
				];
			}
			
			$users[$recipient->getGUID()]['new'][] = [
				'title' => $entity->getDisplayName(),
				'url' => $entity->getURL(),
			];
		}
		
		// check for reminders
		$number_of_reminders = (int) elgg_get_plugin_setting('out_of_date_reminder_repeat', 'static');
		$reminder_interval = (int) elgg_get_plugin_setting('out_of_date_reminder_interval', 'static');
		if ($number_of_reminders > 0 && $reminder_interval > 0) {
			
			for ($i = 1; $i <= $number_of_reminders; $i++) {
				$compare_ts = $compare_ts - ($reminder_interval * 24 * 60 * 60);
				$options['modified_time_upper'] = $compare_ts;
				$options['modified_time_lower'] = $compare_ts - (24 * 60 * 60);
				
				$batch = new \ElggBatch('elgg_get_entities', $options);
				/* @var $entity \StaticPage */
				foreach ($batch as $entity) {
					
					// allows a hook to influence entities being notified
					if (!$entity->isOutOfDate()) {
						continue;
					}
					
					$last_editor = $entity->getLastEditor();
					if (empty($last_editor)) {
						continue;
					}
					
					// make sure we need to notify this user
					$recipient = self::checkRecipient($entity, $last_editor);
					if (!($recipient instanceof \ElggUser)) {
						continue;
					}
					
					if (!isset($users[$recipient->getGUID()])) {
						$users[$recipient->getGUID()] = [
							'reminders' => [],
						];
					}
					
					$users[$recipient->getGUID()]['reminders'][$i][] = [
						'title' => $entity->getDisplayName(),
						'url' => $entity->getURL(),
					];
				}
			}
		}
		
		// restore access
		elgg_set_ignore_access($ia);
		
		if (empty($users)) {
			return;
		}
		
		self::sendNotifications($users);
	}
	
	/**
	 * Let others infuence the recipient of the out-of-date notification
	 *
	 * @param \StaticPage $entity    for which page
	 * @param \ElggUser   $recipient default recipient
	 *
	 * @return false|\ElggUser
	 */
	protected static function checkRecipient(\StaticPage $entity, \ElggUser $recipient) {
		
		if (!($entity instanceof \StaticPage) || !($recipient instanceof \ElggUser)) {
			return false;
		}
		
		$params = [
			'entity' => $entity,
			'recipient' => $recipient,
		];
		
		$notify_user = elgg_trigger_plugin_hook('out_of_date:user', 'static', $params, $recipient);
		if (!($notify_user instanceof \ElggUser)) {
			return false;
		}
		
		return $notify_user;
	}
	
	/**
	 * Send out the notifications
	 *
	 * @param array $notification_information recipient information
	 *
	 * @return void
	 */
	protected static function sendNotifications($notification_information) {
		
		if (empty($notification_information) || !is_array($notification_information)) {
			return;
		}
		
		$site = elgg_get_site_entity();
		foreach ($notification_information as $user_guid => $info) {
			
			// get recipient
			$user = get_user($user_guid);
			if (empty($user)) {
				continue;
			}
			
			// build a list of all out-of-date pages
			$list = '';
			
			$new = (array) elgg_extract('new', $info, []);
			if (!empty($new)) {
				// add a header
				$list .= elgg_echo('static:out_of_date:notification:section:new') . PHP_EOL;
				
				// list all pages
				foreach ($new as $page_info) {
					$list .= '- ' . $page_info['title'] . ' (' . $page_info['url'] . ')' . PHP_EOL;
				}
			}
			
			// add reminder intervals
			$remiders = (array) elgg_extract('reminders', $info, []);
			if (!empty($remiders)) {
				
				foreach ($remiders as $reminder => $pages) {
					
					if (empty($pages)) {
						continue;
					}
					
					$list .= PHP_EOL;
					
					// section header
					if (elgg_language_key_exists("static:out_of_date:notification:section:reminder:{$reminder}")) {
						// custom header
						$list .= elgg_echo("static:out_of_date:notification:section:reminder:{$reminder}") . PHP_EOL;
					} else {
						// default header
						$list .= elgg_echo('static:out_of_date:notification:section:reminder', [$reminder]) . PHP_EOL;
					}
					
					// list all pages
					foreach ($pages as $page_info) {
						$list .= '- ' . $page_info['title'] . ' (' . $page_info['url'] . ')' . PHP_EOL;
					}
				}
			}
			
			if (empty($list)) {
				// shouldn't happen
				continue;
			}
			
			// build notification
			$subject = elgg_echo('static:out_of_date:notification:subject');
			$message = elgg_echo('static:out_of_date:notification:message', [
				$user->getDisplayName(),
				$list,
				elgg_normalize_url("static/out_of_date/{$user->username}"),
			]);
			
			// send notification
			notify_user($user->getGUID(), $site->getGUID(), $subject, $message, [], 'email');
		}
	}
}
 
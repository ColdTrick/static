<?php

namespace ColdTrick\StaticPages;

use Elgg\Database\Clauses\OrderByClause;

/**
 * Cron
 */
class Cron {

	/**
	 * Notify users about out-of-date content
	 *
	 * @param \Elgg\Event $event 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function outOfDateNotification(\Elgg\Event $event): void {
		
		if (!static_out_of_date_enabled()) {
			return;
		}
				
		$time = $event->getParam('time', time());
		$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
		
		$compare_ts = $time - ($days * 24 * 60 * 60);
		
		$users = elgg_call(ELGG_IGNORE_ACCESS, function() use ($compare_ts) {
			$users = [];
			
			$options = [
				'type' => 'object',
				'subtype' => \StaticPage::SUBTYPE,
				'limit' => false,
				'modified_time_upper' => $compare_ts,
				'modified_time_lower' => $compare_ts - (24 * 60 * 60),
				'order_by' => new OrderByClause('e.time_updated', 'DESC'),
				'batch' => true,
			];
			
			$batch = elgg_get_entities($options);
			/* @var $entity \StaticPage */
			foreach ($batch as $entity) {
				// allows an event to influence entities being notified
				if (!$entity->isOutOfDate()) {
					continue;
				}
				
				$last_editor = $entity->getLastEditor();
				if (empty($last_editor)) {
					continue;
				}
				
				// make sure we need to notify this user
				$recipient = self::checkRecipient($entity, $last_editor);
				if (!$recipient instanceof \ElggUser) {
					continue;
				}
				
				if (!isset($users[$recipient->guid])) {
					$users[$recipient->guid] = [
						'new' => [],
					];
				}
				
				$users[$recipient->guid]['new'][] = [
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
					
					$batch = elgg_get_entities($options);
					/* @var $entity \StaticPage */
					foreach ($batch as $entity) {
						// allows an event to influence entities being notified
						if (!$entity->isOutOfDate()) {
							continue;
						}
						
						$last_editor = $entity->getLastEditor();
						if (empty($last_editor)) {
							continue;
						}
						
						// make sure we need to notify this user
						$recipient = self::checkRecipient($entity, $last_editor);
						if (!$recipient instanceof \ElggUser) {
							continue;
						}
						
						if (!isset($users[$recipient->guid])) {
							$users[$recipient->guid] = [
								'reminders' => [],
							];
						}
						
						$users[$recipient->guid]['reminders'][$i][] = [
							'title' => $entity->getDisplayName(),
							'url' => $entity->getURL(),
						];
					}
				}
			}
			
			return $users;
		});
		
		if (empty($users)) {
			return;
		}
		
		self::sendNotifications($users);
	}
	
	/**
	 * Let others influence the recipient of the out-of-date notification
	 *
	 * @param \StaticPage $entity    for which page
	 * @param \ElggUser   $recipient default recipient
	 *
	 * @return null|\ElggUser
	 */
	protected static function checkRecipient(\StaticPage $entity, \ElggUser $recipient): ?\ElggUser {
	
		$params = [
			'entity' => $entity,
			'recipient' => $recipient,
		];
		
		$notify_user = elgg_trigger_event_results('out_of_date:user', 'static', $params, $recipient);
		if (!$notify_user instanceof \ElggUser) {
			return null;
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
	protected static function sendNotifications(array $notification_information): void {
		$site = elgg_get_site_entity();
		foreach ($notification_information as $user_guid => $info) {
			// get recipient
			$user = get_user($user_guid);
			if (empty($user)) {
				continue;
			}
			
			$recipient_language = $user->getLanguage();
			
			// build a list of all out-of-date pages
			$list = '';
			
			$new = (array) elgg_extract('new', $info, []);
			if (!empty($new)) {
				// add a header
				$list .= elgg_echo('static:out_of_date:notification:section:new', [], $recipient_language) . PHP_EOL;
				
				// list all pages
				foreach ($new as $page_info) {
					$list .= '- ' . $page_info['title'] . ' (' . $page_info['url'] . ')' . PHP_EOL;
				}
			}
			
			// add reminder intervals
			$reminders = (array) elgg_extract('reminders', $info, []);
			foreach ($reminders as $reminder => $pages) {
				if (empty($pages)) {
					continue;
				}
				
				$list .= PHP_EOL;
				
				// section header
				if (elgg_language_key_exists("static:out_of_date:notification:section:reminder:{$reminder}")) {
					// custom header
					$list .= elgg_echo("static:out_of_date:notification:section:reminder:{$reminder}", [], $recipient_language) . PHP_EOL;
				} else {
					// default header
					$list .= elgg_echo('static:out_of_date:notification:section:reminder', [$reminder], $recipient_language) . PHP_EOL;
				}
				
				// list all pages
				foreach ($pages as $page_info) {
					$list .= '- ' . $page_info['title'] . ' (' . $page_info['url'] . ')' . PHP_EOL;
				}
			}
			
			if (empty($list)) {
				// shouldn't happen
				continue;
			}
			
			// build notification
			$subject = elgg_echo('static:out_of_date:notification:subject', [], $recipient_language);
			$message = elgg_echo('static:out_of_date:notification:message', [
				$list,
				elgg_generate_url('collection:object:static:user:out_of_date', [
					'username' => $user->username,
				]),
			], $recipient_language);
			
			notify_user($user->guid, $site->guid, $subject, $message, [], 'email');
		}
	}
}

<?php

namespace ColdTrick\StaticPages\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Send an out of date notification
 */
class OutOfDateHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('static:out_of_date:notification:subject');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$info = (array) $this->getParam('info', []);

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
		$reminders = (array) elgg_extract('reminders', $info, []);
		foreach ($reminders as $reminder => $pages) {
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

		return elgg_echo('static:out_of_date:notification:message', [
			$list,
			elgg_generate_url('collection:object:static:user:out_of_date', [
				'username' => $recipient->username,
			]),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
}

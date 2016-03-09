<?php

namespace ColdTrick\StaticPages;

/**
 * Cron
 */
class Cron {

	/**
	 * Add menu items to the filter menu
	 *
	 * @param string $hook         'cron'
	 * @param string $type         'daily'
	 * @param string $return_value optional output
	 * @param array  $params       supplied params
	 *
	 * @return void
	 */
	public static function daily($hook, $type, $return_value, $params) {
	
		if (!static_out_of_date_enabled()) {
			return;
		}
	
		$time = elgg_extract('time', $params, time());
		$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
		$site = elgg_get_site_entity();
	
		$options = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'limit' => false,
			'modified_time_upper' => $time - ($days * 24 * 60 * 60),
			'modified_time_lower' => $time - (($days + 1) * 24 * 60 * 60),
			'order_by' => 'e.time_updated DESC',
		];
	
		// ignore access
		$ia = elgg_set_ignore_access(true);
	
		$batch = new ElggBatch('elgg_get_entities', $options);
		$users = [];
		foreach ($batch as $entity) {
			$last_editors = $entity->getAnnotations([
				'annotation_name' => 'static_revision',
				'limit' => 1,
				'order_by' => 'n_table.time_created DESC',
			]);
	
			if (empty($last_editors)) {
				continue;
			}
	
			$users[$last_editors[0]->getOwnerGUID()] = $last_editors[0]->getOwnerEntity();
		}
	
		// restore access
		elgg_set_ignore_access($ia);
	
		if (empty($users)) {
			return;
		}
	
		foreach ($users as $user) {
			$subject = elgg_echo('static:out_of_date:notification:subject');
			$message = elgg_echo('static:out_of_date:notification:message', [
				$user->name,
				elgg_normalize_url('static/out_of_date/' . $user->username),
			]);
	
			notify_user($user->getGUID(), $site->getGUID(), $subject, $message, [], 'email');
		}
	}
}
<?php

namespace ColdTrick\StaticPages;

class CSVExporter {
	
	/**
	 * Add last editor information to static export
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param arary  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function addLastEditor($hook, $type, $return_value, $params) {
		
		if (elgg_extract('subtype', $params) !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:last_editor:guid') => 'static_last_editor_guid',
			elgg_echo('static:csv_exporter:last_editor:name') => 'static_last_editor_name',
			elgg_echo('static:csv_exporter:last_editor:username') => 'static_last_editor_username',
			elgg_echo('static:csv_exporter:last_editor:email') => 'static_last_editor_email',
			elgg_echo('static:csv_exporter:last_editor:profile_url') => 'static_last_editor_profile_url',
		];
		
		if (!(bool) elgg_extract('readable', $params)) {
			$values = array_values($values);
		}
		
		return array_merge($return_value, $values);
	}
	
	/**
	 * Export last editor information
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param arary  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @retrun void|string
	 */
	public static function exportLastEditor($hook, $type, $return_value, $params) {
		
		if (!is_null($return_value)) {
			// someone already provided output
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (!($entity instanceof \StaticPage)) {
			return;
		}
		
		$last_editor = $entity->getLastEditor();
		if (empty($last_editor)) {
			return;
		}
		
		switch (elgg_extract('exportable_value', $params)) {
			case 'static_last_editor_guid':
				return $last_editor->getGUID();
				break;
			case 'static_last_editor_name':
				return $last_editor->getDisplayName();
				break;
			case 'static_last_editor_username':
				return $last_editor->username;
				break;
			case 'static_last_editor_email':
				return $last_editor->email;
				break;
			case 'static_last_editor_profile_url':
				return $last_editor->getURL();
				break;
		}
	}
	
	/**
	 * Add last revision information to static export
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param arary  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function addLastRevision($hook, $type, $return_value, $params) {
		
		if (elgg_extract('subtype', $params) !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:last_revision:timestamp') => 'static_last_revision_timestamp',
			elgg_echo('static:csv_exporter:last_revision:timestamp:readable') => 'static_last_revision_timestamp_readable',
		];
		
		if (!(bool) elgg_extract('readable', $params)) {
			$values = array_values($values);
		}
		
		return array_merge($return_value, $values);
	}
	
	/**
	 * Export last revison information
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param arary  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @retrun void|string
	 */
	public static function exportLastRevision($hook, $type, $return_value, $params) {
		
		if (!is_null($return_value)) {
			// someone already provided output
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (!($entity instanceof \StaticPage)) {
			return;
		}
		
		$last_revision = $entity->getLastRevision();
		if (empty($last_revision)) {
			return;
		}
		
		switch (elgg_extract('exportable_value', $params)) {
			case 'static_last_revision_timestamp':
				return $last_revision->time_created;
				break;
			case 'static_last_revision_timestamp_readable':
				return csv_exported_get_readable_timestamp($last_revision->time_created);
				break;
		}
	}
}

import 'jquery';
import Ajax from 'elgg/Ajax';

var ajax = new Ajax();

$(document).on('change', '.static-edit-top-parent-select', function() {
	var selected_guid = $(this).val();
	var $selector = $(this);

	if (selected_guid == 0) {
		$selector.next('select').remove();
	} else {
		ajax.view('static/ajax/menu_static_edit', {
			data: {
				guid: selected_guid,
				root_entity_guid: $selector.data().rootEntityGuid
			},
			success: function(data) {
				$selector.next('select').remove();
				$selector.after(data);
			}
		});
	}
});

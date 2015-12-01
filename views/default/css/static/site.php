<?php
// css fix for collapsed page menu items

$icon_sizes = elgg_get_config('icon_sizes');
$large = elgg_extract('large', $icon_sizes);
if (!empty($large)) {
	
	?>
	.static-thumbnail-large img {
		width: auto;
		height: auto;
		max-width: <?php echo (int) elgg_extract('w', $large, 200); ?>px;
		max-height: <?php echo (int) elgg_extract('h', $large, 200); ?>px;
	}
	<?php
}

?>
.elgg-menu-page-static .elgg-state-selected > .elgg-child-menu {
	display: block;
}

.elgg-menu-page-static .elgg-menu-opened:before {
	content: "▾";
}

.elgg-menu-page-static .elgg-menu-closed:before {
	content: "▸";
}

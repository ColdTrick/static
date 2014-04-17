<?php
// css fix for collapsed page menu items
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

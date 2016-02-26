<?php

// set class handler to default Elgg handling
if (get_subtype_id('object', \StaticPage::SUBTYPE)) {
	update_subtype('object', \StaticPage::SUBTYPE);
}

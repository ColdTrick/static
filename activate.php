<?php

// set correct class handler for static pages
if (get_subtype_id('object', \StaticPage::SUBTYPE)) {
	update_subtype('object', \StaticPage::SUBTYPE, 'StaticPage');
} else {
	add_subtype('object', \StaticPage::SUBTYPE, 'StaticPage');
}

<?php

$plugin = elgg_extract("entity", $vars);

$noyes_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

echo "<div>";
echo elgg_echo("static:settings:enable_out_of_date");
echo elgg_view("input/select", array(
	"name" => "params[enable_out_of_date]",
	"options_values" => $noyes_options,
	"value" => $plugin->enable_out_of_date,
	"class" => "mls"
));
echo "</div>";

echo "<div>";
echo elgg_echo("static:settings:out_of_date_days");
echo elgg_view("input/text", array(
	"name" => "params[out_of_date_days]",
	"value" => (int) $plugin->out_of_date_days,
	"size" => "4",
	"class" => "mls",
	"style" => "width:inherit;"
));
echo elgg_echo("static:settings:out_of_date_days:days");
echo "</div>";

echo "<div>";
echo elgg_echo("static:settings:enable_groups");
echo elgg_view("input/dropdown", array(
	"name" => "params[enable_groups]",
	"options_values" => $noyes_options,
	"value" => $plugin->enable_groups,
	"class" => "mls"
));
echo "</div>";

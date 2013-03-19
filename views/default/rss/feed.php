<?php
/**
 * Elgg RSS feed view
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 * @uses $vars['sources']
 * @uses $vars['max']     Optional: Max number of entries to display
 * @uses $vars['title']   Optional: Title to display
 */

$max = elgg_extract('max', $vars, -1);

$sources = elgg_extract('sources', $vars);

$title = elgg_extract('title', $vars, NULL);


if ($title) {
	$img = elgg_view('output/img', array(
		'src' => 'mod/rss/graphics/feed-icon-14x14.png',
	));

	$title_content = "<div class='elgg-rss-feed-title'>$img $title</div>";
}

foreach ($sources as $name => $url) {
	$feed_sources .= elgg_view('input/hidden', array(
		'name' => $name,
		'value' => $url,
		'class' => '_rss-feed-source'
	));
}

$max_input = elgg_view('input/hidden', array(
	'name' => 'max',
	'value' => $max,
));

$content = <<<HTML
	$title_content
	<div class='elgg-rss-feed'>
		$feed_sources
		$max_input
	</div>
HTML;

echo $content;
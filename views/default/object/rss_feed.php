<?php
/**
 * Elgg RSS Feed Object View
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

$full = elgg_extract('full_view', $vars, FALSE);
$rss = elgg_extract('entity', $vars, FALSE);

if (!$rss) {
	return TRUE;
}

$owner = $rss->getOwnerEntity();
$container = $rss->getContainerEntity();

//$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array(
	'href' => "rss/owner/$owner->username",
	'text' => $owner->name,
));

$author_text = elgg_echo('rss:byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $rss->tags));
$date = elgg_view_friendly_time($rss->time_created);

$comments_count = $rss->countComments();
//only display if there are commments
if ($comments_count != 0) {
	$text = elgg_echo("comments") . " ($comments_count)";
	$comments_link = elgg_view('output/url', array(
		'href' => $rss->getURL() . '#rss-comments',
		'text' => $text,
	));
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'rss',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "<p>$author_text $date $comments_link</p>";
$subtitle .= $categories;

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}


$rss_icon = elgg_view('output/url', array(
	'href' => $rss->getURL(),
	'text' => elgg_view('output/img', array(
		'src' => 'mod/rss/graphics/feed-icon-28x28.png',
	))
));

if ($full) {
	$body = elgg_view('output/longtext', array(
		'value' => $rss->description,
	));

	$feed = elgg_view('rss/feed', array(
		'sources' => array(
			$rss->title => $rss->feed_url,
		),
	));	

	$params = array(
		'entity' => $rss,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'tags' => $tags,
	);
	$params = $params + $vars;

	$list_body = elgg_view('object/elements/summary', $params);

	$rss_info = elgg_view_image_block($rss_icon, $list_body);

	echo <<<HTML
	$rss_info
	$body
	<br />
	$feed
HTML;

} else {
	$excerpt = elgg_get_excerpt($rss->description);

	$params = array(
		'entity' => $rss,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);

	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($rss_icon, $list_body);
}

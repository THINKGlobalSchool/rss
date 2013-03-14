<?php
/**
 * Elgg RSS Javascript Library
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */
?>
//<script>
elgg.provide('elgg.rss');

// Init function
elgg.rss.init = function() {
	// Init feeds
	elgg.rss.initFeeds();

 	// Delegate submit handler for rss feed form
 	$(document).delegate('#rss-save-form', 'submit', elgg.rss.saveFormSubmit);

 	// Delegate click handler for rss embed
 	$(document).delegate('.elgg-rss-embed-feed', 'click', elgg.rss.feedEmbedClick);
}

// Init all feeds
elgg.rss.initFeeds = function() {
	$('.elgg-rss-feed').each(function() {
 		elgg.rss.initFeed($(this));
 	});
}

elgg.rss.initFeed = function($feed) {
		var feeds = {};
		$feed.children('input._rss-feed-source').each(function() {
			feeds[$(this).attr('name')] = $(this).val();
		});

		$feed.feeds({
			'feeds': feeds, // Feeds object (can be multiple)
			'entryTemplate': elgg.rss.getDefaultEntryTemplate(),
			'loadingTemplate': '<div class="elgg-ajax-loader"></div>',
			'onComplete': function(entries) {
				if (!entries.length) {
					$(this).append("<h3 class='center'>" + elgg.echo('rss:label:noresults') + "</h3>");
				}
			}
			// 'preprocess': function(feed) {}
		});
}

elgg.rss.getDefaultEntryTemplate = function(entry) {
	return '<div class="elgg-rss-feed-entry elgg-rss-feed-source-<!=source!>">' + 
				'<a class="elgg-rss-feed-entry-title" target="_blank" href="<!=link!>" title="<!=title!>"><!=title!></a>' +
				'<div class="elgg-rss-feed-entry-date elgg-subtext"><!=publishedDate!></div>' + 
				'<div class="elgg-rss-feed-entry-content-excerpt"><!=contentSnippet!></div>' + 
			'</div>';
}

// RSS Feed save form submit handler
elgg.rss.saveFormSubmit = function(event) {
	$(this).find('input[name=rss_save_input]').attr('disabled', 'DISABLED');

	if (!$(this).data('valid_feed')) {
		// Stop submit
		event.preventDefault();

		var $_this = $(this);

		var feed_url = $(this).find('input[name=feed_url]').val();

		elgg.action('rss/validate', {
			data: {
				feed_url: feed_url
			}, 
			success: function(result) {
				if (result.status == 0) {
					$_this.data('valid_feed', 1);
					$_this.trigger('submit');
				} else {
					$_this.find('input[name=rss_save_input]').removeAttr('disabled');
				}
			}
		});
	}
}

// Click handler for rss feed embed click
elgg.rss.feedEmbedClick = function(event) {
	if (!$(this).hasClass('disabled')) {
		// href will be #{guid}
		var entity_guid = $(this).attr('href').substring(1);

		$(this).addClass('disabled');

		$_this = $(this);

		console.log($_this);

		// Get embed
		elgg.get('ajax/view/rss/embeddable', {
			dataType: 'html',
			data: {
				entity_guid: entity_guid,
			}, 
			success: function(data) {	
				if (data.status != -1) {
					console.log(data);
					elgg.tgsembed.insert(data);
				} else {
					// Error
					$_this.removeClass('disabled');
				}
			},
		});
	}
	event.preventDefault();
}

elgg.register_hook_handler('init', 'system', elgg.rss.init);
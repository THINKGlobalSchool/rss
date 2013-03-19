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

 	$(document).delegate('#rss-url', 'input propertychange', elgg.rss.feedPreview);
}

// Init all feeds
elgg.rss.initFeeds = function() {
	$('.elgg-rss-feed').each(function() {
 		elgg.rss.initFeedFromInput($(this));
 	});
}

// Init feeds on container from feed source inputs
elgg.rss.initFeedFromInput = function($feed) {
		var feeds = {};
		$feed.children('input._rss-feed-source').each(function() {
			feeds[$(this).attr('name')] = $(this).val();
		});

		var max = $feed.children('input[name="max"]').val();

		$feed.feeds(elgg.rss.getFeedInitOptions(feeds, max));
}

// Get common feed init options
elgg.rss.getFeedInitOptions = function(feeds, max) {
	// Default max value is -1 (load upto 100 entries from api)
	max = typeof max !== 'undefined' ? max : -1;

	var feed_init = {
		'feeds': feeds, // Feeds object (can be multiple)
		'entryTemplate': elgg.rss.getDefaultEntryTemplate(),
		'loadingTemplate': '<div class="elgg-ajax-loader"></div>',
		'xml': true,
		'max': max,
		'onComplete': function(entries) {
			if (!entries.length) {
				$(this).append("<h3 class='center'>" + elgg.echo('rss:label:noresults') + "</h3>");
			}
		},
		'preprocess': function(feed) {
			// Hack to fix broken feeds.. these seem to only be a handful
			if (this.contentSnippet.indexOf("<!--") !== -1 || this.contentSnippet.indexOf("&lt;") !== -1) {
				var div = document.createElement("div");

				// Find description in XML
				var descriptionText = this.xml.find('description').text();

				if (descriptionText.indexOf("More&#160;&#187;") !== -1) {
					descriptionText = descriptionText.replace("More&#160;&#187;", "");
				}

				// Strip out 'more'

				div.innerHTML = descriptionText;
				var text = div.textContent || div.innerText || "";

				text = text.substring(0, 120) + ' ...';

				this.contentSnippet = text;
			}
		}
	}

	return feed_init;
}

elgg.rss.getDefaultEntryTemplate = function(entry) {
	return '<div class="elgg-rss-feed-entry elgg-rss-feed-source-<!=source!>">' + 
				'<a class="elgg-rss-feed-entry-title" target="_blank" href="<!=link!>" title="<!=title!>"><!=title!></a>' +
				'<div class="elgg-rss-feed-entry-date elgg-subtext"><!=publishedDate!></div>' + 
				'<div class="elgg-rss-feed-entry-content-excerpt"><!=contentSnippet!>' +
				'&nbsp;<a target="_blank" href="<!=link!>" class="elgg-rss-feed-entry-read-article">' + elgg.echo('rss:label:readarticle') + '</a>' + 
				'</div>' + 
			'</div>';
}

// RSS Feed save form submit handler
elgg.rss.saveFormSubmit = function(event) {
	var $save_input = $(this).find('input[name=rss_save_input]');
	$save_input.attr('disabled', 'DISABLED');

	if (!$('#rss-url').data('valid_feed')) {		
		event.preventDefault();

		var feed_url = $(this).find('#rss-url').val();

		var $_this = $(this);

		elgg.action('rss/validate', {
			data: {
				feed_url: feed_url
			}, 
			success: function(result) {
				if (result.status == 0) {
					$('#rss-url').data('valid_feed', 1);
					$('input[name="feed_link"]').val(result.output.feed_link);
					$_this.trigger('submit');
				} else {
					$save_input.removeAttr('disabled');
				}
			}
		});
	}
}

// Change handler for rss feed url input
elgg.rss.feedPreview = function(event) {
	var feed_url = $(this).val();

	var $_this = $(this);

	var $feed_preview_container = $_this.siblings('#rss-feed-preview');
	$feed_preview_container.html('').removeClass('elgg-rss-feed');
	$('input[name="feed_link"]').val('');

	$feed_preview_container.addClass('elgg-ajax-loader');

	elgg.action('rss/validate', {
		data: {
			feed_url: feed_url
		}, 
		success: function(result) {
			if (result.status == 0) {
				$_this.data('valid_feed', 1);
				var feeds = {};
				feeds['preview'] = feed_url;
				$feed_preview_container.addClass('elgg-rss-feed').feeds(elgg.rss.getFeedInitOptions(feeds, 4));
				$('input[name="feed_link"]').val(result.output.feed_link);
			} else {
				// Invalid
				$_this.data('valid_feed', 0);
			}
			$feed_preview_container.removeClass('elgg-ajax-loader');
		}
	});
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
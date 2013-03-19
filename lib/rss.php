<?php
/**
 * Elgg RSS Helper Library
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

/**
 * Get rss listing content 
 */
function rss_get_page_content_list($container_guid = null) {
	$logged_in_user_guid = elgg_get_logged_in_user_guid();
	
	$options = array(
		'type' => 'object', 
		'subtype' => 'rss_feed', 
		'full_view' => false, 
	);
	
	if ($container_guid) {
		$options['container_guid'] = $container_guid;
		$entity = get_entity($container_guid);
		elgg_push_breadcrumb($entity->name);
	
		if (elgg_instanceof($entity, 'group')) {
			$params['filter'] = false;
			elgg_register_title_button();
		} else if ($container_guid == $logged_in_user_guid) {
			$params['filter_context'] = 'mine';
			elgg_register_title_button();
		} else {
			// do not show button or select a tab when viewing someone else's posts
			$params['filter_context'] = 'none';
		}
		
		$content = elgg_list_entities($options);
		$params['title'] = elgg_echo('rss:title:owned', array($entity->name));
			
	} else {
		elgg_register_title_button();
		$content = elgg_list_entities($options);
		$params['title'] = elgg_echo('rss');
		$params['filter_context'] = 'all';
	}
	
	// If theres no content, display a nice message
	if (!$content) {
		$content = "<h3 class='center'>" . elgg_echo("rss:label:noresults") . "</h3>";
	}
		
	$params['content'] = $content;
	return $params;
}

/**
 * Get friends rss feeds
 */
function rss_get_page_content_friends($user_guid) {
	$user = get_user($user_guid);
	
	$params['filter_context'] = 'friends';
	$params['title'] = elgg_echo('rss:title:friends');

	elgg_push_breadcrumb($user->name, elgg_get_site_url() . 'rss/owner/' . $user->username);
	elgg_push_breadcrumb(elgg_echo('friends'));

	if (!$friends = get_user_friends($user_guid, ELGG_ENTITIES_ANY_VALUE, 0)) {
		$content .= elgg_echo('friends:none:you');
	} else {
		$options = array(
			'type' => 'object',
			'subtype' => 'rss_feed',
			'full_view' => FALSE,
		);

		foreach ($friends as $friend) {
			$options['container_guids'][] = $friend->getGUID();
		}
		
		$list = elgg_list_entities($options);
		if (!$list) {
			$content .= "<h3 class='center'>" . elgg_echo("rss:label:noresults") . "</h3>";
		} else {
			$content .= $list;
		}
	}
	elgg_register_title_button();
	
	$params['content'] = $content;
	
	return $params;
}

/**
 * Build content for editing/creating a rss feed
 */
function rss_get_page_content_edit($page, $guid) { 
	$params['filter'] = FALSE;
	
	// General form vars
	$form_vars = array(
		'id' => 'rss-save-form', 
		'name' => 'rss-save-form'
	);
		
	if ($page == 'edit') {
		$rss = get_entity($guid);
		
		$params['title'] = elgg_echo('rss:title:edit');
		
		if (elgg_instanceof($rss, 'object', 'rss_feed') && $rss->canEdit()) {
			$owner = get_entity($rss->container_guid);
			
			elgg_set_page_owner_guid($owner->getGUID());
			
			elgg_push_breadcrumb($rss->title, $rss->getURL());
			elgg_push_breadcrumb('edit');

			$body_vars = rss_prepare_form_vars($rss);

			$params['content'] .= elgg_view_form('rss/save', $form_vars, $body_vars);
		} else {
			register_error(elgg_echo('rss:error:notfound'));
			forward(REFERER);
		}
	} else {
		if (!$guid) {
			$container = elgg_get_logged_in_user_entity();
		} else {
			$container = get_entity($guid);
		}

		$params['title'] = elgg_echo('rss:title:add');

		elgg_push_breadcrumb($params['title']);

		$body_vars = rss_prepare_form_vars();

		$params['content'] .= elgg_view_form('rss/save', $form_vars, $body_vars);
	}	
	return $params;
}

/**
 * View a rss feed
 */
function rss_get_page_content_view($guid) {
	$rss = get_entity($guid);
	$container = get_entity($rss->container_guid);
	
	if (!elgg_instanceof($rss, 'object', 'rss_feed')) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}
	
	elgg_set_page_owner_guid($container->getGUID());
	elgg_push_breadcrumb($container->name, elgg_get_site_url() . 'rss/owner/' . $container->username);
	elgg_push_breadcrumb($rss->title, $rss->getURL());
	$params['title'] = $rss->title;
	$params['content'] .= elgg_view_entity($rss, array('full_view' => TRUE));	
	$params['content'] .= "<a name='comments'></a>" . elgg_view_comments($rss);
	$params['filter'] = ' ';
	return $params;
}

/**
 * Prepare form vars for rss save form
 *
 * @param ElggObject $rss
 * @return array
 */
function rss_prepare_form_vars($rss = NULL) {
	// input names => defaults
	$values = array(
		'tags' => NULL,
		'access_id' => ACCESS_DEFAULT,
		'feed_url' => NULL,
		'feed_link' => NULL,
		'container_guid' => elgg_get_page_owner_guid(),
		'guid' => NULL,
		'title' => NULL,
		'description' => NULL,
		'entity' => $rss,
	);
	
	if ($rss) {
		foreach (array_keys($values) as $field) {
			if (isset($rss->$field)) {
				$values[$field] = $rss->$field;
			}
		}
	}

	if (elgg_is_sticky_form('rss-save-form')) {
		$sticky_values = elgg_get_sticky_values('rss-save-form');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('rss-save-form');

	return $values;
}
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
elgg.rss.init = function () {
	//
}

elgg.register_hook_handler('init', 'system', elgg.rss.init);
<?php
// name of my parent folder
define('RELATED_ARTICLES_BY_TAG_PARENT_DIR', basename(dirname(__FILE__)));

load_plugin_textdomain('related-articles-by-tag', false, 'related-articles-by-tag/languages');

/**
 * Inform user of the minimum PHP version requird for Related Posts by Tag.
 */
function _related_articles_by_tag_min_version_notice() {
	echo "<div class='updated' style='background-color:#f99;'><p><strong>WARNING:</strong> " + __('Related Posts by Tag plugin requires PHP 5 or above to work', 'related-articles-by-tag') + "</p></div>";
}

// need atleast PHP 5
if (5 > intval(phpversion())) {
	add_action('admin_notices', '_related_articles_by_tag_min_version_notice');
} else {
	// if we're at version 3 or above then we can keep it simple using WP hooks:
	global $wp_version;
	if (3 <= substr($wp_version, 0, 1)) {
		// Based on code by Bjorn Wijers at https://github.com/BjornW/tag-pages

		/**
		 * Add the 'post_tag' taxonomy, which is the name of the existing taxonomy
		 * used for tags to the Post type page. Normally in WordPress Pages cannot
		 * be tagged, but this let's WordPress treat Pages just like Posts
		 * and enables the tags metabox so you can add tags to a Page.
		 * NB: This uses the register_taxonomy_for_object_type() function which is only
		 * in WordPress 3 and higher!
		 */
		if (!function_exists('related_articles_by_tag_register_taxonomy')) {
			function related_articles_by_tag_register_taxonomy() {
				register_taxonomy_for_object_type('post_tag', 'page');
			}

			add_action('admin_init', 'related_articles_by_tag_register_taxonomy');
		}

		/**
		 * Display all post_types on the tags archive page. This forces WordPress to
		 * show tagged Pages together with tagged Posts.
		 */
		if (!function_exists('related_articles_by_tag_display_tagged_pages_archive')) {
			function related_articles_by_tag_display_tagged_pages_archive(&$query) {
				if ($query -> is_archive && $query -> is_tag) {
					$q = &$query -> query_vars;
					$q['post_type'] = 'any';
				}
			}

			add_action('pre_get_posts', 'related_articles_by_tag_display_tagged_pages_archive');
		}
	}
	// if we're before version 3
	else {
		require_once ('related-articles-by-tag-class.php');
		add_action('plugins_loaded', array('RelatedPostsByTag', 'init'));
	}
}

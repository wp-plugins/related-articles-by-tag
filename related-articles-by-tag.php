<?php
/*
Related Articles by Tag Lite wordpress plugin
Copyright (C) 2013 Cristian Merli

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/*
 Plugin Name: Related Articles by Tag Lite
 Plugin URI: http://wppluginspool.com/downloads/related-articles-by-tag/
 Description: If you use tags, at the end of your posts it will show a list of links to the other posts that have the same tag(s). This can happen automatically for each post or only where you add the shortcode [related-articles-by-tag].
 Version: 1.0.0
 Author: Cristian Merli
 Author URI: http://wppluginspool.com
 */
 
 require_once ('page-tagger.php');

 //------------------ SHORTCODE -----------------//
add_shortcode('related-articles-by-tag', 'related_articles_func');

function related_articles_func($atts) {
	global $post;
	$post_count = 0;
	
	$output = '';
	
	if(get_option('related_articles_by_tag_mode') == 'shortcode') {
		$output = get_related_articles($post->ID, $post_count);
		
		if($post_count > 0)
			$output = '<'.get_option('related_articles_by_tag_title_type', 'h2').'>'.get_option('related_articles_by_tag_title', 'Related Articles').'</'.get_option('related_articles_by_tag_title_type', 'h2').'>' . $output;
	}
	/*
	 * else
	 * 	$output is already an empty string
	*/
	
	return $output;
}

/**
 * return related articles code
 */
function get_related_articles($post_id, &$post_count){
	global $wp_query;
	$temp_query = $wp_query;
	
	$output = '';
	$tag_names = array();

	$tags = wp_get_post_tags($post_id);

	if (count($tags) > 0) {
		foreach ($tags as $t) {
			$tag_names[] = $t -> slug;
		}
		$tag_names_str = implode(',', $tag_names);
		
		$args = array();

		$args['tag'] =  $tag_names_str;
		$args['post_status'] = 'publish';
		$args['posts_per_page'] = 3;
		$args['orderby'] = 'menu_order';
		$args['order'] = 'ASC';

		query_posts($args);

		if (have_posts()) {
			
			$post_count = 0;
			
			$output .= '<ul>';
			
			while (have_posts()) : the_post();
				if( 
					((get_option('related_articles_by_tag_apply', 'posts') == 'posts') && (get_post_type(get_the_ID()) == 'post')) || 
					((get_option('related_articles_by_tag_apply', 'posts') == 'pages') && (get_post_type(get_the_ID()) == 'page')) ||
					(get_option('related_articles_by_tag_apply', 'posts') == 'posts_pages')) {
					if (get_the_ID() != $post_id) {
						$output .= '<li>';
						$output .= '<a href="'.get_permalink().'">'.get_the_title()."</a>";
						$output .= '</li>';
						$post_count++;
					}
				}
			endwhile;
			$output .= '</ul>';
		}

		wp_reset_query();
		$wp_query = $temp_query;
	}

	return $output;
}


//------------------ WIDGET -----------------//
require_once ('related-articles-by-tag-widget.php');

add_action( 'widgets_init', 'related_articles_by_tag_widget');

function related_articles_by_tag_widget() {
     register_widget( 'RelatedPostsByTag_Widget' );
}

//------------------ SETTINGS PAGE -----------------//
// create custom plugin settings menu
add_action('admin_menu', 'create_related_articles_by_tag_menu');

function create_related_articles_by_tag_menu() {

    //create new options page
    add_options_page('Related Articles by Tag', 'Related Articles by Tag', 'manage_options', 'related_articles_by_tag_settings', 'related_articles_by_tag_settings_page');
    
    //call register settings function
    add_action('admin_init', 'register_related_articles_by_tag_settings');
}


function register_related_articles_by_tag_settings() {
    //register our settings
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_title');
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_title_type');
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_apply');
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_mode');
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_order');
    register_setting('related-articles-by-tag-settings-group', 'related_articles_by_tag_apply');
}

function related_articles_by_tag_settings_page() {
?>
<div class="wrap">
	<h2>Related Articles by Tag Settings</h2>
	<form method="post" action="options.php">
		<?php settings_fields('related-articles-by-tag-settings-group'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"> <?php echo __('Related Articles Title'); ?>
				</th>
				<td>
				<input name="related_articles_by_tag_title" type="text" id="related_articles_by_tag_title" value="<?php echo(get_option('related_articles_by_tag_title', 'Related Articles')); ?>" class="regular-text">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"> <?php echo __('Title Type'); ?>
				</th>
				<td>
				<select name="related_articles_by_tag_title_type">
					<option value="h1"<?php echo(get_option('related_articles_by_tag_title_type', 'h2') == 'h1' ? ' selected="selected"' : ''); ?>>H1</option>
					<option value="h2"<?php echo(get_option('related_articles_by_tag_title_type', 'h2') == 'h2' ? ' selected="selected"' : ''); ?>>H2</option>
					<option value="h3"<?php echo(get_option('related_articles_by_tag_title_type', 'h2') == 'h3' ? ' selected="selected"' : ''); ?>>H3</option>
				</select>
				<p class="description">
					Choose the type of title header you desire
				</p></td>
			</tr>
			<tr valign="top">
				<th scope="row"> <?php echo __('Mode'); ?>
				</th>
				<td>
				<select name="related_articles_by_tag_mode">
					<option value=""<?php echo(get_option('related_articles_by_tag_mode') == '' ? ' selected="selected"' : ''); ?>>- Don't Show Related Articles -</option>
					<option value="auto" disabled="disabled"<?php echo(get_option('related_articles_by_tag_mode') == 'auto' ? ' selected="selected"' : ''); ?>>Add to All Posts and Pages</option>
					<option value="shortcode"<?php echo(get_option('related_articles_by_tag_mode') == 'shortcode' ? ' selected="selected"' : ''); ?>>Shortcode</option>
				</select>
				<p class="description">
					<b>Don't Show Related Articles</b>: Disables shortcodes and automatic insert
					<br/>
					<b>Add to All Posts and Pages</b>: Related articles links will show automatically at the end of every post and page
					<br/>
					<b>Shortcode</b>: Related articles links will show only where you add the shortcode [related-articles-by-tag]
				</p></td>
			</tr>
			<tr valign="top">
				<th scope="row"> <?php echo __('Top Articles'); ?>
				</th>
				<td>
					<input name="related_articles_by_tag_count" disabled="disabled" type="text" id="related_articles_by_tag_count" value="3" class="regular-text">
					<p class="description">
						Max. num of related articles to show (leave empty for unlimited)
					</p>
			</td>
			<tr valign="top">
				<th scope="row"> <?php echo __('Order Related Articles By'); ?> </th>
				<td>
				<select name="related_articles_by_tag_order">
					<option value="menu"<?php echo(get_option('related_articles_by_tag_order') == 'menu' ? ' selected="selected"' : ''); ?>>Menu Order</option>
					<option value="asc" disabled="disabled"<?php echo(get_option('related_articles_by_tag_order') == 'asc' ? ' selected="selected"' : ''); ?>>Title (ASC)</option>
					<option value="desc" disabled="disabled"<?php echo(get_option('related_articles_by_tag_order') == 'desc' ? ' selected="selected"' : ''); ?>>Title (DESC)</option>
					<option value="date_asc" disabled="disabled"<?php echo(get_option('related_articles_by_tag_order') == 'date_asc' ? ' selected="selected"' : ''); ?>>Date (ASC)</option>
					<option value="date_desc" disabled="disabled"<?php echo(get_option('related_articles_by_tag_order') == 'date_desc' ? ' selected="selected"' : ''); ?>>Date (DESC)</option>
				</select>
				<p class="description">
					<b>Menu Order</b>: Orders Posts Based on the menu order you set in the edit post page
				</p></td>
			</tr>
			<tr valign="top">
				<th scope="row"> <?php echo __('Apply to'); ?> </th>
				<td>
				<select name="related_articles_by_tag_apply">
					<option value="posts"<?php echo(get_option('related_articles_by_tag_apply') == 'posts' ? ' selected="selected"' : ''); ?>>Posts</option>
					<option value="pages"<?php echo(get_option('related_articles_by_tag_apply') == 'pages' ? ' selected="selected"' : ''); ?>>Pages</option>
					<option value="posts_pages"<?php echo(get_option('related_articles_by_tag_apply') == 'posts_pages' ? ' selected="selected"' : ''); ?>>Posts & Pages</option>
				</select>
				<p class="description">
					<b>Posts</b>: Will add links to only posts with the same tag(s)
					<br>
					<b>Pages</b>: Will add links to only pages with the same tag(s)
					<br>
					<b>Posts & Pages</b>: Will add links to both posts and pages with the same tag(s)
				</p></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	<h3>Full Version</h3>
	<p>The full version implements all the features that are disabled in the lite version:
		<ul style="list-style-type: circle; list-style-position: inside;">
			<li>Automatically add a list of related articles to all posts and pages (it can be too much work to add the shortcode everywhere)</li>
			<li>Set a maximum number of related articles to show (again, maybe you don't want 50 links at the bottom of your pages), in both page content and widget</li>
			<li>Order the list by title and date</li>
		</ul>	
	</p>
	<p>Please visit <a href="http://wppluginspool.com/downloads/related-articles-by-tag/">wppluginspool.com/downloads/related-articles-by-tag/</a> for the full version.</p>
</div>
<?php
}
?>
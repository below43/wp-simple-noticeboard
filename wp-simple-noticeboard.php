<?php
/*
Plugin Name: WP Simple Noticeboard
Description: This plugin creates a custom post type named 'Notice'.
Version: 1.0
Author: Andrew Drake <andrew@drake.nz>
License: MIT License
*/


function create_notice_post_type()
{

	$labels = array(
		'name' => __('Notices'),
		'singular_name' => __('Notice')
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'notices'),
		'show_in_rest' => true,
		'supports' => array('title', 'thumbnail', 'categories'),
		'taxonomies' => array('category'),

		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 7,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true,
		'has_archive' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'capability_type' => 'post',
	);
	register_post_type(
		'notice',
		$args
	);
}

add_action('init', 'create_notice_post_type');

function include_notices_in_category_pages($query)
{
	if ($query->is_category() && $query->is_main_query()) {
		$post_types = $query->get('post_type');
		if (is_array($post_types)) {
			$post_types[] = 'notice';
		} else {
			$post_types = array('post', 'notice');
		}
		$query->set('post_type', $post_types);
	}
}
add_action('pre_get_posts', 'include_notices_in_category_pages');


function add_notice_meta_boxes()
{
	add_meta_box('notice_meta_box', 'Notice Details', 'display_notice_meta_box', 'notice', 'normal', 'high');
}

add_action('add_meta_boxes', 'add_notice_meta_boxes');

function display_notice_meta_box($notice)
{
	$url = get_post_meta($notice->ID, 'url', true);
	$date_from = get_post_meta($notice->ID, 'date_from', true);
	$date_to = get_post_meta($notice->ID, 'date_to', true);
	$notice_text = get_post_meta($notice->ID, 'notice_text', true);
	$date_enabled = get_post_meta($notice->ID, 'date_enabled', true); // New meta

	if ($date_enabled === '') $date_enabled = '1'; // Default to enabled for existing posts
?>
	<table>
		<tr>
			<td style="width: 100%">Notice</td>
			<td><textarea rows="4" cols="50" name="notice_text" id="notice_text"><?php echo $notice_text; ?></textarea></td>
		</tr>
		<tr>
			<td style="width: 100%">Website URL for more info</td>
			<td><input type="text" size="80" name="notice_url" id="notice_url" value="<?php echo $url; ?>" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<label>
					<input type="checkbox" name="notice_date_enabled" id="notice_date_enabled" value="1" <?php checked($date_enabled, '1'); ?> />
					Enable date range (show/hide notice based on date)
				</label>
			</td>
		</tr>
		<tr class="notice-date-row">
			<td style="width: 100%">Notice Display Date From</td>
			<td><input type="date" name="notice_date_from" id="notice_date_from" value="<?php echo esc_attr($date_from); ?>" /></td>
		</tr>
		<tr class="notice-date-row">
			<td style="width: 100%">Notice Display Date To</td>
			<td><input type="date" name="notice_date_to" id="notice_date_to" value="<?php echo esc_attr($date_to); ?>" /></td>
		</tr>
	</table>
	<script>
		jQuery(function($) {
			function toggleDateRows() {
				if ($('#notice_date_enabled').is(':checked')) {
					$('.notice-date-row').show();
				} else {
					$('.notice-date-row').hide();
					// Clear the date fields when disabling
					$('#notice_date_from').val('');
					$('#notice_date_to').val('');
				}
			}
			$('#notice_date_enabled').on('change', toggleDateRows);
			toggleDateRows();
		});
	</script>
	<?php
}


function save_notice_meta_fields($post_id)
{
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (get_post_type($post_id) == 'notice') {
		update_post_meta($post_id, 'notice_text', sanitize_text_field($_POST['notice_text'] ?? ''));
		update_post_meta($post_id, 'url', sanitize_text_field($_POST['notice_url'] ?? ''));
		update_post_meta($post_id, 'date_from', sanitize_text_field($_POST['notice_date_from'] ?? ''));
		update_post_meta($post_id, 'date_to', sanitize_text_field($_POST['notice_date_to'] ?? ''));
		update_post_meta($post_id, 'date_enabled', isset($_POST['notice_date_enabled']) ? '1' : '0');
	}
}

add_action('save_post', 'save_notice_meta_fields');

add_action('admin_print_footer_scripts', 'notice_admin_script', 99);

function notice_admin_script()
{
	global $post_type;
	if ('notice' == $post_type) {
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#publish').click(function() {
					if ($('#title').val() == '') {
						alert('Title is required');
						$('#title').focus();
						return false;
					}
					if ($('#notice_text').val() == '') {
						alert('Notice is required');
						$('#notice_text').focus();
						return false;
					}
					if ($('#notice_date_enabled').is(':checked')) {
						if ($('#notice_date_to').val() == '') {
							alert('Date To is required');
							$('#notice_date_to').focus();
							return false;
						}
						if ($('#notice_date_from').val() == '') {
							alert('Date From is required');
							$('#notice_date_from').focus();
							return false;
						}
					}
				});
			});
		</script>
<?php
	}
}

// Shortcode for displaying a single notice
add_shortcode('display_noticeboard_item', 'display_noticeboard_func');

function display_noticeboard_func($atts)
{
	$notice_id = get_the_ID();
	$notice = get_post($notice_id);
	$url = get_post_meta($notice_id, 'url', true);
	$date_from = get_post_meta($notice_id, 'date_from', true);
	$date_to = get_post_meta($notice_id, 'date_to', true);
	$notice_text = get_post_meta($notice_id, 'notice_text', true);
	$date_enabled = get_post_meta($notice_id, 'date_enabled', true);

	//get the 200 wide, x high version of the thumbnail
	$thumbnail = get_the_post_thumbnail($notice_id, array(200, 200));

	// Only check date if enabled
	if ($date_enabled === '1') {
		$date_from_time = strtotime($date_from);
		$date_to_time = strtotime($date_to);
		$current_date = date('Y-m-d');
		$current_date_time = strtotime($current_date);

		if ($current_date_time < $date_from_time || $current_date_time > $date_to_time) {
			return "<p>This notice has expired.</p>";
		}
	}

	$output = "<h2>{$notice->post_title}</h2>
               {$thumbnail}
               <p>{$notice_text}</p>";
	if (!empty($url)) {
		$output .= "<p>More information: <a href='{$url}' target='_blank'>{$url}</a>";
	}
	return $output;
}

// Shortcode for displaying a list of notices
add_shortcode('display_noticeboard_list', 'display_noticeboard_list_func');

function display_noticeboard_list_func($atts)
{

	// Extract the attributes
	$atts = shortcode_atts(
		array(
			'category' => '', // Default value
            'title' => '',    // Optional title
            'hide_if_empty' => '0',
		),
		$atts,
		'display_noticeboard_list_func'
	);

	// If a category is specified in the shortcode, or GET params, add it to the query args
	$category_name = (!empty($atts['category'])) ? $atts['category'] : sanitize_text_field($_GET['category']);

	// If a search term is submitted, add it to the query args
	$search_term = isset($_GET['resource_search_term']) ? sanitize_text_field($_GET['resource_search_term']) : '';

	$args = array(
		'post_type' => 'notice',
		'posts_per_page' => -1, // Get all posts
		'orderby'   => 'date',
		'order'     => 'DESC',
		'category_name' => $category_name, // 'category_name' is a query arg for 'post' type, not 'notice' type
		's' => $search_term,

		// 'meta_query' => array(
		// 	'relation' => 'AND',
		// 	array(
		// 		'key' => 'date_from',
		// 		'value' => date('Y-m-d'),
		// 		'compare' => '<=',
		// 		'type' => 'CHAR'
		// 	),
		// 	array(
		// 		'key' => 'date_to',
		// 		'value' => date('Y-m-d'),
		// 		'compare' => '>=',
		// 		'type' => 'CHAR'
		// 	)
		// )
	);

	$notices = new WP_Query($args);

    $output = '<br/><br/>';
    if (!empty($atts['title'])) {
        $output .= '<h2>' . esc_html($atts['title']) . '</h2>';
    }
    $output .= '<ul class="simple-noticeboard-list">';
    $count = 0;
	if ($notices->have_posts()) {
		while ($notices->have_posts()) {
			$notices->the_post();
			$notice_id = get_the_ID();
			$notice_text = get_post_meta($notice_id, 'notice_text', true);
			$notice_text = wp_trim_words($notice_text, 40);

			$thumbnail = get_the_post_thumbnail($notice_id, array(300, 225));
			$categories = get_the_category_list(', ');

			// Skip the post if the current date is not within the date range
			$date_enabled = get_post_meta($notice_id, 'date_enabled', true);
			if ($date_enabled === '1') {
				$date_from = strtotime(get_post_meta($notice_id, 'date_from', true));
				$date_to = strtotime(get_post_meta($notice_id, 'date_to', true));
				$current_date = strtotime(date('Y-m-d'));
				if ($current_date < $date_from || $current_date > $date_to) {
					continue;
				}
			}

			$title = "<a style=\"text-decoration:none\" href='" . get_permalink() . "'>" . get_the_title() . "</a>";

			$output .= "<li>
							<center>{$thumbnail}</center>
							<h2>" . $title . "</h2>
							<p>{$notice_text}</p>";
			//view more link
			$output .= "<p class=\"view-more-link\"><a href='" . get_permalink() . "'>View more</a></p>";
			$output .= "</li>";

			$count++;
		}
	}
    if (!$count) {
        if ($atts['hide_if_empty'] === '1') {
            return ''; // Hide all output if empty and hide_if_empty is set
        } else {
            $output .= '<li>There are currently no notices to be displayed.</li>';
        }
    }
	wp_reset_postdata();
	$output .= '</ul>';
	return $output;
}

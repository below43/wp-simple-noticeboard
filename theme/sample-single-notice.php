<?php get_header(); ?>

<main id="main" class="site-main" role="main">
	<?php
	while ( have_posts() ) : the_post();

		// Check if the post is being displayed on a singular page
		if (is_singular()) {
			$page_name = "";
			//if the page referrer was local to this site, display a back link
			if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], get_site_url()) !== false) {
				//get the wordpress page title for the given url
				$page = get_page_by_path(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
				if ($page) {
					$page_name = $page->post_title;
					$page_href = get_permalink($page->ID);
				}
			}
			if (!$page_name) {
				$page_name = "Notices";
				$page_href = get_site_url() . "/notices";
			}
			echo "<p><small><a href=\"$page_href\">Back to $page_name</a></small></p>";
			echo do_shortcode('[display_noticeboard]');
		} else {
			echo do_shortcode('[display_noticeboard_list]');
		}

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile; // End of the loop.
	?>
</main><!-- #main -->

<?php get_footer(); ?>
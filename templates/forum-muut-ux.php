<?php
/**
 * forum-muut-ux.php
 * The page template for the forum root page. It contains all the Muut UX.
 * To override this template, copy this file to a muut directory under your theme's root
 * (wp-content/themes/my-theme/muut/forum-muut-ux.php) and make any modifications you like!
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */


get_header();

$root_forum = muut_get_option( 'remote_forum_name', '' );
$sub_forum = muut_get_page_forum_path();
?>
	<div id="main-content" class="main-content">
		<div id="content" class="site-content" role="main">
			<?php while ( have_posts() ) : the_post();
				if ( muut_is_forum_page() ) {
					muut_forum_page_anchor();
				}
			endwhile; ?>
		</div><!-- #content -->
	</div><!-- #main-content -->
<?php
get_sidebar();
get_footer();
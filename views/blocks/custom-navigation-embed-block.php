<?php
/**
 * The block is used for the Muut embed markup when custom navigation is being used.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */


if ( !isset( $settings ) ) {
	$settings = '';
}

$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';

if ( !isset( $path ) ) {
	$path = Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID() );
}
?>
<!-- Muut placeholder tag -->
<div <?php echo $id_attr; ?> class="muut-url" data-url="/i/<?php echo muut()->getRemoteForumName() . '/' . $path; ?>">

	<!-- Custom HTML -->
	<?php
	$category_headers = Muut_Forum_Category_Utility::getForumCategoryHeaders();
	foreach( $category_headers as $header_id => $header_array ) { ?>
		<div class="m-h3"><?php echo Muut_Forum_Category_Utility::getCategoryHeaderTitle( $header_id ); ?></div>
		<?php foreach ( $header_array as $category_post ) {
			$class = '';
			if ( !Muut_Forum_Category_Utility::isAllpostsCategory( $category_post->ID ) ) {
				$class .= 'non-category ';
			}
			?>
			<a href="#!/<?php echo $category_post->post_name; ?>" class="<?php echo $class; ?>"><?php echo $category_post->post_title; ?></a>
		<?php }
	} ?>
</div>
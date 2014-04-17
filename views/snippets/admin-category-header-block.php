<?php
/**
 * The list item block used for the custom navigation administration screen for category headers.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

if ( !isset( $header_block_id ) || ( !is_string( $header_block_id ) && !is_numeric( $header_block_id ) ) ) {
	$header_block_id = 'new';
}
if ( !isset( $header_block_title ) || !is_string( $header_block_title ) ) {
	$header_block_title = 'New';
}
?>

<li class="muut_forum_header_item" id="category_header_<?php echo $header_block_id; ?>">
	<div class="muut-category-header-actions">
			<label class="screen-reader-text" for="header-name-<?php echo $header_block_id; ?>"><?php _e( 'Header Text', 'muut' ); ?></label>
			<a href="#" class="x-editable" id="header-name-<?php echo $header_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $header_block_title; ?>"></a>
			<input type="button" class="button button-secondary new_category_for_header" id="new_category_in_<?php echo $header_block_id; ?>" title="<?php _e( 'New Category', 'muut' ); ?>" value="<?php _e( 'New Category', 'muut' ); ?>" />
	</div>
	<div class="muut-category-header-content">
		<ul id="muut_forum_nav_headers_<?php echo $header_block_id; ?>" class="muut_category_list">
			<li class="muut_forum_category_item" id="category_item_1">
				<div class="muut_category_item_content">
					<h3>Category 1</h3>
				</div>
			</li>
			<li class="muut_forum_category_item" id="category_item_1">
				<div class="muut_category_item_content">
					<h3>Category 1</h3>
				</div>
			</li>
		</ul>
	</div>
</li>
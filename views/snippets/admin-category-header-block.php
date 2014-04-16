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
		<div class="major-publishing-actions">
			<label for="header-name-<?php echo $header_block_id; ?>" class="header-input-label"><span><?php _e( 'Header Text', 'muut' ); ?></span></label>
			<input name="header-title[<?php echo $header_block_id; ?>]" id="header-name-<?php echo $header_block_id; ?>" type="text" class="regular-text menu-item-textbox" title="' . __( 'Header Text', 'muut' ) . '" value="<?php echo $header_block_title; ?>">
		</div>
	</div>
	<div class="muut-category-header-content">
	</div>
</li>
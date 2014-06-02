<?php
/**
 * The Individual Channel Embed widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Channel_Embed' ) ) {
	/**
	 * Muut Channel Embed widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_Channel_Embed extends WP_Widget {

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Channel_Embed
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_channel_embed_widget',
				__( 'Muut Channel', 'muut' ),
				array(
					'description' => __( 'Use this to embed a specific channel in a widget area.', 'muut' ),
				)
			);
		}

		/**
		 * Render the widget frontend output.
		 *
		 * @param array $args The sidebar arguments.
		 * @param array $instance The widget instance parameters.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function widget( $args, $instance ) {
			// Output the frontend widget content.
		}

		/**
		 * Render the admin form for widget customization.
		 *
		 * @param array $instance The widget instance parameters.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function form( $instance ) {
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-channel-embed.php' );
		}

		/**
		 * Process the widget arguments to save the customization for that instance.
		 *
		 * @param array $new_instance The changed/new arguments.
		 * @param array $old_instance The previous/old arguments.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['hide_online'] = !empty( $new_instance['hide_online'] ) ? $new_instance['hide_online'] : '0';
			$instance['disable_uploads'] = !empty( $new_instance['disable_uploads'] ) ? $new_instance['disable_uploads'] : '0';

			// Make sure that the path is saved as SOMETHING.
			$default_path = isset( $old_instance['muut_path'] ) ? $old_instance['muut_path'] : '';
			$default_path = empty( $default_path ) ? sanitize_title( $instance['title'] ) : $default_path;
			$default_path = empty( $default_path ) ? sanitize_title( $this->get_field_id( 'muut_path' ) ) : $default_path;
			$default_path = !isset( $old_instance['muut_path'] ) ? '' : $default_path;
			$instance['muut_path'] = !empty( $new_instance['muut_path'] ) ? Muut_Post_Utility::sanitizeMuutPath( $new_instance['muut_path'] ) : $default_path;

			return $instance;
		}
	}
}
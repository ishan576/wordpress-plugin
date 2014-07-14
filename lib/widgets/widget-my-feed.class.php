<?php
/**
 * The My Feed widget..
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_My_Feed' ) ) {
	/**
	 * Muut My Feed widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_My_Feed extends WP_Widget {

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_My_Feed
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_my_feed_widget',
				__( 'Muut \'My Feed\'', 'muut' ),
				array(
					'description' => __( 'Use this to show a logged in user\'s personal Muut feed.', 'muut' ),
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
			if ( !is_home() && ( Muut_Post_Utility::isMuutPost( get_the_ID() ) || Muut_Post_Utility::isMuutCommentingPost( get_the_ID() ) ) ) {
				return;
			}

			// Make sure the Muut resources get loaded (only stuff in the footer will work, as this happens
			// partway through page load.
			add_filter( 'muut_requires_muut_resources', '__return_true' );
			muut()->enqueueFrontendScripts();

			if ( isset( $instance['disable_uploads'] ) ) {
				$embed_args['allow-uploads'] = !$instance['disable_uploads'] ? 'true' : 'false';
			}

			$embed_args['title'] = isset( $instance['title'] ) ? $instance['title'] : '';

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . $embed_args['title'] . $args['after_title'];
			$path = 'feed';
			echo '<div id="muut-widget-my-feed-wrapper" class="muut_widget_wrapper muut_widget_my_feed_wrapper">';
			Muut_Channel_Utility::getChannelEmbedMarkup( $path, $embed_args, true );
			echo '<div id="muut-widget-my-feed-login"><a href="#" class="muut_login">' . __('Login', 'muut' ) . '</a></div>';
			echo '</div>';
			echo $args['after_widget'];
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
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-my-feed.php' );
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
			$instance['disable_uploads'] = !empty( $new_instance['disable_uploads'] ) ? $new_instance['disable_uploads'] : '0';

			return $instance;
		}
	}
}
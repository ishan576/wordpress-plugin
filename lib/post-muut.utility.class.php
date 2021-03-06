<?php
/**
 * The Forum Page static class that contains all the static methods required to interact with a post that
 * embeds a Muut forum, channel, or comments in some way.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Post_Utility' ) ) {

	/**
	 * Muut Forum Page Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Post_Utility
	{

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Post_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function __construct() {}

		/**
		 * The meta name for the super-options meta for individual forum pages.
		 * This post_meta will contain an array of the specific page options.
		 */
		const META_POSTSETTINGS = 'muut_post_settings';

		/**
		 * The method for setting a forum page's remote path.
		 *
		 * @param int $page_id The page ID for the forum page.
		 * @param string $path The path to set (if custom).
		 * @return bool Whether the page's remote path was updated or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function setChannelRemotePathForPage( $page_id, $path = '' ) {
			if ( !is_numeric( $page_id ) || !is_string( $path ) ) {
				return false;
			}

			self::setPostOption( $page_id, 'channel_remote_path', $path );
			return true;
		}

		/**
		 * Sets the page as the forum page.
		 *
		 * @param int $page_id The page ID we are turning into the forum page.
		 * @return bool Whether the page was successfully turned into the forum page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function setAsForumPage( $page_id ) {
			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			if ( self::getForumPageId() != $page_id ) {
				muut()->setOption( 'forum_page_id', $page_id );
				return true;
			}
		}

		/**
		 * Sets page as no longer being a forum page.
		 *
		 * @param int $page_id The page ID that we are removing as being the forum page.
		 * @return bool Whether the page was successfully turned into NO LONGER being the forum page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function removeAsForumPage( $page_id ) {
			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			if ( self::getForumPageId() == $page_id ) {
				muut()->setOption( 'forum_page_id', '' );
				return true;
			}
		}

		/**
		 * Returns whether a post utilizes Muut (forum page, channel page, etc.).
		 *
		 * @param int $post_id The post ID that we are checking.
		 * @return bool Whether the post uses Muut or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isMuutPost( $post_id ) {
			if( is_numeric( $post_id ) && get_post_meta( $post_id, 'muut_last_active_tab', true ) ) {
				$value = true;
			} else {
				$value = false;
			}
			return apply_filters( 'muut_is_muut_post', $value, $post_id );
		}

		/**
		 * Returns whether a is a Muut channel page.
		 *
		 * @param int $post_id The post ID that we are checking.
		 * @return bool Whether the page is a channel page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isMuutChannelPage( $post_id ) {
			if( is_numeric( $post_id ) && get_post_meta( $post_id, 'muut_last_active_tab', true ) == 'channel-tab' ) {
				$value = true;
			} else {
				$value = false;
			}
			return apply_filters( 'muut_is_channel_page', $value, $post_id );
		}

		/**
		 * Returns whether a is a Muut forum page.
		 *
		 * @param int $post_id The post ID that we are checking.
		 * @return bool Whether the page is a forum page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isMuutForumPage( $post_id ) {
			if( is_numeric( $post_id ) && get_post_meta( $post_id, 'muut_last_active_tab', true ) == 'forum-tab' ) {
				$value = true;
			} else {
				$value = false;
			}
			return apply_filters( 'muut_is_forum_page', $value, $post_id );
		}

		/**
		 * Gets the channel page's remote path.
		 *
		 * @param int $page_id The page ID that we are getting the remote channel path for.
		 * @param bool $no_suffix Whether to include ':comments' for unthreaded.
		 * @return string|false Returns the path if one is found for the forum page or false if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getChannelRemotePathForPage( $page_id, $no_suffix = false ) {
			if( !is_numeric( $page_id ) ) {
				return false;
			}

			$page_channel_options = Muut_Post_Utility::getPostOption( $page_id, 'channel_settings' );

			$path = isset( $page_channel_options['channel_path'] ) ? $page_channel_options['channel_path'] : '';

			return $path;
		}

		/**
		 * Gets a channel page's full index URI.
		 *
		 * @param int $page_id The page we are getting the remote URI for.
		 * @return string The full index URI.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public static function getChannelIndexUriForPage( $page_id ) {
			if( !is_numeric( $page_id ) ) {
				return false;
			}

			$path = self::getChannelRemotePathForPage( $page_id );

			$uri = Muut_Channel_Utility::getChannelIndexUri( $path );

			return apply_filters( 'muut_channel_page_index_uri', $uri, $page_id );
		}

		/**
		 * Checks if comments and Muut commenting are enabled for a post.
		 *
		 * @param int $post_id The post ID we are checking.
		 * @return bool True if muut commenting is being used, false if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isMuutCommentingPost( $post_id ) {
			$post = get_post( $post_id );
			$comment_count = get_comments( array( 'post_id' => $post_id, 'count' => true ) );
			$latest_update_array = muut_get_option('update_timestamps', array() );
			$activation_timestamp = muut()->getOption( 'activation_timestamp' );
			if ( muut()->getOption( 'replace_comments' )
				&& ( get_post_meta( $post_id, 'muut_last_active_tab', true ) == 'commenting-tab'
					|| ( !get_post_meta( $post_id, 'muut_last_active_tab', true )
						&& ( ( ( $comment_count == 0
							&& $post->post_status == 'auto-draft' ) )
							|| muut()->getOption( 'override_all_comments' )
						|| ( get_post_modified_time( 'U', false, $post_id ) < $activation_timestamp
							&& !has_shortcode( $post->post_content, 'muut' ) && !has_shortcode( $post->post_content, 'moot' )
							&& $comment_count == 0 ) ) )
				&& get_post( $post_id )->comment_status == 'open' )
				&& ( get_post_meta( $post_id, 'muut_last_active_tab', true ) !== '0'
					|| $post->post_status == 'auto-draft' ) ){
				return true;
			} else {
				// For old posts, ones that existed before upgrade, lets set the comment status to closed
				// if they have an old shortcode, so that WP comments don't show up either.
				if ( get_post_modified_time( 'U', false, $post_id ) < $activation_timestamp
					&& $post->comment_status == 'open'
					&& ( has_shortcode( $post->post_content, 'muut' )
					|| has_shortcode( $post->post_content, 'moot' ) ) ) {
					$post_args = array(
						'ID' => $post_id,
						'comment_status' => 'closed',
					);

					wp_update_post( $post_args );
				}
				return false;
			}
		}

		/**
		 * Sets the other options for a post using Muut.
		 *
		 * @param int $post_id The post ID that we are saving the options for.
		 * @param string $option_name The option name for the setting we are saving.
		 * @param mixed $value The value we are setting for the option.
		 * @param string $section Whether the option sits in a subsection (deeper in settings array).
		 * @return bool Whether the options were saved or not.
		 */
		public static function setPostOption( $post_id, $option_name, $value, $section = null ) {
			if ( !is_numeric( $post_id ) || !is_string( $option_name ) ) {
				return false;
			}

			$current_settings = get_post_meta( $post_id, self::META_POSTSETTINGS, true );

			$new_setting = apply_filters( 'muut_set_post_options', array( $option_name => $value ), $post_id );

			update_post_meta( $post_id, self::META_POSTSETTINGS, wp_parse_args( $new_setting, $current_settings ) );

			return true;
		}

		/**
		 * Gets a given setting for a post using Muut.
		 *
		 * @param int $post_id The post ID that we are getting a setting for.
		 * @param string $option_name The option name that we are getting for the page using Muut.
		 * @param string $default The default value if none is returned.
		 * @return mixed The value of the option.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getPostOption( $post_id, $option_name, $default = '' ) {
			if ( !is_numeric( $post_id ) || !is_string( $option_name ) ) {
				return false;
			}

			$current_settings = get_post_meta( $post_id, self::META_POSTSETTINGS, true );

			$settings = apply_filters( 'muut_get_post_options', $current_settings, $post_id );

			return isset( $settings[$option_name] ) ? $settings[$option_name] : $default;
		}


		/**
		 * Renders a given forum page's forum (returns the Muut JS anchor element).
		 *
		 * @param int $page_id The page ID whose forum we are rendering.
		 * @param bool $echo Whether to echo the anchor or return the markup.
		 * @return string|void The anchor markup or void, if it is set to be echoed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function forumPageEmbedMarkup( $page_id, $echo = true ) {
			if ( !is_numeric( $page_id ) || !self::isMuutPost( $page_id ) ) {
				return false;
			}

			$settings = ' ';
			if ( self::isMuutChannelPage( $page_id ) ) {
				$post_options = self::getPostOption( $page_id, 'channel_settings' );
				$type_of_embed = 'channel';
			} elseif ( self::isMuutForumPage( $page_id ) ) {
				$post_options = self::getPostOption( $page_id, 'forum_settings' );
				$type_of_embed = 'forum';
			} else {
				return;
			}
			if ( isset( $post_options['hide_online'] ) ) {
				$args['show-online'] = !$post_options['hide_online'] ? 'true' : 'false';
			}
			if ( isset( $post_options['disable_uploads'] ) ) {
				$args['allow-uploads'] = !$post_options['disable_uploads'] ? 'true' : 'false';
			}

			$args['title'] = get_the_title( $page_id );
			$args['channel'] = get_the_title( $page_id );

			$settings = muut()->getEmbedAttributesString( $args );

			if ( $type_of_embed == 'channel' ) {
				$path = self::getChannelRemotePathForPage( $page_id );

				$embed = Muut_Channel_Utility::getChannelEmbedMarkup( $path, $args );
			} elseif ( $type_of_embed == 'forum' ) {
				ob_start();
					include ( muut()->getPluginPath() . 'views/blocks/forum-page-embed.php' );
				$embed = ob_get_clean();
				$embed = apply_filters( 'muut_forum_page_embed_content', $embed, $page_id );
			} else {
				return;
			}

			$embed = apply_filters( 'muut_embed_content', $embed, $page_id );
			if ( $echo ) {
				echo $embed;
			} else {
				return $embed;
			}
		}

		/**
		 * Gets the ID of the page that is being used as the main forum page.
		 *
		 * @return int|false The ID of the forum page, or false on failure.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getForumPageId() {
			$forum_page_id = muut()->getOption( 'forum_page_id', false );
			if ( 'publish' != get_post_status( $forum_page_id ) ) {
				return false;
			} else {
				return $forum_page_id;
			}
		}

		/**
		 * Sanitizes a Muut remote path for storage.
		 *
		 * @param string $path The current channel path.
		 * @return string The sanitized path.
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public static function sanitizeMuutPath( $path ) {
			if ( substr( $path, 0, 1 ) == '/' ) {
				$path = substr( $path, 1 );
			}
			if ( substr( $path, -1 ) == '/' ) {
				$path = substr( $path, 0, -1 );
			}
			$path =  str_replace( '%3A', ':', implode('/', array_map('rawurlencode', explode( '/', $path ) ) ) );
			return $path;
		}
	}
}
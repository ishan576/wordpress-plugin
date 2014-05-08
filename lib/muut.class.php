<?php
/**
 * The main class file for the Muut plugin.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut' ) ) {

	/**
	 * Muut main class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut
	{

		/**
		 * The current version of urGuru
		 */
		const VERSION = '3.0';

		/**
		 * The version of Muut this was released with.
		 */
		const MUUTVERSION = '1.11.9';

		/**
		 * The plugin slug, for all intents and purposes.
		 */
		const SLUG = 'muut';

		/**
		 * The option name for the Muut plugin.
		 */
		const OPTIONNAME = 'muut_options';

		/**
		 * The Muut server location.
		 */
		const MUUTSERVERS = 'muut.com';

		/**
		 * @static
		 * @property Muut The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property string The directory of the plugin.
		 */
		protected $pluginDir;

		/**
		 * @property string The plugin path.
		 */
		protected $pluginPath;

		/**
		 * @property string The url of the plugin.
		 */
		protected $pluginUrl;

		/**
		 * @property string The Muut container element css class.
		 */
		protected $wrapperClass;

		/**
		 * @property string The Muut container element css id.
		 */
		protected $wrapperId;

		/**
		 * @property string The path prefix for fetching information from the Muut servers.
		 *                      Depends on if proxy rewriting is enabled.
		 */
		protected $contentPathPrefix;

		/**
		 * @property array The plugin options array.
		 */
		protected $options;

		/**
		 * @property array An array of admin notices to render.
		 */
		protected $adminNotices;

		/**
		 * @property array The array of languages available for Muut.
		 */
		protected $languages;

		/**
		 * The singleton method.
		 *
		 * @return Muut The instance.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public static function instance() {
			if ( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * The class constructor.
		 *
		 * @return Muut
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->pluginDir = trailingslashit( basename( dirname( dirname( __FILE__ ) ) ) );
			$this->pluginPath = trailingslashit( dirname( dirname( __FILE__ ) ) );
			$this->pluginUrl = trailingslashit( plugins_url( '', dirname( __FILE__ ) ) );
			$this->wrapperClass = 'muut';
			$this->wrapperId = '';
			$this->adminNotices = array();

			$this->loadLibraries();
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds the main actions for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'admin_init', array( $this, 'maybeAddRewriteRules' ) );
			add_action( 'admin_menu', array( $this, 'createAdminMenuItems' ) );
			add_action( 'admin_notices', array( $this, 'renderAdminNotices' ) );
			add_action( 'add_meta_boxes_page', array( $this, 'addPageMetaBoxes' ) );
			add_action( 'load-' . self::SLUG . '_page_muut_settings', array( $this, 'saveSettings' ) );
			add_action( 'flush_rewrite_rules_hard', array( $this, 'removeRewriteAdded' ) );
			add_action( 'save_post_page', array( $this, 'saveForumPage' ), 10, 2 );

			add_action( 'init', array( $this, 'registerScriptsAndStyles' ) );
			add_action( 'init', array( $this, 'disregardOldMoot' ), 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendScripts' ) );

			add_action( 'wp_print_scripts', array( $this, 'printCurrentPageJs' ) );

			add_action( 'muut_forum_custom_navigation_after_headers', array( 'Muut_Forum_Page_Utility', 'forumPageCommentsNavigationItem' ), 10 , 1 );

			add_action( 'load-nav-menus.php', array( $this, 'checkIfUserEditedMenus') );
		}

		/**
		 * Adds the main filters for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {
			add_filter( 'body_class', array( $this, 'addBodyClasses' ) );
			add_filter( 'admin_body_class', array( $this, 'addAdminBodyClasses' ) );
			add_filter( 'post_type_link', array( $this, 'filterForumChannelsPermalinks' ), 10, 2 );
			add_filter( 'the_content', array( $this, 'filterForumPageContent' ), 10 );
		}

		/**
		 * Determines whether a given admin function should continue to execute based on the current screen id.
		 *
		 * @param string $screen_id What the id of the screen should be.
		 * @return bool True if the screen id does not match (as in "YES, bail"); false if good to go.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function adminBail( $screen_id ) {
			if ( get_current_screen()->id != $screen_id ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Load the necessary files for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function loadLibraries() {

			// Load the template tags.
			require_once( $this->pluginPath . 'public/template_tags.php' );

			// Load the initializer class.
			require_once( $this->pluginPath . 'lib/initializer.class.php' );
			Muut_Initializer::instance();
		}

		/**
		 * Gets the plugin directory.
		 *
		 * @return string The plugin directory.
		 */
		public function getPluginDir() {
			return $this->pluginDir;
		}

		/**
		 * Gets the plugin absolute path.
		 *
		 * @return string The plugin path.
		 */
		public function getPluginPath() {
			return $this->pluginPath;
		}

		/**
		 * Gets the plugin URL.
		 *
		 * @return string The plugin URL.
		 */
		public function getPluginUrl() {
			return $this->pluginUrl;
		}

		/**
		 * Gets the content path prefix for fetching content from the Muut servers.
		 * Depends on if proxy rewriting is enabled.
		 *
		 * @return string The path prefix (full server is rewriting is disabled).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getContentPathPrefix() {
			if ( !isset( $this->contentPathPrefix ) ) {
				if ( $this->getOption( 'disable_proxy_rewrites', false ) ) {
					$this->contentPathPrefix = 'https://' . self::MUUTSERVERS . '/';
				} else {
					$this->contentPathPrefix = '/';
				}
			}
			return $this->contentPathPrefix;
		}

		/**
		 * Gets the remote forum name that is registered.
		 *
		 * @return string The remote forum name.
		 */
		public function getRemoteForumName() {
			return $this->getOption( 'remote_forum_name', '' );
		}

		/**
		 * Gets the Muut element wrapper class.
		 *
		 * @return string The container css class attribute.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getWrapperCssClass() {
			return ' ' . apply_filters( 'muut_wrapper_css_class', $this->wrapperClass );
		}

		/**
		 * Gets the Muut element wrapper id attribute.
		 *
		 * @return string The container id attribute.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getWrapperCssId() {
			return apply_filters( 'muut_wrapper_css_id', $this->wrapperId );
		}

		/**
		 * Adds the rewrite rules for indexing Muut posts locally if they are currently not already set.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function maybeAddRewriteRules() {
			if ( $this->getOption( 'remote_forum_name', '' ) !== '' && !$this->getOption( 'added_rewrite_rules', false ) && !$this->getOption( 'disable_proxy_rewrites', false ) ) {
				$this->addProxyRewrites();
			}
		}

		/**
		 * If using DEFAULT permalinks (i.e. no permalinks), we need to still add the rewrite such that data is
		 * indexed properly.
		 * Much of the design of this method comes from wp-admin/includes/misc.php, save_mod_rewrite_rules() function.
		 * If we are not using defaults, lets make sure to filter in proper rewrites by adding the filter and then
		 * flushing the rules (so it gets executed).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addProxyRewrites() {
			if ( get_option( 'permalink_structure', '') == '' ) {

				$home_path = get_home_path();
				$htaccess_file = $home_path.'.htaccess';

				$rules = array(
					'<IfModule mod_rewrite.c>',
					'RewriteEngine On',
					'RewriteBase /',
					'RewriteRule ^i/(' . $this->getOption( 'remote_forum_name' ) . ')(/.*)?$ http://' . self::MUUTSERVERS . '/i/$1$2 [P]',
					'RewriteRule ^m/(.*)$ http://' . self::MUUTSERVERS . '/m/$1 [P]',
					'</IfModule>',
				);

				if ( ( !file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) ) {
					insert_with_markers( $htaccess_file, 'WordPress', $rules );
				}
				$this->setOption( 'added_rewrite_rules', true );
			} else {
				add_filter( 'mod_rewrite_rules', array( $this, 'addProxyRewritesFilter' ) );
				flush_rewrite_rules( true );
			}
		}

		/**
		 * Adds the necessary rewrite rules to fix SEO issues such that Muut posts are indexed at this site
		 * even though the content is hosted on Muut's servers.
		 *
		 * @param string $rules The current string containing all of the re-write structure block.
		 * @return string The modified rewrite block (as a long string).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addProxyRewritesFilter( $rules ) {
			$permastruct = get_option( 'permalink_structure', '' );

			$muut_rules = "RewriteRule ^i/(" . $this->getOption( 'remote_forum_name' ) . ")(/.*)?\$ http://" . self::MUUTSERVERS . "/i/\$1\$2 [P]\n";
			$muut_rules .=	"RewriteRule ^m/(.*)$ http://" . self::MUUTSERVERS . "/m/\$1 [P]";

			if ( $permastruct == '' ) {
				$rules .= "<IfModule mod_rewrite.c>\n";
				$rules .= "RewriteEngine On\n";
				$rules .= "RewriteBase /\n";
				$rules .= $muut_rules . "\n";
				$rules .= "</IfModule>\n";
			} else {
				$split_rules = explode( "\n", $rules );
				$rule_before_index = array_search( 'RewriteRule ^index\.php$ - [L]', $split_rules );
				array_splice( $split_rules, $rule_before_index, 0, $muut_rules );
				$rules = implode( "\n", $split_rules ) . "\n";
			}

			$this->setOption( 'added_rewrite_rules', true );
			return $rules;
		}

		/**
		 * Removes the plugin setting that says we have added the necessary rewrite rules.
		 *
		 * @param bool $hard_flush Whether we are undergoing a hard flush or not.
		 * @return bool The same variable as the parameter passed in (we are treating this like and action).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function removeRewriteAdded( $hard_flush ) {
			if ( $hard_flush ) {
				$this->setOption( 'added_rewrite_rules', false );
				// Make have the rewrites double checked at top of page (in case we were in the middle of initial flush).
				add_action( 'admin_head', array( $this, 'maybeAddRewriteRules' ) );
			}
			return $hard_flush;
		}

		/**
		 * If the user is upgrading from the OLD plugin with namespace "moot" instead of "muut" and has not yet
		 * deactivated it, make sure to ignore it.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 2.0.13
		 */
		public function disregardOldMoot() {
			if ( class_exists( 'Moot' ) ) {
				remove_filter('the_content', array(Moot::get_instance(), 'default_comments'));

				remove_action('wp_enqueue_scripts', array(Moot::get_instance(), 'moot_includes'));
				remove_action('wp_head', array(Moot::get_instance(), 'moot_head'));
				remove_action('admin_menu', array(Moot::get_instance(), 'moot_admin_menu'));
				remove_action('admin_init', array(Moot::get_instance(), 'moot_settings'));
			}
		}

		/**
		 * Gets the Muut language equivalent of a given WordPress language abbreviation.
		 *
		 * @param string $wp_lang The WordPress abbreviation for a given language.
		 * @return string The Muut equivalent language string.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMuutLangEquivalent( $wp_lang ) {
			// Format is wp_lang => muut_lang.
			$muut_langs = apply_filters( 'muut_and_wp_langs', array(
				'ar' => 'ar',
				'pt_BR' => 'pt-br',
				'bg_BG' => 'bg',
				'zh_CN'=> 'ch',
				'zh_TW' => 'tw',
				'nl_NL' => 'nl',
				'en_US' => 'en',
				'fi' => 'fi',
				'fr_FR' => 'fr',
				'de_DE' => 'de',
				'hu_HU' => 'hu',
				'he_IL' => 'he',
				'id_ID' => 'id',
				'ja' => 'ja',
				'ko_KR' => 'ko',
				'pl_PL' => 'pl',
				'ru_RU' => 'ru',
				'es_ES' => 'es',
				'sv_SE' => 'se',
				'ta_IN' => 'ta',
				'tr_TR' => 'tr',
			) );

			if ( in_array( $wp_lang, array_keys( $muut_langs ) ) ) {
				return $muut_langs[$wp_lang];
			} else {
				return 'en';
			}
		}

		/**
		 * Registers the various scripts and styles that we may be using in the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function registerScriptsAndStyles() {
			$muut_version = $this->getMuutVersion();
			wp_register_script( 'muut', '//cdn.' . self::MUUTSERVERS . '/' . $muut_version . '/moot.' . $this->getOption( 'language', 'en' ) . '.min.js', array( 'jquery' ), $muut_version, true );
			wp_register_script( 'muut-admin-functions', $this->pluginUrl . 'resources/admin-functions.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'x-editable', $this->pluginUrl . 'vendor/jqueryui-editable/js/jqueryui-editable.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'jquery-ui-button' ), '1.5.1', true);

			wp_register_script( 'muut-frontend-functions', $this->pluginUrl . 'resources/frontend-functions.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'muut-sso', $this->pluginUrl . 'resources/muut-sso.js', array( 'jquery', 'muut' ), '1.0', true );

			wp_register_style( 'muut-admin-style', $this->pluginUrl . 'resources/admin-style.css' );
			wp_register_style( 'x-editable-style', $this->pluginUrl . 'vendor/jqueryui-editable/css/jqueryui-editable.css' );
			wp_register_style( 'muut-forum-css', '//cdn.' . self::MUUTSERVERS . '/' . $muut_version . '/moot.css', array(), $muut_version );

			wp_register_style( 'muut-font-css', $this->pluginUrl . 'resources/muut-font.css', array(), $muut_version );
			// Localization rules.
			$localizations = array(
				'muut-admin-functions' => array(
					//'default_path' => sprintf( __( '%sdefault%s', 'muut' ), '/(', ')' ),
				),
			);

			foreach ( $localizations as $key => $array ) {
				$new_key = str_replace( '-', '_', $key ) . '_localized';

				wp_localize_script( $key, $new_key, $array );
			}
		}

		/**
		 * Gets the version of Muut to use.
		 *
		 * @return string
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMuutVersion() {
			$muut_version = defined( 'MUUT_VERSION' ) ? MUUT_VERSION : self::MUUTVERSION;

			return $muut_version;
		}

		/**
		 * Gets the default value of a given option.
		 *
		 * @param string $option_name Gets the default value of a given Muut option.
		 * @return array The array of default values for the Muut options.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function getOptionsDefaults() {

			$default_lang = 'en';

			if ( defined( 'WPLANG' ) ) {
				$default_lang = $this->getMuutLangEquivalent( WPLANG );
			}

			$defaults = apply_filters( 'muut_options_defaults', array(
				'remote_forum_name' => '',
				// TODO: Make this match whatever language is set for the site.
				'language' => $default_lang,
				'replace_comments' => false,
				'use_threaded_commenting' => false,
				'override_all_comments' => true,
				'show_comments_in_forum' => false,
				'forum_home_id' => false,
				'forum_page_defaults' => array(
					'is_threaded' => false,
					'show_online' => true,
					'allow_uploads' => false,
				),
				'subscription_api_key' => '',
				'subscription_secret_key' => '',
				'subscription_use_sso' => false,
				'forum_channel_defaults' => array(
					'show_in_allposts' => true,
				),
				'disable_proxy_rewrites' => false,
				'comments_base_domain' => $_SERVER['SERVER_NAME'],
			) );

			return $defaults;
		}

		/**
		 * Enqueues the admin-side scripts we will be using.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueAdminScripts() {
			$screen = get_current_screen();
			if ( $screen->id == 'page'
				|| $screen->id == self::SLUG . '_page_muut_settings'
				|| $screen->id == 'toplevel_page_muut' ) {
				wp_enqueue_script( 'muut-admin-functions' );
				wp_enqueue_style( 'muut-admin-style' );
			}


			if ( $screen->id == self::SLUG . '_page_muut_custom_navigation' ) {
				wp_enqueue_script( 'muut-admin-functions' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'x-editable' );
				wp_enqueue_style( 'x-editable-style' );
				wp_enqueue_style( 'muut-admin-style' );
			}

			// Enqueue the font on all admin pages for the menu icon.
			wp_enqueue_style( 'muut-font-css' );
		}

		/**
		 * Enqueues scripts for the frontend.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueFrontendScripts() {
			if ( $this->needsMuutResources() ) {
				wp_enqueue_script( 'muut' );
				wp_enqueue_style( 'muut-forum-css' );
				wp_enqueue_script( 'muut-frontend-functions' );
			}


		}

		/**
		 * Prints the JS necessary on a given frontend page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function printCurrentPageJs() {
			if ( !is_admin() && get_post() ) {
				$page_id = get_the_ID();
				if ( Muut_Forum_Page_Utility::isForumPage( $page_id ) ) {
					echo '<script type="text/javascript">';
					if ( $this->getOption( 'forum_home_id', 0 ) == $page_id ) {
						echo 'var muut_current_page_permalink = "' . get_permalink( $page_id ) . '";';
						echo 'var muut_show_comments_in_nav = ' . $this->getOption( 'show_comments_in_forum' ) . ';';
						echo 'var muut_comments_base_domain = "' . $this->getOption( 'comments_base_domain' ) . '";';
					}
					echo '</script>';
				}
			}
		}

		/**
		 * Adds the metaboxes for the Page admin editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addPageMetaBoxes() {
			if ( $this->getOption( 'remote_forum_name', '' ) != '' ) {
				add_meta_box(
					'muut-is-forum-page',
					__( 'Muut', 'muut' ),
					array( $this, 'renderMuutPageMetaBox' ),
					'page',
					'side',
					'high'
				);
			}
		}

		/**
		 * Renders the metabox content for the Page Editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderMuutPageMetaBox() {
			include( $this->pluginPath . 'views/admin-page-metabox.php' );
		}

		/**
		 * Gets the array of possible languages.
		 *
		 * @return array The array of languages in the form of [abbrev] => [human_readable].
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getLanguages() {
			if ( !isset( $this->languages ) || is_null( $this->languages ) ){
				$this->languages = apply_filters( 'muut_languages', array(
						'ar' => __( 'Arabic', 'muut' ),
						'pt-br' => __( 'Brazil Portuguese', 'muut' ),
						'bg' => __( 'Bulgarian', 'muut' ),
						'ch' => __( 'Chinese', 'muut' ),
						'tw' => __( 'Chinese / Taiwan', 'muut' ),
						'nl' => __( 'Dutch', 'muut' ),
						'en' => __( 'English', 'muut' ),
						'fi' => __( 'Finnish', 'muut' ),
						'fr' => __( 'French', 'muut' ),
						'de' => __( 'German', 'muut' ),
						'hu' => __( 'Hungarian', 'muut' ),
						'he' => __( 'Hebrew', 'muut' ),
						'id' => __( 'Indonesian', 'muut' ),
						'ja' => __( 'Japanese', 'muut' ),
						'ko' => __( 'Korean', 'muut' ),
						'pl' => __( 'Polish', 'muut' ),
						'ru' => __( 'Russian', 'muut' ),
						'es' => __( 'Spanish', 'muut' ),
						'se' => __( 'Swedish', 'muut' ),
						'ta' => __( 'Tamil', 'muut' ),
						'tr' => __( 'Turkish', 'muut' ),
					)
				);
			}
			return $this->languages;
		}

		/**
		 * Queues an admin notice to be rendered when admin_notices is run.
		 *
		 * @param string $type The type of notice (really the class for the notice div).
		 * @param string $content The content of the admin notice.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function queueAdminNotice( $type, $content ) {
			$this->adminNotices[] = array(
				'type' => $type,
				'content' => $content,
			);
		}

		/**
		 * Renders the admin notices that have been queued.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderAdminNotices() {
			foreach ( $this->adminNotices as $notice ) {
				echo '<div class="' . $notice['type'] . '">';
				echo '<p>' . $notice['content'] . '</p>';
				echo '</div>';
			}
		}

		/**
		 * Gets and returns the plugin options (as an array).
		 *
		 * @return array The plugin options.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function getOptions() {
			if ( !isset( $this->options ) || is_null( $this->options ) ) {
				$options = get_option( self::OPTIONNAME, array() );
				$this->options = apply_filters( 'muut_get_options', $options );
			}
			return $this->options;
		}

		/**
		 * Gets and returns a specific plugin option.
		 *
		 * @param string $option_name The Muut option name.
		 * @param mixed  $default     The default value if none is returned.
		 * @return mixed The option value.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function getOption( $option_name, $default = '' ) {
			$options = $this->getOptions();

			$default_options = $this->getOptionsDefaults();

			if ( in_array( $option_name, array_keys( $default_options ) ) ) {
				$default = $default_options[$option_name];
			}

			if ( !is_string( $option_name ) )
				return false;

			$value = isset( $options[$option_name] ) ? $options[$option_name] : $default;

			return apply_filters( 'muut_get_option', $value, $option_name, $default );
		}

		/**
		 * Sets the plugin super-option array.
		 *
		 * @param array $options       The options array that we are saving.
		 * @param bool  $apply_filters Whether to apply filters to the options being saved or not.
		 * @return bool True on success, false on failure.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function setOptions( $options, $apply_filters = true ) {
			if ( !is_array( $options ) )
				return false;

			$options = $apply_filters ? apply_filters( 'muut_save_options', $options ) : $options;

			if ( update_option( self::OPTIONNAME, $options ) ) {
				$this->options = null; // Refresh options array.
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Sets an option or an array of options.
		 *
		 * @param string|array $option        The option name OR an array of option => value pairs.
		 * @param string       $value         If only option name was passed, this is the value we should be setting.
		 * @param bool         $apply_filters Whether to apply filters to the individual option(s) we are saving.
		 * @return bool True on success, false on failure.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function setOption( $option, $value = '', $apply_filters = true ) {
			if ( is_string( $option ) )
				$option = array( $option => $value );

			if ( !is_array( $option ) )
				return false;

			$current_options = $this->getOptions();

			if ( $apply_filters ) {
				// Make sure to filter each option before we save it.
				foreach ( $option as $name => $value ) {
					$option[$name] = apply_filters( 'muut_save_option-' . $name, $value );
				}
			}

			return $this->setOptions( wp_parse_args( $option, $current_options ) );
		}

		/**
		 * Saves the settings specified on the Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveSettings() {
			if ( isset( $_POST['muut_settings_save'] )
				&& $_POST['muut_settings_save'] == 'true'
				&& check_admin_referer( 'muut_settings_save', 'muut_settings_nonce' )
			) {
				$old_options = $this->getOptions();

				$settings = $this->settingsValidate( $_POST['setting'] );

				// Save all the options by passing an array into setOption.
				// Save all the options by passing an array into setOption.
				if ( $this->setOption( $settings ) || $old_options == $this->getOptions() ) {
					// Display success notice if they were updated or matched the previous settings.
					$this->queueAdminNotice( 'updated', __( 'Settings successfully saved.', 'muut' ) );
				} else {
					// Display error if the settings failed to save.
					$this->queueAdminNotice( 'error', __( 'Failed to save settings.', 'muut' ) );
				}
			}
		}

		/**
		 * Deals with settings-specific validation functionality.
		 *
		 * @param array $settings an array of key => value pairs that define what settings are being changed to.
		 * @return array A modified array defining the settings after validation.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function settingsValidate( $settings ) {

			$boolean_settings = apply_filters( 'muut_boolean_settings', array(
				'replace_comments',
				'use_threaded_commenting',
				'override_all_comments',
				'show_comments_in_forum',
				'is_threaded_default',
				'show_online_default',
				'allow_uploads_default',
				'subscription_use_sso',
				'disable_proxy_rewrites',
			) );

			foreach ( $boolean_settings as $boolean_setting ) {
				$settings[$boolean_setting] = isset( $settings[$boolean_setting]) ? $settings[$boolean_setting] : '0';
			}

			if ( ( isset( $settings['remote_forum_name'] ) && $settings['remote_forum_name'] != $this->getOption( 'remote_forum_name' ) )
				|| ( isset( $settings['disable_proxy_rewrites'] ) && $settings['disable_proxy_rewrites'] != $this->getOption( 'disable_proxy_rewrites' ) ) ) {
				flush_rewrite_rules( true );
			}

			// Add depth to the settings that need to be further in on the actual settings array.
			$settings['forum_page_defaults']['is_threaded'] = $settings['is_threaded_default'];
			$settings['forum_page_defaults']['show_online'] = $settings['show_online_default'];
			$settings['forum_page_defaults']['allow_uploads'] = $settings['allow_uploads_default'];
			unset( $settings['is_threaded_default'] );
			unset( $settings['show_online_default'] );
			unset( $settings['allow_uploads_default'] );

			// If the Secret Key setting does not get submitted (i.e. is disabled), make sure to erase its value.
			$settings['subscription_secret_key'] = isset( $settings['subscription_secret_key']) ? $settings['subscription_secret_key'] : '';

			return apply_filters( 'muut_settings_validated', $settings );
		}

		/**
		 * Creates the Muut admin menu section and menu items.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function createAdminMenuItems() {
			add_menu_page(
				__( 'Muut', 'muut' ),
				__( 'Muut', 'muut' ),
				'manage_options',
				self::SLUG,
				array( $this, 'renderAdminMainPage' )
			);

			add_submenu_page(
				self::SLUG,
				__( 'Muut Settings', 'muut' ),
				__( 'Settings', 'muut' ),
				'manage_options',
				'muut_settings',
				array( $this, 'renderAdminSettingsPage' )
			);

			add_submenu_page(
				self::SLUG,
				__( 'Muut Custom Navigation', 'muut' ),
				__( 'Custom Navigation', 'muut' ),
				'edit_pages',
				'muut_custom_navigation',
				array( $this, 'renderAdminCustomNavPage' )
			);
		}

		/**
		 * Renders the Muut main menu page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminMainPage() {
			// Confirm that this function is being called from a valid callback.
			if ( $this->adminBail( 'toplevel_page_' . self::SLUG ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-main.php' );
		}

		/**
		 * Renders the Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminSettingsPage() {
			// Confirm that this function is being called from a valid callback.
			if ( $this->adminBail( self::SLUG . '_page_muut_settings' ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-settings.php');
		}

		/**
		 * Renders the Muut Custom Navigation page, which interacts with the Muut Forum Channel CPT.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminCustomNavPage() {
			// Confirm that this function is being called from a valid callback.
			if ( $this->adminBail( self::SLUG . '_page_muut_custom_navigation' ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-custom-navigation.php');
		}

		/**
		 *  Saves a forum page's information.
		 *
		 * @param int $post_id The id of the post (page) being saved.
		 * @param WP_Post $post The post (page) object that is being saved.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveForumPage( $post_id, $post ) {
			if ( get_post_type( $post ) != 'page' )
				return;

			if ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '0' ) {
				Muut_Forum_Page_Utility::removeAsForumPage( $post_id );
			} elseif ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '1' ) {
				Muut_Forum_Page_Utility::setAsForumPage( $post_id );
			}

			if ( isset( $_POST['muut_is_forum_page'] )
				&& $_POST['muut_is_forum_page'] == '1'
				&& ( ( isset( $_POST['muut_forum_is_home'] )
					&& $_POST['muut_forum_is_home'] != '1' )
					|| !isset( $_POST['muut_forum_is_home'] )
				) ) {
				if ( !isset( $_POST['muut_forum_path'] )
					|| $_POST['muut_forum_path'] == '' ) {
					// If no path is saved yet, let's generate one and save it.
					// An already saved path CANNOT be changed (even though the post slug and ancestry can).
					if ( !Muut_Forum_Page_Utility::getRemoteForumPath( $post_id, true ) ) {
						$path = $post->post_name;
						$ancestors = get_post_ancestors( $post );

						foreach ( $ancestors as $ancestor ) {
							if ( Muut_Forum_Page_Utility::isForumPage( $ancestor ) && Muut_Forum_Page_Utility::getForumPageOption( $ancestor, 'is_threaded', false ) ) {
								$path = Muut_Forum_Page_Utility::getRemoteForumPath( $ancestor ) . '/' . $path;
							}
						}
						Muut_Forum_Page_Utility::setForumPageRemotePath( $post_id, $path );
					}
				} elseif ( isset( $_POST['muut_forum_path'] ) && $_POST['muut_forum_path'] != '' ) {
					$path = $_POST['muut_forum_path'];
					if ( substr( $path, 0, 1 ) == '/' ) {
						$path = substr( $path, 1 );
					}
					if ( substr( $path, -1 ) == '/' ) {
						$path = substr( $path, 0, -1 );
					}
					$path = implode('/', array_map('rawurlencode', explode( '/', $path ) ) );
					Muut_Forum_Page_Utility::setForumPageRemotePath( $post_id, $path );
				}
			}

			if ( isset( $_POST['muut_forum_is_home'] ) && $_POST['muut_forum_is_home'] == '1' ) {
				$this->setOption( 'forum_home_id', $post_id );
				Muut_Forum_Page_Utility::setForumPageRemotePath( $post_id, '' );
			} elseif ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '1' && ( $this->getOption( 'forum_home_id', false ) == $post_id || $this->getOption( 'forum_home_id', false ) === false ) ) {
				$this->setOption( 'forum_home_id', '0' );
			}

			if ( isset( $_POST['muut_forum_is_threaded'] ) && $_POST['muut_forum_is_threaded'] == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'is_threaded', '1' );
			} elseif ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '1' && Muut_Forum_Page_Utility::getForumPageOption( $post_id, 'is_threaded', '0' ) == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'is_threaded', '0' );
			}

			if ( isset( $_POST['muut_forum_show_online'] ) && $_POST['muut_forum_show_online'] == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'show_online', '1' );
			} elseif ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '1' && Muut_Forum_Page_Utility::getForumPageOption( $post_id, 'show_online', '0' ) == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'show_online', '0' );
			}

			if ( isset( $_POST['muut_forum_allow_uploads'] ) && $_POST['muut_forum_allow_uploads'] == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'allow_uploads', '1' );
			} elseif ( isset( $_POST['muut_is_forum_page'] ) && $_POST['muut_is_forum_page'] == '1' && Muut_Forum_Page_Utility::getForumPageOption( $post_id, 'allow_uploads', '0' ) == '1' ) {
				Muut_Forum_Page_Utility::setForumPageOption( $post_id, 'allow_uploads', '0' );
			}
		}

		/**
		 * Filters the permalink for post channel pages. This makes sure that the link goes to the proper muut forum page with the hashbang.
		 *
		 * @param string $permalink The current permalink.
		 * @param WP_Post $post The channel WP_Post (custom post type).
		 * @return string The modified permalink.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function filterForumChannelsPermalinks( $permalink, $post ) {
			if ( Muut_Forum_Channel_Utility::FORUMCHANNEL_POSTTYPE == get_post_type( $post ) ) {
				$forum_home_id = $this->getOption( 'forum_home_id', false );
				if ( $forum_home_id ) {
					$base_link = get_permalink( $forum_home_id );

					$permalink = $base_link . '#!/' . Muut_Forum_Channel_Utility::getRemotePath( $post->ID );
				}
			}
			return $permalink;
		}

		/**
		 * Adds the proper body class(es) for admin depending on the admin page being loaded.
		 *
		 * @param string $classes The current string of body classes.
		 * @return string The modified array of body classes
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addAdminBodyClasses( $classes ) {
			$screen = get_current_screen();

			if ( $screen->id == self::SLUG . '_page_muut_settings' ) {
				$classes .= 'muut_settings';
			}

			return $classes;
		}

		/**
		 * Adds the proper body class(es) depending on the page being loaded.
		 *
		 * @param array $classes The current array of body classes.
		 * @return array The modified array of body classes
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addBodyClasses( $classes ) {
			if ( $this->needsMuutResources( get_the_ID() ) ) {
				$classes[] = 'muut-enabled';
				$classes[] = 'has-muut';
				$classes[] = 'has-moot';
				if ( $this->getOption( 'forum_home_id', '0' ) == get_the_ID() ) {
					$classes[] = 'muut-forum-home';
					$channel_headers = Muut_Forum_Channel_Utility::getForumChannelHeaders();
					if ( !empty( $channel_headers ) ) {
						$classes[] = 'muut-custom-nav';
					}
				}
			}

			return $classes;
		}

		/**
		 * Checks if the a page needs to include the frontend Muut resources.
		 *
		 * @param int $page_id The page we are checking.
		 * @return bool Whether we need the frontend Muut resources or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function needsMuutResources( $page_id = null ) {
			if ( is_null( $page_id ) ) {
				$page_id = get_the_ID();
			}

			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			$return = false;
			if ( Muut_Forum_Page_Utility::isForumPage( get_the_ID() )
				|| ( $this->getOption( 'replace_comments' ) && is_singular() && comments_open() ) ) {
				$return = true;
			}

			return apply_filters( 'muut_requires_muut_resources', $return, $page_id );
		}

		/**
		 * Filters the content on a page if it is a standalone forum page to include the embed.
		 *
		 * @param string $content The current content string.
		 * @return string The content.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function filterForumPageContent( $content ) {
			global $post;


			if ( $post->post_type == 'page' && Muut_Forum_Page_Utility::isForumPage( $post->ID ) ) {
				$content = Muut_Forum_Page_Utility::forumPageEmbedMarkup( $post->ID, false );
			}

			return $content;
		}

		/**
		 * Check if the user has edited nav menus before and, if so, add the hook to update his meta data to NOT hide
		 * the forum channels metabox.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function checkIfUserEditedMenus() {
			if ( get_user_meta( get_current_user_id(), 'metaboxhidden_nav-menus', true ) == '' ) {
				add_action( 'admin_head', array( $this, 'updateHiddenMetaboxUserDefault' ) );
			}
		}

		/**
		 * Updates the user's default hidden metabox default so that forum channels are displayed by default.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function updateHiddenMetaboxUserDefault() {
			$current_meta = get_user_meta( get_current_user_id(), 'metaboxhidden_nav-menus', true );

			$index = array_search( 'add-' . Muut_Forum_Channel_Utility::FORUMCHANNEL_POSTTYPE, $current_meta );

			if ( $index ) {
				unset( $current_meta[$index] );
				update_user_meta( get_current_user_id(), 'metaboxhidden_nav-menus', $current_meta );
			}
		}

	}


	/**
	 * Can be used to return the Muut instance.
	 *
	 * @return Muut
	 * @author Paul Hughes
	 * @since  3.0
	 */
	function muut() {
		return Muut::instance();
	}
}
<?php
/**
 * The class that is responsible for Adding the contextual help menu to the applicable admin screens.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Contextual_Help' ) ) {

	/**
	 * Muut Admin Contextual Help class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Contextual_Help
	{
		/**
		 * @static
		 * @property Muut_Admin_Contextual_Help The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Contextual_Help The instance.
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
		 * @return Muut_Admin_Contextual_Help
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the admin contextual help menu functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			// Add the contextual help tab items for the main Muut settings page.
			add_action( 'load-toplevel_page_muut', array( $this, 'addMuutSettingsHelp' ) );

			// Add the contextual help tab items for the post editor (the Muut meta box).
			add_action( 'load-post.php', array( $this, 'addMuutPostEditorHelp' ) );
			add_action( 'load-post-new.php', array( $this, 'addMuutPostEditorHelp' ) );
			add_action( 'load-widgets.php', array( $this, 'addMuutWidgetsHelpAction' ) );

		}

		/**
		 * The method for adding all filters regarding the admin contextual help menu.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {

		}

		/**
		 * Adds the contextual help tab to the main Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutSettingsHelp() {
			$screen = get_current_screen();

			$help_overview_tab_args = array(
				'id' => 'muut_settings_help_overview_tab',
				'title' => __( 'Overview', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpOverviewTabContent' ),
			);

			$help_general_tab_args = array(
				'id' => 'muut_settings_help_general_tab',
				'title' => __( 'General', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpGeneralTabContent' ),
			);

			$help_commenting_tab_args = array(
				'id' => 'muut_settings_help_commenting_tab',
				'title' => __( 'Commenting', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpCommentingTabContent' ),
			);

			$help_seo_tab_args = array(
				'id' => 'muut_settings_help_seo_tab',
				'title' => __( 'SEO', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpSeoTabContent' ),
			);

			$help_signedsetup_tab_args = array(
				'id' => 'muut_settings_help_signedsetup_tab',
				'title' => __( 'Signed Setup', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpSignedsetupTabContent' ),
			);

			$help_fedid_tab_args = array(
				'id' => 'muut_settings_help_fedid_tab',
				'title' => __( 'Federated Identities', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpFedidTabContent' ),
			);

			$help_webhooks_tab_args = array(
				'id' => 'muut_settings_help_webhooks_tab',
				'title' => __( 'Webhooks', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpWebhooksTabContent' ),
			);

			// Add the help tabs for the Muut Settings page.
			$screen->add_help_tab( $help_overview_tab_args );

			$screen->add_help_tab( $help_general_tab_args );

			$screen->add_help_tab( $help_commenting_tab_args );

			$screen->add_help_tab( $help_seo_tab_args );

			$screen->add_help_tab( $help_signedsetup_tab_args );

			$screen->add_help_tab( $help_fedid_tab_args );

			$screen->add_help_tab( $help_webhooks_tab_args );

			// Set the "For More Information" sidebar up as well.
			ob_start();
				include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-sidebar.php' );
			$settings_help_sidebar_content = ob_get_clean();

			$screen->set_help_sidebar( $settings_help_sidebar_content );
		}

		/**
		 * Renders the content for the Overview help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpOverviewTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-overview.php' );
		}

		/**
		 * Renders the content for the General help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpGeneralTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-general.php' );
		}

		/**
		 * Renders the content for the Commenting help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpCommentingTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-commenting.php' );
		}

		/**
		 * Renders the content for the SEO help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpSeoTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-seo.php' );
		}

		/**
		 * Renders the content for the Signed Setup help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2.3
		 */
		public function renderSettingsHelpSignedsetupTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-signedsetup.php' );
		}

		/**
		 * Renders the content for the Federated Identities help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpFedidTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-fedid.php' );
		}

		/**
		 * Renders the content for the Webhooks help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpWebhooksTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-webhooks.php' );
		}

		/**
		 * Adds the contextual help tab items to the post/page editing page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutPostEditorHelp() {
			$screen = get_current_screen();

			$tabs = Muut_Admin_Post_Editor::instance()->getMetaBoxTabsForCurrentPostType();

			$help_tabs = array(
				'commenting-tab' => array(
					'id' => 'muut_post_editor_help_commenting_tab',
					'title' => __( 'Muut Commenting', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpCommentingTabContent' ),
				),
				'channel-tab' => array(
					'id' => 'muut_post_editor_help_channel_tab',
					'title' => __( 'Muut Channel', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpChannelTabContent' ),
				),
				'forum-tab' => array(
					'id' => 'muut_post_editor_help_forum_tab',
					'title' => __( 'Muut Forum', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpForumTabContent' ),
				),
			);

			// Add the help tabs for the post editor, depending on which tabs are visible/being used.
			foreach ( $tabs as $tab ) {
				$screen->add_help_tab( $help_tabs[$tab['name']] );
			}
		}

		/**
		 * Renders the content for the Commenting-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpCommentingTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-commenting.php' );
		}

		/**
		 * Renders the content for the Channel-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpChannelTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-channel.php' );
		}

		/**
		 * Renders the content for the Forum-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpForumTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-forum.php' );
		}

		/**
		 * We only want to add the widgets help on the admin widgets page, but it has to get added a bit later (after the default ones).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutWidgetsHelpAction() {
			add_action('admin_head', array( $this, 'addMuutWidgetsHelp' ) );
		}

		/**
		 * Adds the contextual help tab items to the Widgets page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutWidgetsHelp() {
			$screen = get_current_screen();

			$help_muut_widgets_tab_args = array(
				'id' => 'muut_widgets_help_tab',
				'title' => __( 'Muut Widgets', 'muut' ),
				'callback' => array( $this, 'renderMuutWidgetsHelpTabContent' ),
			);

			// Add the help tabs for the Muut Settings page.
			$screen->add_help_tab( $help_muut_widgets_tab_args );
		}

		/**
		 * Renders the content for the Commenting-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderMuutWidgetsHelpTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-muut-widgets.php' );
		}
	}
}
<?php
/**
 * The class that is responsible for all the webhooks functionality.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Webhooks' ) ) {

	/**
	 * Muut Webhooks class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Webhooks
	{
		const DEFAULT_ENDPOINT_SLUG = 'muut-webhooks';

		/**
		 * @static
		 * @property Muut_Webhooks The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Webhooks The instance.
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
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
		 * @return Muut_Webhooks
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addActions() {
			add_action( 'admin_init', array( $this, 'addWebhooksEndpoint' ) );

			add_action( 'template_redirect', array( $this, 'receiveRequest' ) );
		}

		/**
		 * The method for adding all filters.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addFilters() {

		}

		/**
		 * Adds the webhooks endpoint for receiving Muut HTTP requests.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addWebhooksEndpoint() {
			add_rewrite_rule( '^' . $this->getEndpointSlug() . '/?', 'index.php?muut_action=webhooks', 'top' );
		}

		/**
		 * Begins request processing if the muut_action query var is passed
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function receiveRequest() {
			if ( get_query_var( 'muut_action' ) == 'webhooks' ) {
				exit( print_r( $_SERVER, true ) );
			}
		}


		/**
		 * Gets and filters the endpoint slug.
		 *
		 * @return string The endpoint slug.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getEndpointSlug() {
			return apply_filters( 'muut_webhooks_endpoint_slug', self::DEFAULT_ENDPOINT_SLUG );
		}

		/**
		 * Checks if webhooks are activated.
		 *
		 * @return bool Whether webhooks are activated or not.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function isWebhooksActivated() {
			return muut()->getOption( 'use_webhooks' );
		}
	}
}
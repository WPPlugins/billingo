<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Billingo_WooCommerce' ) ) {
	class Billingo_WooCommerce {
		public function __construct() {
			require_once BILLINGO_PATH . 'includes/billingo-admin-page.php';
			require_once BILLINGO_PATH . 'includes/billingo-generate-invoice.php';
			require_once BILLINGO_PATH . 'includes/billingo-add-bulk-icon.php';

			add_filter( 'plugin_action_links_' . BILLINGO_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
		}

		public function add_settings_link( $links ) {
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=billingo' ) ) . '">Settings</a>';

			return $links;
		}
	}
}

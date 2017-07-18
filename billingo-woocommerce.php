<?php
/*
Plugin Name: Billingo WooCommerce
Description: Billingo számlakiállítás WooCommerce-hez
Plugin URI: https://billingo.hu
Author: Billingo
Author URI: https://billingo.hu
Version: v1.0.0
License: MIT
*/

// TODO: plugin info

defined( 'ABSPATH' ) or die();

define( 'BILLINGO_PATH', plugin_dir_path( __FILE__ ) );
define( 'BILLINGO_URL', plugin_dir_url( __FILE__ ) );
define( 'BILLINGO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'BILLINGO_LOGENTRIES_TOKEN', '7ad7c4ab-1a92-4226-b702-16532c8e6f26');
define( 'BILLINGO_VERSION', 'v1.0.0' );

require_once BILLINGO_PATH . 'lib/autoload.php';
require_once BILLINGO_PATH . 'includes/class-billingo-woocommerce.php';

new Billingo_WooCommerce();

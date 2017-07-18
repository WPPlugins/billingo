<?php

defined( 'ABSPATH' ) or die();

use Billingo\Plugin\Helper;
use Logentries\LeLogger;

if ( ! class_exists( 'Billingo_Generate_Invoice' ) ) {

	class Billingo_Generate_Invoice {
		private $billingo;

		public function __construct() {
			$action = get_option( 'wc_settings_tab_billingo_invoice_generation_action' );
			switch ( $action ) {
				case 'ordercomplete':
					add_action( 'woocommerce_order_status_completed', array( $this, 'generate_invoice' ) );
					break;
				case 'thankyou':
					add_action( 'woocommerce_thankyou', array( $this, 'generate_invoice' ) );
					break;
				default:
					add_action( 'woocommerce_order_status_completed', array( $this, 'generate_invoice' ) );
					break;
			}
		}

		public function generate_invoice( $order_id ) {
			$private_key          = get_option( 'wc_settings_tab_billingo_private_key' );
			$public_key           = get_option( 'wc_settings_tab_billingo_public_key' );
			$invoice_block        = intval( get_option( 'wc_settings_tab_billingo_invoice_block' ) );
			$invoice_due_date     = get_option( 'wc_settings_tab_billingo_invoice_due_date' );
			$invoice_due_date     = $invoice_due_date !== false ? $invoice_due_date : 8;
			$send_email           = get_option( 'wc_settings_tab_billingo_invoice_send_email' );
			$use_gross_unit_price = get_option( 'wc_settings_tab_billingo_use_gross_unit_price' ) === 'yes' ? true : false;
			$billingo_custom_vat  = get_option( 'wc_settings_tab_billingo_vat' );
            $debug_mode           = get_option( 'wc_settings_debug_mode_enabled' ) === 'yes' ? true : false;

			if ( false === $private_key || false === $public_key ) {
				return false;
			}

			$logger = false;
            if ($debug_mode === true) {
                $logger = new Logentries\LeLogger(BILLINGO_LOGENTRIES_TOKEN, LOG_DEBUG);
            }
			$invoice_helper = new Helper\Helper( $public_key, $private_key, $send_email, $logger);
            $invoice_helper->debug_info = 'version: ' . BILLINGO_VERSION;

			$order       = new WC_Order( $order_id );
			$order_items = $order->get_items();

			$client = array(
				'name'            => $order->billing_company ?: $order->billing_first_name . ' ' . $order->billing_last_name,
				'email'           => $order->billing_email,
				'billing_address' => array(
					'street_name' => trim( $order->billing_address_1 . $order->billing_address_2 ),
					'street_type' => '',
					'house_nr'    => '',
					'city'        => $order->billing_city,
					'postcode'    => $order->billing_postcode,
					'country'     => $order->billing_country
				)
			);

			$invoice = array(
				'fulfillment_date'   => current_time( 'Y-m-d' ),
				'due_date'           => date( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . "+$invoice_due_date days" ) ),
				'payment_method'     => $order->payment_method,
				'comment'            => '',
				'template_lang_code' => 'hu',
				'electronic_invoice' => 1,
				'currency'           => $order->get_order_currency(),
				'block_uid'          => $invoice_block,
				'type'               => 3,
			);

			$billingo_invoice_items = array();
			foreach ( $order_items as $item ) {
				$billingo_unit_price = $use_gross_unit_price ? 'gross_unit_price' : 'net_unit_price';
				$unit_price          = floatval( $item['line_total'] );
				$name                = $item['name'];
				$qty                 = intval( $item['qty'] );
				$vat_rate            = $use_gross_unit_price ? intval( $billingo_custom_vat ) : false;

				if ( false === $vat_rate ) {
					$tax_rates = WC_Tax::get_rates( $item['tax_class'] );
					$tax       = array_shift( $tax_rates );
					$vat_rate  = floatval( $tax['rate'] );
				}

				if ( $qty > 1 ) {
					$product_id = $item['variation_id'] == '0' ? $item['product_id'] : $item['variation_id'];
					$product    = new WC_Product_Factory();
					$product    = $product->get_product( $product_id );
					$unit_price = floatval( $product->get_price_excluding_tax() );
				}

				$invoice_item                         = array(
					'description' => $name,
					'vat_rate'    => $vat_rate,
					'unit'        => '',
					'qty'         => $qty,
				);
				$invoice_item[ $billingo_unit_price ] = $unit_price;

				$billingo_invoice_items[] = $invoice_item;
			}
			$shipping = $order->get_shipping_methods();
			foreach ( $shipping as $s ) {
				$name  = $s['name'];
				$price = floatval( $s['cost'] );
				$tax   = 0;
				if ( $price !== 0.0 ) {
					foreach ( unserialize( $s['taxes'] ) as $val ) {
						$tax += floatval( $val );
					}
					$billingo_invoice_items[] = array(
						'net_unit_price' => $price,
						'description'    => $name,
						'vat_rate'       => $tax / $price,
						'unit'           => '',
						'qty'            => 1,
					);
				}
			}

			$invoice['items'] = $billingo_invoice_items;

			$invoice_id = $invoice_helper->createInvoice( $client, $invoice );

			if ( is_array( $invoice_id ) ) {
				$order->add_order_note( substr( $invoice_id['error'], 0, 300 ) );

				return false;
			}

			update_post_meta( $order_id, '_billingo_invoice_created', 1, true );

			$download_link = $invoice_helper->getDownloadLink( $invoice_id );

			if ( is_array( $download_link ) ) {
				$order->add_order_note( substr( $download_link['error'], 0, 300 ) );

				return false;
			}

			update_post_meta( $order_id, '_billingo_invoice_download_link', $download_link, true );

			if ( $send_email === 'yes' ) {
				$send_email_reponse = $invoice_helper->sendEmail( $invoice_id );

				if ( is_array( $send_email_reponse ) ) {
					$order->add_order_note( substr( $send_email_response['error'], 0, 300 ) );

					return false;
				}
			}

			$order->add_order_note( 'Successful Billingo invoice generation!' );

			return true;
		}
	}

	new Billingo_Generate_Invoice();
}

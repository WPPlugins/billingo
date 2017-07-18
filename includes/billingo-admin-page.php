<?php

defined( 'ABSPATH' ) or die();

class WC_Settings_Tab_Billingo {
	/**
	 * Bootstraps the class and hooks required actions & filters.
	 */
	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_billingo', __CLASS__ . '::settings_tab' );
		add_action( 'woocommerce_update_options_billingo', __CLASS__ . '::update_settings' );
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
	 *
	 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
	 */
	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['billingo'] = __( 'Billingo', 'woocommerce-settings-tab-billingo' );

		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_settings()
	 */
	public static function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses self::get_settings()
	 */
	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}

	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public static function get_settings() {
		$settings = array(
			'section_title'             => array(
				'name' => __( 'Számlázási beállítások', 'woocommerce-settings-tab-billingo' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_settings_tab_billingo_section_title'
			),
			'public_key'                => array(
				'name' => __( 'Publikus kulcs', 'woocommerce-settings-tab-billingo' ),
				'type' => 'text',
				'desc' => __( 'Billingo publikus kulcs', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_public_key'
			),
			'private_key'               => array(
				'name' => __( 'Privát kulcs', 'woocommerce-settings-tab-billingo' ),
				'type' => 'text',
				'desc' => __( 'Billingo privát kulcs', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_private_key'
			),
			'invoice_due_date'          => array(
				'name' => __( 'Számla fizetési határidő', 'woocommerce-settings-tab-billingo' ),
				'type' => 'text',
				'desc' => __( 'Számla kiállításától számított fizetési határidő napban.', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_invoice_due_date'
			),
			'invoice_block'             => array(
				'name' => __( 'Számlatömb', 'woocommerce-settings-tab-billingo' ),
				'type' => 'text',
				'desc' => __( 'Számlatömb ID', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_invoice_block'
			),
			'invoice_generation_action' => array(
				'name'     => __( 'Számla generálás esemény', 'woocommerce-settings-tab-billingo' ),
				'desc'     => __( 'Milyen esemény következzen be, hogy számla generálódjon', 'woocommerce-settings-tab-billingo' ),
				'id'       => 'wc_settings_tab_billingo_invoice_generation_action',
				'css'      => 'min-width:150px;',
				'default'  => 'ordercomplete',
				'type'     => 'select',
				'options'  => array(
					'ordercomplete' => __( 'Rendelés teljesítve', 'woocommerce-settings-tab-billingo' ),
					'thankyou'      => __( 'Thank you page', 'woocommerce-settings-tab-billingo' )
				),
				'desc_tip' => true,

			),
			'invoice_send_email'        => array(
				'name'    => __( 'Számla küldése e-mail-ben', 'woocommerce-settings-tab-billingo' ),
				'desc'    => __( 'Automatikus számlaküldés Billingo-n keresztül a számla kiállítása után', 'woocommerce-settings-tab-billingo' ),
				'id'      => 'wc_settings_tab_billingo_invoice_send_email',
				'default' => 'checked',
				'type'    => 'checkbox'
			),
			'use_gross_unit_price'      => array(
				'name' => __( 'Nettó árakat használok', 'woocommerce-settings-tab-billingo' ),
				'desc' => __( 'A termékek árai nettóban vannak megadva a webáruházban', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_use_gross_unit_price',
				'type' => 'checkbox'
			),
			'vat'                       => array(
				'name' => __( 'Áfakulcs (%)', 'woocommerce-settings-tab-billingo' ),
				'type' => 'text',
				'desc' => __( 'Termékek áfája. Csak akkor érvényesül, ha a webáruház nettó árakat használ.', 'woocommerce-settings-tab-billingo' ),
				'id'   => 'wc_settings_tab_billingo_vat'
			),
            'debug'                      => array(
                'name' => __( 'Debug', 'woocommerce-settings-tab-billingo' ),
                'desc' => __( 'Csak hibajelenség kivizsgálására használatos!', 'woocommerce-settings-tab-billingo' ),
                'id'   => 'wc_settings_debug_mode_enabled',
                'type' => 'checkbox'
            ),
			'section_end'               => array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_tab_billingo_section_end'
			)
		);

		return apply_filters( 'wc_settings_tab_billingo_settings', $settings );
	}
}

WC_Settings_Tab_Billingo::init();

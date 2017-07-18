<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Billingo_Add_Bulk_Icon' ) ) {
	class Billingo_Add_Bulk_Icon {
		public function __construct() {
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_icon' ) );
		}

		public function add_icon( $order ) {
			if ( $download_link = get_post_meta( $order->id, '_billingo_invoice_download_link', true ) ):
				?>
				<a href="<?php echo $download_link ?>" class="button tips" data-tip="Billingo számla letöltése.">
					<img style="width:13px;height:15px;" src="<?php echo BILLINGO_URL . 'assets/bill.svg'; ?>"
					     alt="" width="16" height="16">
				</a>
				<?php
			endif;
		}
	}

	new Billingo_Add_Bulk_Icon();
}

<?php
/**
 * Thankyou page
 * Template override for minimal design + Continue Shopping button.
 *
 * @package WooCommerce/Templates
 * @version 9.0.0 (adjust if WooCommerce updates)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Helper: get shop page url
$continue_url = wc_get_page_permalink( 'shop' );
if ( ! $continue_url ) {
    $continue_url = home_url( '/' );
}
?>
<div class="woocommerce-order">
	<?php if ( $order ) : ?>
		<?php if ( $order->has_status( 'failed' ) ) : ?>
			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'woocommerce' ); ?></p>
			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button account"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>
		<?php else : ?>
			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ); ?></p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>
		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ); ?></p>
	<?php endif; ?>
</div>
<div class="thankyou-actions">
	<a class="button button-continue-shopping" href="<?php echo esc_url( $continue_url ); ?>">&larr; <?php esc_html_e( 'Kontynuuj zakupy', 'woocommerce' ); ?></a>
</div>
<style>
/* Scoped minimal styling for continue shopping below notice */
body.woocommerce-order-received .thankyou-actions { margin: 18px 0 26px; }
body.woocommerce-order-received .thankyou-actions .button-continue-shopping { background: var(--accent-color); color:#fff; border-radius:6px; padding:10px 22px; font-weight:600; font-size:14px; text-decoration:none; display:inline-block; }
body.woocommerce-order-received .thankyou-actions .button-continue-shopping:hover { background:#e55a00; }
</style>

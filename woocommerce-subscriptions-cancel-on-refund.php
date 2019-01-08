<?php
/*
 * Plugin Name: WooCommerce Subscriptions - Cancel on Refund
 * Plugin URI: https://github.com/Prospress/woocommerce-subscriptions-cancel-on-refund/
 * Description: Cancel a subscription when its parent order or last renewal order is fully refunded.
 * Author: Prospress Inc.
 * Author URI: https://prospress.com/
 * License: GPLv3
 * Version: 1.0.0
 * Version: 1.0.0
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.5
 *
 * GitHub Plugin URI: Prospress/woocommerce-subscriptions-cancel-on-refund
 * GitHub Branch: master
 *
 * Copyright 2019 Prospress, Inc.  (email : freedoms@prospress.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		WooCommerce Subscriptions - Cancel on Refund
 * @author		Prospress Inc.
 * @since		1.0
 */

require_once( 'includes/class-pp-dependencies.php' );

if ( false === PP_Dependencies::is_woocommerce_active( '3.0' ) ) {
	PP_Dependencies::enqueue_admin_notice( 'WooCommerce Subscriptions - Cancel on Refund', 'WooCommerce', '3.0' );
	return;
}

if ( false === PP_Dependencies::is_subscriptions_active( '2.1' ) ) {
	PP_Dependencies::enqueue_admin_notice( 'WooCommerce Subscriptions - Cancel on Refund', 'WooCommerce Subscriptions', '2.1' );
	return;
}

function pp_maybe_cancel_subscription_on_full_refund( $order ) {

	if ( ! is_object( $order ) ) {
		$order = wc_get_order( $order );
	}

	if ( wcs_order_contains_subscription( $order, array( 'parent', 'renewal' ) ) ) {

		$subscriptions = wcs_get_subscriptions_for_order( wcs_get_objects_property( $order, 'id' ), array( 'order_type' => array( 'parent', 'renewal' ) ) );

		foreach ( $subscriptions as $subscription ) {
			$latest_order = $subscription->get_last_order();

			if ( wcs_get_objects_property( $order, 'id' ) == $latest_order && $subscription->can_be_updated_to( 'cancelled' ) ) {
				// translators: $1: opening link tag, $2: order number, $3: closing link tag
				$subscription->update_status( 'cancelled', wp_kses( sprintf( __( 'Subscription cancelled for refunded order %1$s#%2$s%3$s.', 'woocommerce-subscriptions' ), sprintf( '<a href="%s">', esc_url( wcs_get_edit_post_link( wcs_get_objects_property( $order, 'id' ) ) ) ), $order->get_order_number(), '</a>' ), array( 'a' => array( 'href' => true ) ) ) );
			}
		}
	}
}
add_action( 'woocommerce_order_fully_refunded', 'pp_maybe_cancel_subscription_on_full_refund' );

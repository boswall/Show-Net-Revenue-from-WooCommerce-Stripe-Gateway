<?php
/**
* Plugin Name:       Show Net Revenue from WooCommerce Stripe Gateway
* Plugin URI:        https://github.com/boswall/show-woostripe-revenue
* Description:       Shows the Stripe fee and net revenue for Woocommerce order listings in the admin area.
* Version:           1.0.0
* Author:            Matt Rose
* Author URI:        http://glaikit.co.uk/
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       show-woostripe-revenue
* Domain Path:       /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// Fiters and Actions needed to make everything work.
add_action( 'plugins_loaded', 'show_woostripe_revenue_load_plugin_textdomain' );
add_action( 'woocommerce_admin_order_totals_after_refunded', 'show_woostripe_revenue_admin_order_totals_after_refunded', 10, 1 );
add_filter( 'manage_shop_order_posts_columns', 'show_woostripe_revenue_shop_order_columns' , 100 );
add_action( 'manage_shop_order_posts_custom_column', 'show_woostripe_revenue_render_shop_order_columns', 2 );
add_filter( 'manage_edit-shop_order_sortable_columns', 'show_woostripe_revenue_shop_order_sortable_columns' );
add_filter( 'request', 'show_woostripe_revenue_request_query' );

/**
* i18n because I can.
*/
function show_woostripe_revenue_load_plugin_textdomain() {
    load_plugin_textdomain( 'show-woostripe-revenue', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
* Show fees and net revenue in order totals.
* @param  int $order_id
*/
function show_woostripe_revenue_admin_order_totals_after_refunded( $order_id ) {
  if ( $fees = get_post_meta( get_the_ID(), 'Stripe Fee' )[0] ) {
    ?>

    <tr>
      <td class="label"><?php echo __('Stripe Fee', 'show-woostripe-revenue'); ?>:</td>
      <td width="1%"></td>
      <td class="total">-<?php echo wc_price( $fees ); ?></td>
    </tr>

  <?php
  }

  if ( $net = get_post_meta( get_the_ID(), 'Net Revenue From Stripe' )[0] ) {
    ?>

    <tr>
      <td class="label"><?php echo __('Net Revenue From Stripe', 'show-woostripe-revenue'); ?>:</td>
      <td width="1%"></td>
      <td class="total"><?php echo wc_price( $net ); ?></td>
    </tr>

  <?php
  }
};

/**
* Define custom columns for orders.
* @param  array $columns
* @return array
*/
function show_woostripe_revenue_shop_order_columns( $columns ) {
  $columns['order_stripefees'] = 	__('Stripe Fees', 'show-woostripe-revenue');
  $columns['order_stripenet'] = 	__('Stripe Net Total', 'show-woostripe-revenue');
  return $columns;
}

/**
* Output custom columns for coupons.
* @param string $column
*/
function show_woostripe_revenue_render_shop_order_columns( $column ) {
  if ( $column == 'order_stripefees' ) {
    if ( ! $fees = get_post_meta( get_the_ID(), 'Stripe Fee' )[0] ) {
      echo '<span class="na">–</span>';
      return;
    }
    echo wc_price( $fees );
  }
  if ( $column == 'order_stripenet' ) {
    if ( ! $net = get_post_meta( get_the_ID(), 'Net Revenue From Stripe' )[0] ) {
      echo '<span class="na">–</span>';
      return;
    }
    echo wc_price( $net );
  }
}

/**
* Make columns sortable
* @param  array $columns
* @return array
*/
function show_woostripe_revenue_shop_order_sortable_columns( $columns ) {
  $columns['order_stripefees'] = 'order_stripefees';
  $columns['order_stripenet'] = 'order_stripenet';
  return $columns;
}

/**
* Filters and sorting handler.
* @param  array $vars
* @return array
*/
function show_woostripe_revenue_request_query( $vars ) {
  global $typenow;
  if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ) ) ) {
    if ( isset( $vars['orderby'] ) ) {
      if ( 'order_stripefees' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
          'meta_key'  => 'Stripe Fee',
          'orderby'   => 'meta_value_num',
        ) );
      }
      if ( 'order_stripenet' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
          'meta_key'  => 'Net Revenue From Stripe',
          'orderby'   => 'meta_value_num',
        ) );
      }
    }

  }
  return $vars;
}

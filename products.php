<?php
/**
 * @package  Products
 */

/**
 * Plugin Name: Products
 * Description: A plugin to manage products
 * Version: 1.0
 * Author: Niranjan C K
 * Text Domain: products
 * Domain Path: /languages
 */

require_once plugin_dir_path( __FILE__ ) . 'shortcodes/shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-dashboard.php';


/**
 * Create constant value of base file.
 */
if ( ! defined( 'PRODUCT_BASEFILE' ) ) {
	define( 'PRODUCT_BASEFILE', plugin_dir_url( __FILE__ ) );
}
defined( 'ABSPATH' ) || die( 'not working' );



$dashboard = new Dashboard();

/**
 * Activation hook
 */
register_activation_hook( __FILE__, array( $dashboard, 'activate' ) );

/**
 * Uninstall hook
 */
register_uninstall_hook( __FILE__, 'uninstall' );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, array( $dashboard, 'deactivate' ) );


add_action( 'init', array( $dashboard, 'product_post_type' ), 10 );
add_action( 'init', array( $dashboard, 'product_taxonomy' ), 10 );
add_action( 'add_meta_boxes', array( $dashboard, 'price_meta_box' ) );
add_action( 'save_post', array( $dashboard, 'save_price_meta_box_data' ), 10 );
add_action( 'init', array( $dashboard, 'handle_cart_now' ) );
add_action( 'wp_logout', array( $dashboard, 'product_user_logout' ) );
add_action( 'init', array( $dashboard, 'buy_products' ) );


// Shortcodes.
add_shortcode( 'products_grid', 'take_all_products' );
add_shortcode( 'purchased_products', 'purchased_products' );
add_shortcode( 'cart', 'cart_products' );

<?php
/**
 *
 * @package Products
 *
 * Plugin Name: Products
 * Description: A plugin to manage products
 * Version: 1.0
 * Author: Niranjan C K
 * Text Domain: products
 * Domain Path: /languages
 */

/*All hooks */
require_once plugin_dir_path( __FILE__ ) . 'functions/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'shortcodes/shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-dashboard.php';


/**
 * Create constant value of base file.
 */
if ( ! defined( 'PRODUCT_BASEFILE' ) ) {
	define( 'PRODUCT_BASEFILE', plugin_dir_url( __FILE__ ) );
}
defined( 'ABSPATH' ) || die( 'not working' );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'activate' );
$dashboard = new Dashboard();
$dashboard->register();
/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, 'deactivate' );

/**
 * Uninstall hook
 */
register_uninstall_hook( __FILE__, 'uninstall' );

add_action( 'init', 'product_post_type', 10 );
add_action( 'init', 'product_taxonomy', 10 );
add_action( 'add_meta_boxes', 'price_meta_box' );
add_action( 'save_post', 'save_price_meta_box_data', 10 );

add_shortcode( 'products_grid', 'take_all_products' );
add_shortcode( 'purchased_products', 'purchased_products' );

<?php
/**
 * @package Products
 */

/**
 * Dashboard class
 */
class Dashboard {

	/**
	 * Reguster custom post type
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue style
	 */
	public function enqueue_script() {
		wp_enqueue_style( 'products-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', array(), '1.0.0' );
		wp_enqueue_style( 'products-style', PRODUCT_BASEFILE . 'includes/assets/style.css', array(), '1.0.0' );
		wp_enqueue_script( 'products-script', PRODUCT_BASEFILE . 'includes/assets/script.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'products-jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array( 'jquery' ), '1.0.0', true );
	}
}

<?php
/**
 * @package Products
 */

/**
 * Manage products class
 */
class Manageproduct {

	/**
	 * Create table
	 */
	public static function create_table() {
		global $wpdb;
		// set the default character set and collation for the table.
		$charset_collate = $wpdb->get_charset_collate();
		// Check that the table does not already exist before continuing.
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}product` (
        id bigint(50) NOT NULL AUTO_INCREMENT,
        post_id int(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        product_name bigint(20),
        product_price varchar(100),
        product_image varchar(100),
        product_description varchar(100),
        PRIMARY KEY (id)
        ) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		$is_error = empty( $wpdb->last_error );
		return $is_error;
	}
}

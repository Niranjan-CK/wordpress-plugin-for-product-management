<?php
/**
 * @package Products
 */

/**
 * Activation hook
 */
function activate() {
	$post_details = array(
		'post_title'   => 'Shopping',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);
	wp_insert_post( $post_details );
	$product_page = array(
		'post_title'   => 'Purchased Products',
		'post_content' => '[purchased_products]',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);
	wp_insert_post( $product_page );

	flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
function deactivate() {
	flush_rewrite_rules();
}

/**
 * Create custom post type.
 * Register custom post type.
 */
function product_post_type() {

	$labels = array(
		'name'               => 'Products',
		'singular_name'      => 'Product',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Product',
		'edit_item'          => 'Edit Product',
		'all_items'          => 'All Products',
		'new_item'           => 'New Product',
		'view_item'          => 'View Product',
		'search_items'       => 'Search Products',
		'not_found'          => 'No Products Found',
		'parent_item_colon'  => 'Parent Product:',
		'menu_name'          => 'Products',
		'not_found_in_trash' => 'No Products Found in Trash',
	);
	$args   = array(
		'labels'        => $labels,
		'public'        => true,
		'menu_icon'     => 'dashicons-products',
		'menu_position' => 5,
		'rewrite'       => array( 'slug' => 'products' ),
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
	);
	register_post_type( 'article', $args );
}

/**
 * Create custom taxonomy.
 * Register custom taxonomy.
 */
function product_taxonomy() {

	$labels = array(
		'name'              => 'Product Categories',
		'singular_name'     => 'Product Category',
		'search_items'      => 'Search Product Categories',
		'all_items'         => 'All Product Categories',
		'parent_item'       => 'Parent Product Category',
		'parent_item_colon' => 'Parent Product Category:',
		'edit_item'         => 'Edit Product Category',
		'update_item'       => 'Update Product Category',
		'add_new_item'      => 'Add New Product Category',
		'new_item_name'     => 'New Product Category Name',
		'menu_name'         => 'Product Categories',
	);
	$args   = array(
		'labels'       => $labels,
		'hierarchical' => true,
	);
	register_taxonomy( 'product_category', 'article', $args );
}

/**
 * Add price meta box
 */
function price_meta_box() {
	add_meta_box(
		'price_meta_box', // Meta box ID.
		'Price', // Meta box title.
		'price_meta_box_callback', // Meta box callback function.
		'article', // The custom post type parameter 1.
		'normal', // Meta box location in the edit screen.
		'high' // Meta box priority.
	);
}

/**
 * Meta box callback function
 *
 * @param WP_Post $post Current post object.
 */
function price_meta_box_callback( $post ) {
	wp_nonce_field( 'price-nonce', 'meta-box-nonce' );
	?>
	<div class="container" >
		<?php
			// Retrieve the saved meta values.
			$prices      = array();
			$prices      = get_post_meta( $post->ID, 'price', true );
			$count_price = 0;
		if ( ! empty( $prices ) ) {
			if ( count( $prices ) > 0 ) {
				foreach ( $prices as $price ) {
					if ( isset( $price['variable'] ) || isset( $price['price'] ) ) {
						printf( '<p class="navbar"><span><input type="text" name="price[%1$s][variable]" value="%2$s"/>  <input type="text" name="price[%1$s][amount]" value="%3$s"/></span><button class="btn btn-primary remove">%4$s</button></p>', esc_html( $count_price ), esc_html( $price['variable'] ), esc_html( $price['amount'] ), esc_html( 'Remove' ) );
						++$count_price;
					}
				}
			}
		}

		?>
		<span id="priceField"></span>
		<button class="addPrice btn btn-primary"><?php echo esc_html( 'Add' ); ?></button>
		<script>
			var $ = jQuery.noConflict();
			$(document).ready(function() {
				var count = <?php echo esc_html( $count_price ); ?>;
				$(".addPrice").click(function(){
					count = count + 1;
					$('#priceField').append('<p><input type="text" name="price['+count+'][variable]" value="" placeholder="type"/>  <input type="number" name="price['+count+'][amount]" value="" placeholder="amount"/><btn class="btn btn-primary remove">Remove</btn></p>');
					return false;
				})
				$(document).on('click', '.remove', function() {
					$(this).parent().remove();
				});

				
				
			});
		</script>


	</div>
	<?php
}

/**
 * Save meta box data
 *
 * @param int $post_id Post ID.
 */
function save_price_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['meta-box-nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['meta-box-nonce'], 'price-nonce' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'article' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
	}

	// Make sure that it is set.
	if ( ! isset( $_POST['price'] ) ) {
		return;
	}

	// Sanitize user input.
	$price_data = $_POST['price'];

	// Update the meta field in the database.
	update_post_meta( $post_id, 'price', $price_data );
}

?>

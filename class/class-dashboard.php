<?php
/**
 * @package Products
 */

/**
 * Dashboard class
 */
class Dashboard {

	/**
	 * Constructor
	 * activate the plugin
	 */
	public function __construct() {
		$this->register();
	}

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

	/**
	 * Activation hook
	 */
	public function activate() {
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

		$cart_page = array(
			'post_title'   => 'Cart',
			'post_content' => '[cart]',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'page',
		);
		wp_insert_post( $cart_page );

		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create custom post type.
	 * Register custom post type.
	 */
	public function product_post_type() {

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
	public function product_taxonomy() {

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
	public function price_meta_box() {
		add_meta_box(
			'price_meta_box', // Meta box ID.
			'Price', // Meta box title.
			array( $this, 'price_meta_box_callback' ), // Meta box callback function.
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
	public function price_meta_box_callback( $post ) {
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
			wp_localize_script(
				'products-script',
				'price',
				array(
					'count' => $count_price,
				)
			);

			?>
			<span id="priceField"></span>
			<button class="addPrice btn btn-primary" ><?php echo esc_html( 'Add' ); ?></button>


		</div>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_price_meta_box_data( $post_id ) {
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

	/**
	 * Logout user
	 */
	public function product_user_logout() {
		setcookie( 'cart', '', time() - 3600, '/' );
		wp_safe_redirect( home_url() );
		exit;
	}

	/**
	 * Handle cart Now button click.
	 */
	public function handle_cart_now() {

		if ( ! isset( $_POST['cart-now-nonce'] ) || ! wp_verify_nonce( $_POST['cart-now-nonce'], 'cart-now-nonce' ) ) {

			return;
		}

		if ( isset( $_POST['cart_product'] ) ) {

			$product_id = $_POST['cart_product'] . '.' . $_POST['price'];
			if ( ! is_user_logged_in() ) {
				// Redirect the user to the login page.
				$this->add_to_cart( $product_id );

			} else {
				$this->add_to_cart( $product_id );
				// Add the product to the list of purchased items associated with the user.
				// Your code to add the product to the user's purchased items goes here.
				$user_id         = get_current_user_id();
				$purchased_items = get_user_meta( $user_id, 'cart', true );

				if ( ! is_array( $purchased_items ) ) {
					$purchased_items = array();
				}

				$purchased_items[] = $product_id;
				update_user_meta( $user_id, 'cart', $purchased_items );

				// Redirect the user to a success page or any other desired action.
				$single_post_url = get_permalink( $product_id ); // Replace $post_id with the ID of the newly added post.
				// Redirect the user to the single post page.
				wp_safe_redirect( $single_post_url );

			}
		}
	}

	/**
	 * Add to cart
	 *
	 * @param int $product_id product id.
	 */
	public function add_to_cart( $product_id ) {
		if ( isset( $_COOKIE['cart'] ) ) {

			$guest_cart = $_COOKIE['cart'];

			$append = $guest_cart . ',' . $product_id;

			setcookie( 'cart', $append, time() + ( 86400 * 30 ), '/' ); // Cookie valid for 30 days.

		} elseif ( ini_get( 'session.use_cookies' ) ) {

				$guest_cart = $product_id;
				setcookie( 'cart', $guest_cart, time() + ( 86400 * 30 ), '/' ); // Cookie valid for 30 days.

		}
	}

	/**
	 * Purchase product
	 */
	public function buy_products() {

		if ( ! isset( $_POST['product_nonce'] ) || ! wp_verify_nonce( $_POST['product_nonce'], 'product_nonce' ) ) {

			return;
		}
		if ( isset( $_POST['buy_product'] ) ) {

			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( login_url() );
			} else {
				$user_id          = get_current_user_id();
				$purchase_product = get_user_meta( $user_id, 'purchase_product', true );
				if ( ! is_array( $purchase_product ) ) {
					$purchase_product = array();
				}
				$purchase_product[] = $_POST['buy_product'];
				update_user_meta( $user_id, 'purchase_product', $purchase_product );
			}
		}
		if ( isset( $_POST['remove_from_cart'] ) ) {
			// remove product from cart using product id.

			$postid           = $_POST['remove_from_cart'];
			$user_id          = get_current_user_id();
			$purchase_product = get_user_meta( $user_id, 'cart', true );
			$index            = array_search( $postid, $purchase_product, true );
			if ( false !== $index ) {
				unset( $purchase_product[ $index ] );

			}
			setcookie( 'cart', $postid, time() - 3600, '/' );

			update_user_meta( $user_id, 'cart', $purchase_product );
		}
	}
}

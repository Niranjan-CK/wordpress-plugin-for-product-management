<?php
/**
 * @package Products
 */

/**
 * All shortcodes are registered here
 */
function take_all_products() {

	$products = new WP_Query( array( 'post_type' => 'article' ) );

	if ( $products->have_posts() ) {
		?>
		<div class="container">
		<div class="row row-cols-3">
		<?php

		while ( $products->have_posts() ) {
			$products->the_post();
			?>

			<div class="card m-1 p-5" style="width:18rem; border-radius:10px;">
			<p > <?php echo esc_html( the_post_thumbnail() ); ?> </p>
		
			<p class="mt-5"><?php echo esc_html( the_title( '<h3>', '</h3>' ) ); ?></p>
			
			<p><?php echo esc_html( the_content() ); ?>
			
			<p><?php echo esc_html( the_terms( get_the_ID(), 'product_category', 'Category: ', ', ', '' ) ); ?></p>
			<?php

			$price_array = get_post_meta( get_the_ID(), 'price', true );
			product_price( $price_array );
			// Check if $price_array is an array.

			?>

		
			<form method="post">
				<?php wp_nonce_field( 'buy-now-nonce', 'buy-now-nonce' ); ?>
				<button class="btn " name="buy_product" value="<?php echo get_the_ID(); ?>">Buy Now</button>
			</form>
			</div>
			<?php
		}
		?>
		</div>
		</div>
		<?php
	}
}

/**
 *
 *  Fetch all product price
 *
 * @param array $price_array array of price.
 */
function product_price( $price_array ) {
	if ( is_array( $price_array ) ) {
		foreach ( $price_array as $price_item ) {
			$n = 0;
			foreach ( $price_item as $variable => $amount ) {
				if ( 0 !== $n % 2 ) {
					echo ' - ';
				}
				echo esc_html( $amount );
				++$n;
			}
			echo '<br/>';
		}
	}
}

// Handle Buy Now button click.
add_action( 'init', 'handle_buy_now' );

/**
 * Handle Buy Now button click.
 */
function handle_buy_now() {
	if ( ! isset( $_POST['buy-now-nonce'] ) || ! wp_verify_nonce( $_POST['buy-now-nonce'], 'buy-now-nonce' ) ) {
		return;
	}
	if ( isset( $_POST['buy_product'] ) ) {

		if ( ! is_user_logged_in() ) {
			// Redirect the user to the login page.
			wp_safe_redirect( wp_login_url() );
			exit;
		} else {
			$product_id = $_POST['buy_product'];
			// // Add the product to the list of purchased items associated with the user.
			// // Your code to add the product to the user's purchased items goes here.
			$user_id         = get_current_user_id();
			$purchased_items = get_user_meta( $user_id, 'purchased_items', true );

			if ( ! is_array( $purchased_items ) ) {
				$purchased_items = array();
			}

			$purchased_items[] = $product_id;

			update_user_meta( $user_id, 'purchased_items', $purchased_items );

			// Redirect the user to a success page or any other desired action.
			$single_post_url = get_permalink( $product_id ); // Replace $post_id with the ID of the newly added post.

			// Redirect the user to the single post page.
			wp_safe_redirect( $single_post_url );

		}
	}
}

/**
 *  Take all purchased products by current user from user meta
 */
function purchased_products() {
	$user_id         = get_current_user_id();
	$purchased_items = get_user_meta( $user_id, 'purchased_items', true );

	if ( ! empty( $purchased_items ) && is_array( $purchased_items ) ) {
		?>

		<div class="conatiner row row-cols-33 ">
		<?php

		foreach ( $purchased_items as $product_id ) {
			// Perform actions with each purchased product.
			// For example, retrieve product information or display a list of purchased items.

			$product = get_post( $product_id );
			?>
			<div class="card m-1 p-5" style="width:18rem; border-radius:10px;">
			
			<p><?php echo get_the_post_thumbnail( $product_id ); ?></p>
			
			<h3><?php echo esc_html( $product->post_title ); ?> </h3>
			
			<p> <?php echo esc_html( $product->post_content ); ?></p>
			
			<p>Category: <?php get_the_term_list( $product_id, 'product_category', '', ', ', '' ); ?></p>
			
			<?php
			$price_array = get_post_meta( $product_id, 'price', true );
			product_price( $price_array );
			?>
			<a href="<?php echo esc_attr( wp_get_attachment_url( get_post_thumbnail_id( $product_id ) ) ); ?>" download id="download">
				<button class="btn" name="download" value="<?php echo esc_attr( $product_id ); ?>">Download</button>
			</a>
		</div>
			<?php

		}
	} else {
		?>
		<p>You have not purchased any products yet.</p>
		<?php
	}
}

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
					$product_id = get_the_ID();
					?>

						
						<div class="card m-1 p-5" style="width:18rem; border-radius:10px;">
							<p style="height:130px; width:150px;"> <?php echo esc_html( the_post_thumbnail() ); ?> </p>
						
							<p class="mt-5"> <?php echo esc_html( the_title( '<h3>', '</h3>' ) ); ?></p>
							
							<p> <?php echo esc_html( the_content() ); ?> </p>
							
							<p> <?php echo esc_html( the_terms( get_the_ID(), 'product_category', 'Category: ', ', ', '' ) ); ?> </p>
							<form method="post">
							<?php
								$price_array = get_post_meta( get_the_ID(), 'price', true );
							if ( is_array( $price_array ) ) {
								$variables = 0;
								?>
									
									<Select name="price">
									<?php
									foreach ( $price_array as $price_item ) {
										?>
											<option value="<?php echo esc_html( $variables ); ?>">
											<?php
											if ( isset( $price_item['variable'] ) ) {
												echo esc_html( $price_item['variable'] ) . '-' . esc_html( $price_item['amount'] );
											}
											?>
											<?php
											++$variables;
									}
									?>
									</Select>
									<?php
							}
							?>
								
							<?php wp_nonce_field( 'cart-now-nonce', 'cart-now-nonce' ); ?>
								<button class="btn " name="cart_product" value="<?php echo get_the_ID(); ?>">Cart</button>
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
 *  Take all purchased products by current user from user meta
 */
function purchased_products() {
	$user_id         = get_current_user_id();
	$purchased_items = get_user_meta( $user_id, 'purchase_product', true );

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
			
				<p class="mb-5" style="width:150px;heigth:100px;"><?php echo get_the_post_thumbnail( $product_id ); ?></p>
				
				<h3 class="mt-5"><?php echo esc_html( $product->post_title ); ?> </h3>
				
				<p> <?php echo esc_html( $product->post_content ); ?></p>
				
				<p>Category: <?php get_the_term_list( $product_id, 'product_category', '', ', ', '' ); ?></p>
				
				<?php
				$price_array = get_post_meta( $product_id, 'price', true );
				if ( is_array( $price_array ) ) {
					foreach ( $price_array as $price_item ) {
						echo esc_html( $price_item['variable'] ) . '-' . esc_html( $price_item['amount'] );
						echo '<br>';
					}
				}
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

/**
 * Cart products
 */
function cart_products() {
	$user_id    = get_current_user_id();
	$cart_items = get_user_meta( $user_id, 'cart', true );
	if ( is_user_logged_in() ) {

		if ( isset( $_COOKIE['cart'] ) ) {

			$cart     = $_COOKIE['cart'];
			$products = explode( ',', $cart );

			$previous_products = get_user_meta( $user_id, 'cart', true );
			if ( ! is_array( $previous_products ) ) {
				$previous_products = array();
			}

			foreach ( $products as $product ) {
				if ( ! in_array( $product, $previous_products, true ) ) {
					$previous_products[] = $product;
					update_user_meta( $user_id, 'cart', $previous_products );
				}
			}
		}

		if ( ! empty( $cart_items ) && is_array( $cart_items ) ) {

			foreach ( $cart_items as $product ) {
				fetch_cart_products( $product );

			}
		}
	} elseif ( isset( $_COOKIE['cart'] ) ) {

			$cart     = $_COOKIE['cart'];
			$products = explode( ',', $cart );

		foreach ( $products as $product ) {
			fetch_cart_products( $product );
		}
	}
}

/**
 * Fetch cart products
 *
 * @param int $product product id.
 */
function fetch_cart_products( $product ) {
	$product    = explode( '.', $product );
	$product_id = $product[0];
	$price_id   = intval( $product[1] );
	$product    = get_post( $product_id );
	$price      = get_post_meta( $product_id, 'price', true );
	$count      = 0;
	foreach ( $price as $key => $value ) {
		if ( $count === $price_id ) {
			$price    = $value['amount'];
			$variable = $value['variable'];

			show_cart_products( $product_id, $price_id, $price, $variable );

		}
		++$count;
	}
}

/**
 * Show cart products
 *
 * @param int $product_id product id.
 * @param int $price_id price id.
 * @param int $price price.
 * @param int $variable variable.
 */
function show_cart_products( $product_id, $price_id, $price, $variable ) {
	$product = get_post( $product_id );
	?>
	<div class="conatiner row row-cols-33 ">
	<div class="card m-1 p-5" style="width:20rem; border-radius:10px;">
			
		<p class="mb-5" style="width:150px;height:150px;"><?php echo get_the_post_thumbnail( $product_id ); ?></p>
		
		<h3 class="mt-5"><?php echo esc_html( $product->post_title ); ?> </h3>
		
		<p> <?php echo esc_html( $product->post_content ); ?></p>
		
		<p>Category: <?php get_the_term_list( $product_id, 'product_category', '', ', ', '' ); ?></p>
		
		<p> Price : <?php echo esc_html( $variable . ':' . $price ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'product_nonce', 'product_nonce' ); ?>
			<button class="btn" name="remove_from_cart" value="<?php echo esc_html( $product_id . '.' . $price_id ); ?>">Remove</button>
			<button class="btn" name="buy_product" value="<?php echo esc_html( $product_id ); ?>">Buy Now</button>
		</form>
	</div>
	</div>
	<?php
}




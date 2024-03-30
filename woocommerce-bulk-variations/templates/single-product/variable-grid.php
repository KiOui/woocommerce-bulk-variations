<?php
/**
 * Variable grid class.
 *
 * This class renders the grid used by WooCommerce Bulk Variations.
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WBV_ABSPATH . 'includes/WbvMatrix.php';

$wbv_matrix = wbv_get_matrix();

if ( is_null( $wbv_matrix ) ) {
	return;
}

do_action( 'wbv_before_add_to_cart_form' );

?>

<div id="matrix_form" style="display: none">
	<div class="summary">
		<?php woocommerce_template_single_title(); ?>
		<?php woocommerce_template_single_price(); ?>
		<?php woocommerce_template_single_excerpt(); ?>
	</div>
	<form id="wholesale_form" class="bulk_variations_form cart matrix" method="POST" enctype="multipart/form-data">
		<table id="matrix_form_table">
			<thead>
				<tr>
					<th></th>
					<?php foreach ( $wbv_matrix->get_formatted_column_keys() as $column ) : ?>
						<th><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php for ( $row_index = 0; $row_index < $wbv_matrix->get_row_length(); $row_index++ ) : ?>

					<!-- Info boxes -->
					<?php for ( $column_index = 0; $column_index < $wbv_matrix->get_column_length(); $column_index++ ) : ?>
						<tr style="display: none;" class="info_box" id=<?php echo esc_attr( 'matrix_form_input_' . ( $row_index * $wbv_matrix->get_column_length() + $column_index ) . '_info' ); ?>>
							<td colspan="<?php echo esc_attr( $wbv_matrix->get_column_length() + 1 ); ?>">
								<?php $variation = $wbv_matrix->get_matrix_index( $row_index, $column_index ); ?>
								<?php if ( ! is_null( $variation ) ) : ?>
									<div class="matrix_form_input_info" >
										<div class="images">
											<?php echo $variation->get_image(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function. ?>
										</div>
										<div class="summary">
											<p itemprop="name" class="product_title entry-title"><?php echo esc_html( $variation->get_title() ); ?></p>
											<?php echo $variation->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function. ?>
											<ul>
												<li><?php echo esc_html( wc_attribute_label( $wbv_matrix->get_row_attribute() ) ); ?>: <?php echo esc_html( ucwords( $wbv_matrix->get_row_key( $row_index ) ) ); ?></li>
												<li><?php echo esc_html( wc_attribute_label( $wbv_matrix->get_column_attribute() ) ); ?>: <?php echo esc_html( ucwords( $wbv_matrix->get_column_key( $column_index ) ) ); ?></li>

												<?php if ( $variation->get_sku() ) : ?>
													<li><?php echo esc_html( $variation->get_sku() ); ?></li>
												<?php endif; ?>

											</ul>
											<?php echo $variation->get_availability()['availability'] ? wc_get_stock_html( $variation ) : '<p class="stock">&nbsp;</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function. ?>
										</div>
									</div>
								<?php endif; ?>
							</td>
						</tr>
					<?php endfor; ?>

					<!-- Input fields -->
					<tr data-index="<?php echo esc_attr( $row_index ); ?>">
						<td class="row-label"><?php echo esc_html( ucwords( $wbv_matrix->get_row_key( $row_index ) ) ); ?></td>
						<?php for ( $column_index = 0; $column_index < $wbv_matrix->get_column_length(); $column_index++ ) : ?>
							<td>
								<?php $variation = $wbv_matrix->get_matrix_index( $row_index, $column_index ); ?>
								<?php if ( ! is_null( $variation ) && ( false === $variation->get_manage_stock() || $variation->get_stock_quantity() > 0 || $variation->backorders_allowed() ) ) : ?>
									<input
											data-row="<?php echo esc_attr( $row_index ); ?>"
											data-column="<?php echo esc_attr( $column_index ); ?>"
											class="number matrix_form_input"
											type="number"
											id="matrix_form_input_<?php echo esc_attr( $row_index * $wbv_matrix->get_column_length() + $column_index ); ?>"
											name="order_info[<?php echo esc_attr( $row_index * $wbv_matrix->get_column_length() + $column_index ); ?>][quantity]"
											min="0"
											<?php if ( true === $variation->get_manage_stock() && ! $variation->backorders_allowed() ) : ?>
												max="<?php echo esc_attr( $variation->get_stock_quantity() ); ?>"
											<?php endif; ?>
										/>
									<input type="hidden" name="order_info[<?php echo esc_attr( $row_index * $wbv_matrix->get_column_length() + $column_index ); ?>][variation_id]" value="<?php echo esc_attr( $variation->get_id() ); ?>" />
								<?php endif; ?>
							</td>
						<?php endfor; ?>
					</tr>
				<?php endfor; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="<?php echo esc_attr( $wbv_matrix->get_column_length() + 1 ); ?>">
						<input class="button btn_back_to_product" type="button" value="<?php echo esc_attr( __( '&larr; Product Page', 'woocommerce-bulk-variations' ) ); ?>" />
						<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_attr( apply_filters( 'single_add_to_cart_text', __( 'Add to cart', 'woocommerce-bulk-variations' ), 'variable' ) ); ?></button>
					</td>
				</tr>
			</tfoot>
		</table>
		<div>
			<input type="hidden" name="woocommerce-bulk-variations-submission" value="true" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
			<?php wp_nonce_field( 'wbv_submit_bulk_form' ); ?>
		</div>
	</form>
</div>

<?php

do_action( 'wbv_after_add_to_cart_form' );

<?php
global $post;
global $product;

$table = wbv_get_bulk_variations_matrix_data($post);

$grid = $table['grid'];
$row_attribute = $table['row_attribute'];
$column_attribute = $table['column_attribute'];
$grid_rows = $table['row_keys'];
$grid_columns = $table['column_keys'];

$current_row_index = 0;
$current_column_index = 0;
$current_cell_index = 0;
$info_boxes = array();

do_action('wbv_before_add_to_cart_form');

?>

<div id="matrix_form">
	<div class="summary">
		<?php woocommerce_template_single_title(); ?>
		<?php woocommerce_template_single_price(); ?>
		<?php woocommerce_template_single_excerpt(); ?>
	</div>
	<form id="wholesale_form" class="bulk_varations_form cart matrix" method="POST" enctype="multipart/form-data">
		<table id="matrix_form_table">
			<thead>
				<tr>
					<th></th>
					<?php foreach ($grid_columns as $column) : ?>
						<th><?php echo wbv_get_attribute_title($column_attribute, $column, $product) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
            <tbody>
                <?php foreach ($grid as $row => $columns): ?>
                    <?php $current_column_index = 0 ?>
                    <tr data-index="<?php echo $current_row_index; ?>">
                        <td class="row-label"><?php echo wbv_get_attribute_title($row_attribute, $row, $product); ?></td>
                        <?php foreach ($columns as $key => $field_data): ?>
                            <td>
                                <?php if ($field_data != null) : ?>
                                    <?php
                                        $variation = new WC_Product_variation( $field_data['variation_id'] );
                                        $stock_msg = $variation->get_stock_quantity() ? __("Only " . $variation->get_stock_quantity() . "available", "woocommerce-bulk-variations") : __("Currently unavailable", "woocommerce-bulk-variations");
                                    ?>

                                    <input
                                            data-manage-stock="<?php echo $variation->get_manage_stock(); ?>"
                                            data-purchasable="<?php echo $variation->is_purchasable() ? '1' : '0'; ?>"
                                            data-instock="<?php echo $variation->is_in_stock() ? '1' : '0'; ?>"
                                            data-backorders="<?php echo $variation->backorders_allowed() ? '1' : '0'; ?>"
                                            data-max="<?php echo $variation->get_stock_quantity(); ?>"
                                            data-price="<?php echo $variation->get_price(); ?>"
                                            data-stock-message="<?php echo $stock_msg; ?>"
                                            data-column="<?php echo $current_column_index; ?>"
                                            class="number qty_input"
                                            type="number"
                                            id="qty_input_<?php echo $current_cell_index; ?>"
                                            name="order_info[<?php echo $current_cell_index; ?>][quantity]"
                                        />
	                                <?php
	                                    $info_boxes['qty_input_' . $current_cell_index . '_info'] = array($row_attribute => $row, $column_attribute => $key, 'variation_data' => $field_data, 'variation' => $variation);
	                                ?>
                                    <input type="hidden" name="order_info[<?php echo $current_cell_index; ?>][variation_id]" value="<?php echo $field_data['variation_id']; ?>" />
                                    <input type="hidden" name="order_info[<?php echo $current_cell_index; ?>][variation_data][attribute_<?php echo $column_attribute; ?>]" value="<?php echo $key; ?>" />
                                    <input type="hidden" name="order_info[<?php echo $current_cell_index; ?>][variation_data][attribute_<?php echo $row_attribute; ?>]" value="<?php echo $row; ?>" />
                                <?php endif; ?>
                            </td>
                            <?php
                                $current_cell_index++;
                                $current_column_index++;
                            ?>
                        <?php endforeach; ?>
                    </tr>
                    <?php
                        $current_row_index++;
                    ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?php echo $current_column_index + 1; ?>">
                        <button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters( 'single_add_to_cart_text', __( 'Add to cart', 'woocommerce-bulk-variations' ), 'variable' ); ?></button>
                    </td>
                </tr>
            </tfoot>
		</table>
        <div>
            <input type="hidden" name="add-variations-to-cart" value="true" />
            <input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
        </div>
	</form>

	<?php if (!wbv_single_product_form_enabled($post)): ?>
        <input class="button btn-back-to-single" type="button" value="<?php __( '&larr; Singular Order Form', 'woocommerce-bulk-variations' ); ?>" />
	<?php else : ?>
        <input class="button btn-back-to-product" type="button" value="<?php __( '&larr Product Page', 'woocommerce-bulk-variations' ); ?>" />
	<?php endif; ?>

    <div id="matrix_form_info_holder" style="display:none;" >
		<?php foreach ( $info_boxes as $key => $field_data ) : ?>
			<?php $variation = $field_data['variation']; ?>
            <div id="<?php echo $key; ?>" class="qty_input_info" >
                <div class="images">
					<?php echo $variation->get_image(); ?>
                </div>
                <div class="summary">
                    <p itemprop="name" class="product_title entry-title"><?php echo $variation->get_title(); ?></p>
					<?php echo $variation->get_price_html(); ?>
                    <ul>
                        <li><?php echo wc_attribute_label($row_attribute); ?>: <?php echo wbv_get_attribute_title($row_attribute, $field_data[$row_attribute], $product); ?></li>
                        <li><?php echo wc_attribute_label($column_attribute); ?>: <?php echo wbv_get_attribute_title($column_attribute, $field_data[$column_attribute], $product); ?></li>

						<?php if ($variation->get_sku()): ?>
                            <li><?php echo $field_data['variation_data']['sku']; ?></li>
						<?php endif; ?>

                    </ul>


					<?php echo $field_data['variation_data']['availability_html'] ?: '<p class="stock">&nbsp;</p>'; ?>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
</div>

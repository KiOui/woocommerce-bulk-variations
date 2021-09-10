<?php

if (!defined('ABSPATH')) {
    exit;
}

global $post;
global $product;

include_once WBV_ABSPATH . 'includes/wbv-matrix.php';
$wbv_matrix = new WBVMatrix($product);

do_action('wbv_before_add_to_cart_form');

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
					<?php foreach ($wbv_matrix->get_column_keys() as $column) : ?>
						<th><?php echo ucwords($column) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
            <tbody>
                <?php for ($row_index = 0; $row_index < $wbv_matrix->get_row_length(); $row_index++): ?>

                    <!-- Info boxes -->
                    <?php for ($column_index = 0; $column_index < $wbv_matrix->get_column_length(); $column_index++): ?>
                        <tr style="display: none;" class="info_box" id=<?php echo "qty_input_" . ($row_index*$wbv_matrix->get_column_length() + $column_index) . "_info"; ?>>
                            <td colspan="<?php echo($wbv_matrix->get_column_length() + 1); ?>">
                                <?php $variation = $wbv_matrix->get_matrix_index($row_index, $column_index) ?>
                                <?php if ($variation != null): ?>
                                    <div class="qty_input_info" >
                                        <div class="images">
                                            <?php echo $variation->get_image(); ?>
                                        </div>
                                        <div class="summary">
                                            <p itemprop="name" class="product_title entry-title"><?php echo $variation->get_title(); ?></p>
                                            <?php echo $variation->get_price_html(); ?>
                                            <ul>
                                                <li><?php echo wc_attribute_label($wbv_matrix->get_row_attribute()); ?>: <?php echo ucwords($wbv_matrix->get_row_key($row_index)); ?></li>
                                                <li><?php echo wc_attribute_label($wbv_matrix->get_column_attribute()); ?>: <?php echo ucwords($wbv_matrix->get_column_key($column_index)); ?></li>

                                                <?php if ($variation->get_sku()): ?>
                                                    <li><?php echo $variation->get_sku() ?></li>
                                                <?php endif; ?>

                                            </ul>
                                            <?php echo $variation->get_availability()['availability'] ? wc_get_stock_html($variation) : '<p class="stock">&nbsp;</p>'; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>

                    <!-- Input fields -->
                    <tr data-index="<?php echo $row_index; ?>">
                        <td class="row-label"><?php echo ucwords($wbv_matrix->get_row_key($row_index)); ?></td>
                        <?php for ($column_index = 0; $column_index < $wbv_matrix->get_column_length(); $column_index++): ?>
                            <td>
	                            <?php $variation = $wbv_matrix->get_matrix_index($row_index, $column_index) ?>
                                <?php if ($variation != null && ($variation->get_stock_quantity() > 0 || $variation->backorders_allowed())) : ?>
                                    <input
                                            data-row="<?php echo $row_index; ?>"
                                            data-column="<?php echo $column_index; ?>"
                                            class="number qty_input"
                                            type="number"
                                            id="qty_input_<?php echo($row_index*$wbv_matrix->get_column_length() + $column_index); ?>"
                                            name="order_info[<?php echo($row_index*$wbv_matrix->get_column_length() + $column_index); ?>][quantity]"
                                            min="0"
                                            <?php if ($variation->get_manage_stock() && !$variation->backorders_allowed()): ?>
                                                max="<?php echo $variation->get_stock_quantity(); ?>"
                                            <?php endif; ?>
                                        />
                                    <input type="hidden" name="order_info[<?php echo($row_index*$wbv_matrix->get_column_length() + $column_index); ?>][variation_id]" value="<?php echo $variation->get_id(); ?>" />
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?php echo $wbv_matrix->get_column_length() + 1; ?>">
                        <input class="button btn-back-to-product" type="button" value="<?php echo __('&larr; Product Page', 'woocommerce-bulk-variations'); ?>" />
                        <button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __('Add to cart', 'woocommerce-bulk-variations'), 'variable'); ?></button>
                    </td>
                </tr>
            </tfoot>
		</table>
        <div>
            <input type="hidden" name="add-variations-to-cart" value="true" />
            <input type="hidden" name="product_id" value="<?php echo esc_attr($post->ID); ?>" />
        </div>
	</form>
</div>

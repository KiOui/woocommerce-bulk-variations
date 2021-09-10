<?php

/**
 * WBV Bulk Form
 *
 * @class WBVBulkForm
 */
if (!class_exists("WBVBulkForm")) {
    class WBVBulkForm
    {
        /**
         * Enqueue scripts and styles.
         */
        public function add_scripts_and_styles()
        {
            wp_enqueue_script('woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/js/bulk-variations.js', array( 'jquery' ));
            wp_enqueue_style('woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/css/variable-grid.css');
        }

        /**
         * Add actions and filters.
         */
        public function actions_and_filters()
        {
            add_action('woocommerce_before_single_product', array($this, 'render_bulk_form'), 99);
        }

        /**
         * Render the bulk form button.
         */
        public function before_add_to_cart_form()
        {
            global $post;
            if (wbv_has_bulk_variation_form($post)) {
                ?>
                <input class="button btn-bulk" type="button" value="<?php echo __('Bulk Order Form', 'woocommerce-bulk-variations'); ?>"  />
                <?php
            }
        }

        /**
         * Render the bulk form.
         */
        public function render_bulk_form()
        {
            global $post;
            if (wbv_has_bulk_variation_form($post)) {
                if (!wbv_single_product_form_enabled($post)) {
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                    add_action('woocommerce_product_meta_start', array($this, 'before_add_to_cart_form'));
                } else {
                    add_action('woocommerce_before_add_to_cart_form', array($this, 'before_add_to_cart_form'));
                }
                $this->add_scripts_and_styles();
                wc_get_template('variable-grid.php', array(), WC_TEMPLATE_PATH . '/single-product/', WBV_ABSPATH . 'templates/single-product/');
            }
        }
    }
}

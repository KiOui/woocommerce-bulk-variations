<?php

if (!class_exists("WBVBulkForm")) {
    class WBVBulkForm {

        function __construct() {

        }

        function actions_and_filters() {

        }

        public function has_bulk_form(): bool {
            global $post;

            if (!$post || !is_product()) {
                return false;
            }

            $product = wc_get_product( $post->ID );

            if (!$product || !get_post_meta($product->get_id(), 'wbv_enable_bulk_form', true)) {
                return false;
            }

            if (!$product->has_child() && !$product->is_type('variable')) {
                return false;
            }

            return true;
        }

        public function render_bulk_form() {
            if ($this->has_bulk_form()) {
                wc_get_template( 'variable-grid.php', array(), WC_TEMPLATE_PATH . '/single-product/', WBV_ABSPATH . 'templates/single-product/' );
            }
        }
    }
}
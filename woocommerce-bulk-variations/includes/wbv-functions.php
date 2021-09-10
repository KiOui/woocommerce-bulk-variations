<?php

if (!function_exists('wbv_get_product_attributes')) {
    /**
     * Get all product attributes of a specific product.
     *
     * @param WC_Product $product the product to get the product attributes of
     *
     * @return array an array of available attributes for a product
     */
    function wbv_get_product_attributes(WC_Product $product): array
    {
        $attributes = $product->get_attributes();
        $available_attributes = array();
        foreach ($attributes as $name => $attribute) {
            if (taxonomy_exists($name)) {
                $taxonomy = get_taxonomy($name);
                $available_attributes[] = array(
                    'value' => $name,
                    'label' => $taxonomy->label
                );
            } else {
                $available_attributes[] = array(
                    'value' => $name,
                    'label' => $name
            );
            }
        }
        return $available_attributes;
    }
}

if (!function_exists('wbv_get_bulk_variation_options')) {
    /**
     * Get all available bulk variation options for a specific post
     *
     * @param $post WP_Post post to get available attributes for
     *
     * @return array an array of all options for the bulk variation form
     */
    function wbv_get_bulk_variation_options(WP_Post $post): array
    {
        $product = wc_get_product($post->ID);

        if (empty($product) || !$product->is_type('variable')) {
            return array();
        }

        $available_attributes = wbv_get_product_attributes($product);
        array_unshift($available_attributes, array(
            'value' => null,
            'label' => __("Select Attribute", 'woocommerce-bulk-variations')
        ));
        return $available_attributes;
    }
}

if (!function_exists("wbv_single_product_form_enabled")) {
    /**
     * Check if the single product form is enabled.
     *
     * @param WP_Post $post the post to check the form for
     *
     * @return bool true if the product form is enabled, false otherwise
     */
    function wbv_single_product_form_enabled(WP_Post $post): bool
    {
        if (!wbv_has_bulk_variation_form($post)) {
            return false;
        }
        if (!get_post_meta($post->ID, 'wbv_enable_singular_form', true)) {
            return false;
        }

        return true;
    }
}

if (!function_exists("wbv_is_variable_product")) {
    /**
     * Check if a WP_Post is a variable WC_Product.
     *
     * @param WP_Post $post the post to check
     *
     * @return bool true if the WP_Post is a variable WC_Product
     */
    function wbv_is_variable_product(WP_Post $post): bool
    {
        $product = wc_get_product($post->ID);
        if (!$product) {
            return false;
        } elseif (!$product->has_child() && !$product->is_type('variable')) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists("wbv_bulk_form_enabled")) {
    /**
     * Check if a WP_Post has the bulk form enabled.
     *
     * @param WP_Post $post the post to check
     *
     * @return bool true if the post has the bulk form enabled
     */
    function wbv_bulk_form_enabled(WP_Post $post): bool
    {
        if (!get_post_meta($post->ID, 'wbv_enable_bulk_form', true)) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists("wbv_row_column_set")) {
    /**
     * Check if a WP_Post has a column and row bulk form setting.
     *
     * @param WP_Post $post the post to check
     *
     * @return bool true if the post has a column and row set in the bulk form settings.
     */
    function wbv_row_column_set(WP_Post $post): bool
    {
        $row_attribute = get_post_meta($post->ID, 'wbv_bulk_form_row', true);
        $column_attribute = get_post_meta($post->ID, 'wbv_bulk_form_column', true);

        if (!$row_attribute || !$column_attribute || $row_attribute == $column_attribute) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists("wbv_has_bulk_variation_form")) {
    /**
     * Check if the bulk variation form is enabled.
     *
     * @param WP_Post $post the post to check the form for
     *
     * @return bool true if the product form is enabled, false otherwise
     */
    function wbv_has_bulk_variation_form(WP_Post $post): bool
    {
        return wbv_is_variable_product($post) && wbv_bulk_form_enabled($post) && wbv_row_column_set($post);
    }
}

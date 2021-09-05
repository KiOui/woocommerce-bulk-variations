<?php

use JetBrains\PhpStorm\ArrayShape;

if (!function_exists('wbv_get_product_attributes')) {
    function wbv_get_product_attributes(WC_Product $product): array {
        $attributes = $product->get_attributes();
        $available_attributes = array();
        foreach ($attributes as $name => $attribute) {
            if (taxonomy_exists($name)) {
                $taxonomy = get_taxonomy($name);
                $available_attributes[] = array(
                    'value' => $name,
                    'label' => $taxonomy->label
                );
            }
            else {
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
    function wbv_get_bulk_variation_options($post): array
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

if (!function_exists('wbv_get_attribute_list')) {
	function wbv_get_attribute_values_list($product, $attribute_name): array {
		$attributes = $product->get_attributes();

		$attribute_values = array();
		$all_attribute_values = $product->get_variation_attributes();

		if (isset($attributes[$attribute_name]) && $attributes[$attribute_name]['is_taxonomy']) {
			$row_terms = wc_get_product_terms($product->get_id(), $attribute_name);

			foreach ($row_terms as $row_term) {
				if (in_array($row_term->slug, $all_attribute_values[$attribute_name])) {
					$attribute_values[] = $row_term->slug;
				}
			}
		}
		else {
			$attribute_values[] = $all_attribute_values[$attribute_name];
		}
		return $attribute_values;
	}
}

if (!function_exists("wbv_get_variation_from_array")) {
	function wbv_get_variation_from_array( $row_attribute_name, $column_attribute_name, $row_value, $column_value, $variations ) {
		foreach ( $variations as $variation ) {
			if ( $variation['attributes'][ $row_attribute_name ] == $row_value && $variation['attributes'][ $column_attribute_name ] == $column_value ) {
				return $variation;
			}
		}
		return null;
	}
}

if (!function_exists('wbv_get_bulk_variations_matrix_data')) {
	function wbv_get_bulk_variations_matrix_data($post): array {
		$product = wc_get_product($post->ID);

		$row_attribute = get_post_meta($post->ID, 'wbv_bulk_form_row', true);
		$column_attribute = get_post_meta($post->ID, 'wbv_bulk_form_column', true);

		$attribute_values = array();

		$attribute_values[$row_attribute] = wbv_get_attribute_values_list($product, $row_attribute);
		$attribute_values[$column_attribute] = wbv_get_attribute_values_list($product, $column_attribute);

		$grid = array();
		foreach ( $attribute_values[ $row_attribute ] as $row_value ) {
			foreach ( $attribute_values[ $column_attribute ] as $column_value ) {
				$grid[ ( $row_value ) ][ ( $column_value ) ] = null;
			}
		}

		$row_attribute = sanitize_title( $row_attribute );
		$column_attribute = sanitize_title( $column_attribute );

		$pv = $product->get_available_variations();
		foreach ( $grid as $row_key => $row ) {
			foreach ( $row as $column_key => $column ) {
				$grid[$row_key][$column_key] = wbv_get_variation_from_array("attribute_" . $row_attribute, "attribute_" . $column_attribute, $row_key, $column_key, $pv);
			}
		}

		return array(
			"row_attribute" => $row_attribute,
			"column_attribute" => $column_attribute,
			"row_keys" => $attribute_values[$row_attribute],
			"column_keys" => $attribute_values[$column_attribute],
			"grid" => $grid
		);
	}
}

if (!function_exists("wbv_get_attribute_title")) {
	function wbv_get_attribute_title($taxonomy, $value, WC_Product $product): string {
		return ucwords($value);
	}
}

if (!function_exists("wbv_single_product_form_enabled")) {
	function wbv_single_product_form_enabled(WP_Post $post): bool {
		if (!wbv_has_bulk_variation_form($post)) {
			return false;
		}
		if (!get_post_meta($post->ID, 'wbv_enable_singular_form', true)) {
			return false;
		}

		return true;
	}
}

if (!function_exists("wbv_has_bulk_variation_form")) {
	function wbv_has_bulk_variation_form(WP_Post $post): bool {
		$product = wc_get_product($post->ID);
		if (!$product) {
			return false;
		}

		if (!get_post_meta($product->get_id(), 'wbv_enable_bulk_form', true)) {
			return false;
		}

		if (!$product->has_child() && !$product->is_type('variable')) {
			return false;
		}

		return true;
	}
}
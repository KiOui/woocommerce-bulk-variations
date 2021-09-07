<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WidCol Metabox class
 *
 * This class is able to render a custom metabox with a custom specification of fields.
 *
 * @class WidColMetabox
 */
if ( ! class_exists( "WBVMatrix" ) ) {
	class WBVMatrix {
		private WC_Product $product;
		private array $matrix;
		private string $row_attribute;
		private string $column_attribute;
		private array $row_keys;
		private array $column_keys;

		public function __construct(WC_Product_Variable $product) {
			$this->product = $product;
			$this->row_attribute = get_post_meta($this->product->get_id(), 'wbv_bulk_form_row', true);
			$this->column_attribute = get_post_meta($this->product->get_id(), 'wbv_bulk_form_column', true);

			$this->row_keys = $this->get_attribute_value_list($this->row_attribute);
			$this->column_keys = $this->get_attribute_value_list($this->column_attribute);
			$this->matrix = $this->construct_matrix();
		}

		private function get_attribute_value_list(string $attribute_name): array {
			$all_attribute_values = $this->product->get_variation_attributes();
			return array_values($all_attribute_values[$attribute_name]);
		}

		private function construct_matrix(): array {
			$matrix = array();
			for ($row_index = 0; $row_index < $this->get_row_length(); $row_index++) {
				for ($column_index = 0; $column_index < $this->get_column_length(); $column_index++) {
					$matrix[$row_index][$column_index] = $this->get_variation($this->row_keys[$row_index], $this->column_keys[$column_index]);
				}
			}
			return $matrix;
		}

		private function get_variation($row_value, $column_value): ?WC_Product_Variation {
			foreach ($this->product->get_available_variations('objects') as $variation) {
				if ($variation->get_attributes()[$this->row_attribute] == $row_value && $variation->get_attributes()[$this->column_attribute] == $column_value) {
					return $variation;
				}
			}
			return null;
		}

		public function get_row_attribute(): string {
			return $this->row_attribute;
		}

		public function get_column_attribute(): string {
			return $this->column_attribute;
		}

		public function get_row_keys(): array {
			return $this->row_keys;
		}

		public function get_column_keys(): array {
			return $this->column_keys;
		}

		public function get_row_length(): int {
			return sizeof($this->row_keys);
		}

		public function get_column_length(): int {
			return sizeof($this->column_keys);
		}

		public function get_row_key(int $index): ?string {
			if ($index < $this->get_row_length() && $index >= 0) {
				return $this->row_keys[$index];
			}
			else {
				return null;
			}
		}

		public function get_column_key(int $index): ?string {
			if ($index < $this->get_column_length() && $index >= 0) {
				return $this->column_keys[$index];
			}
			else {
				return null;
			}
		}

		public function get_matrix_index(int $row_index, int $column_index): ?WC_Product_variation {
			if ($row_index >= 0 && $row_index < $this->get_row_length() && $column_index >= 0 && $column_index < $this->get_column_length()) {
				return $this->matrix[$row_index][$column_index];
			}
			return null;
		}
	}
}

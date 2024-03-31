<?php
/**
 * WooCommerce Bulk Variations Matrix class
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WBV_ABSPATH . 'includes/WbvMatrixException.php';

if ( ! class_exists( 'WbvMatrix' ) ) {
	/**
	 * WbvMatrix class
	 *
	 * This class is used for storing the product variations matrix.
	 *
	 * @class WbvMatrix
	 */
	class WbvMatrix {

		/**
		 * Product to store variations for.
		 *
		 * @var WC_Product_Variable
		 */
		private $product;

		/**
		 * Built matrix.
		 *
		 * @var array
		 */
		private $matrix;

		/**
		 * Row attribute of matrix.
		 *
		 * @var string
		 */
		private $row_attribute;

		/**
		 * Column attribute of matrix
		 *
		 * @var string
		 */
		private $column_attribute;

		/**
		 * Array storing row keys of matrix.
		 *
		 * @var array
		 */
		private $row_keys;

		/**
		 * Array storing column keys of matrix
		 *
		 * @var array
		 */
		private $column_keys;

		/**
		 * Construct a variation matrix.
		 *
		 * @param WC_Product_Variable $product The product to create the variation matrix for.
		 * @throws WbvMatrixException When the $row_attribute or $column_attribute does not exist for $product.
		 */
		public function __construct( WC_Product_Variable $product, string $row_attribute, string $column_attribute ) {
			$this->product = $product;
			$this->row_attribute = $row_attribute;
			$this->column_attribute = $column_attribute;

			$attributes = $product->get_variation_attributes();

			if ( ! key_exists( $row_attribute, $attributes ) ) {
				throw new WbvMatrixException( esc_html( "$row_attribute is not an attribute of " . $product->get_name() ) );
			}

			if ( ! key_exists( $column_attribute, $attributes ) ) {
				throw new WbvMatrixException( esc_html( "$column_attribute is not an attribute of " . $product->get_name() ) );
			}

			$this->row_keys = array_values( $attributes[ $row_attribute ] );
			$this->column_keys = array_values( $attributes[ $column_attribute ] );

			$this->matrix = $this->construct_matrix();
		}

		/**
		 * Construct a variation matrix.
		 *
		 * @return array A 2D array containing the variation matrix.
		 */
		private function construct_matrix(): array {
			$matrix = array();
			for ( $row_index = 0; $row_index < $this->get_row_length(); $row_index++ ) {
				for ( $column_index = 0; $column_index < $this->get_column_length(); $column_index++ ) {
					$matrix[ $row_index ][ $column_index ] = $this->get_variation( $this->row_keys[ $row_index ], $this->column_keys[ $column_index ] );
				}
			}
			return $matrix;
		}

		/**
		 * Get a specific variation.
		 *
		 * @param string $row_value Row value of the variation to get.
		 * @param string $column_value Column value of the variation to get.
		 *
		 * @return WC_Product_Variation|null Null if the product variation was not found, the WC_Product_Variation otherwise.
		 */
		private function get_variation( string $row_value, string $column_value ) {
			foreach ( $this->product->get_available_variations( 'objects' ) as $variation ) {
				if ( $variation->get_attributes()[ $this->row_attribute ] == $row_value && $variation->get_attributes()[ $this->column_attribute ] == $column_value ) {
					return $variation;
				}
			}
			return null;
		}

		/**
		 * Get the product for the bulk form.
		 *
		 * @return WC_Product_Variable The product for the bulk form.
		 */
		public function get_product(): WC_Product_Variable {
			return $this->product;
		}

		/**
		 * Get the row attribute name.
		 *
		 * @return string The row attribute name.
		 */
		public function get_row_attribute(): string {
			return $this->row_attribute;
		}

		/**
		 * Get the column attribute name.
		 *
		 * @return string The column attribute name.
		 */
		public function get_column_attribute(): string {
			return $this->column_attribute;
		}

		/**
		 * Get all row keys.
		 *
		 * @return array The row keys.
		 */
		public function get_row_keys(): array {
			return $this->row_keys;
		}

		/**
		 * Get all column keys.
		 *
		 * @return array The column keys.
		 */
		public function get_column_keys(): array {
			return $this->column_keys;
		}

		/**
		 * Get all formatted column keys.
		 *
		 * @return array The formatted column keys.
		 */
		public function get_formatted_column_keys(): array {
			return array_map( 'ucwords', $this->column_keys );
		}

		/**
		 * Get the length of the row elements.
		 *
		 * @return int The length of the row.
		 */
		public function get_row_length(): int {
			return count( $this->row_keys );
		}

		/**
		 * Get the length of the column elements.
		 *
		 * @return int The length of the column.
		 */
		public function get_column_length(): int {
			return count( $this->column_keys );
		}

		/**
		 * Get a specific row key.
		 *
		 * @param int $index The index of the row key to get.
		 *
		 * @return string|null Null if the row key does not exist, the row key otherwise.
		 */
		public function get_row_key( int $index ) {
			if ( $index < $this->get_row_length() && $index >= 0 ) {
				return $this->row_keys[ $index ];
			} else {
				return null;
			}
		}

		/**
		 * Get a specific column key.
		 *
		 * @param int $index The index of the column key to get.
		 *
		 * @return string|null Null if the column key does not exist, the column key otherwise.
		 */
		public function get_column_key( int $index ) {
			if ( $index < $this->get_column_length() && $index >= 0 ) {
				return $this->column_keys[ $index ];
			} else {
				return null;
			}
		}

		/**
		 * Get an element of the matrix.
		 *
		 * @param int $row_index The row index for the element to get from the matrix.
		 * @param int $column_index The column index for the element to get from the matrix.
		 *
		 * @return WC_Product_variation|null Null if the variation does not exist, the variation otherwise.
		 */
		public function get_matrix_index( int $row_index, int $column_index ) {
			if ( $row_index >= 0 && $row_index < $this->get_row_length() && $column_index >= 0 && $column_index < $this->get_column_length() ) {
				return $this->matrix[ $row_index ][ $column_index ];
			}
			return null;
		}
	}
}

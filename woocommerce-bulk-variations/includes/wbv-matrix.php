<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WBVMatrix class
 *
 * This class is used for storing the product variations matrix.
 *
 * @class WBVMatrix
 */
if (! class_exists("WBVMatrix")) {
    class WBVMatrix
    {
        /**
         * Product to store variations for.
         *
         * @var WC_Product_Variable
         */
        private WC_Product_Variable $product;

        /**
         * Built matrix.
         *
         * @var array
         */
        private array $matrix;

        /**
         * Row attribute of matrix.
         *
         * @var string
         */
        private string $row_attribute;

        /**
         * Column attribute of matrix
         *
         * @var string
         */
        private string $column_attribute;

        /**
         * Array storing row keys of matrix.
         *
         * @var array
         */
        private array $row_keys;

        /**
         * Array storing column keys of matrix
         *
         * @var array
         */
        private array $column_keys;

        /**
         * Construct a variation matrix.
         *
         * @param WC_Product_Variable $product the product to create the variation matrix for
         */
        public function __construct(WC_Product_Variable $product)
        {
            $this->product = $product;
            $this->row_attribute = get_post_meta($this->product->get_id(), 'wbv_bulk_form_row', true);
            $this->column_attribute = get_post_meta($this->product->get_id(), 'wbv_bulk_form_column', true);

            $this->row_keys = $this->get_attribute_value_list($this->row_attribute);
            $this->column_keys = $this->get_attribute_value_list($this->column_attribute);
            $this->matrix = $this->construct_matrix();
        }

        /**
         * Get a list of attribute values.
         *
         * @param string $attribute_name the attribute name to get the attribute values for
         *
         * @return array an array of attribute values
         */
        private function get_attribute_value_list(string $attribute_name): array
        {
            $all_attribute_values = $this->product->get_variation_attributes();
            return array_values($all_attribute_values[$attribute_name]);
        }

        /**
         * Construct a variation matrix.
         *
         * @return array a 2D array containing the variation matrix
         */
        private function construct_matrix(): array
        {
            $matrix = array();
            for ($row_index = 0; $row_index < $this->get_row_length(); $row_index++) {
                for ($column_index = 0; $column_index < $this->get_column_length(); $column_index++) {
                    $matrix[$row_index][$column_index] = $this->get_variation($this->row_keys[$row_index], $this->column_keys[$column_index]);
                }
            }
            return $matrix;
        }

        /**
         * Get a specific variation.
         *
         * @param $row_value string row value of the variation to get
         * @param $column_value string column value of the variation to get
         *
         * @return WC_Product_Variation|null null if the product variation was not found, the WC_Product_Variation otherwise
         */
        private function get_variation(string $row_value, string $column_value): ?WC_Product_Variation
        {
            foreach ($this->product->get_available_variations('objects') as $variation) {
                if ($variation->get_attributes()[$this->row_attribute] == $row_value && $variation->get_attributes()[$this->column_attribute] == $column_value) {
                    return $variation;
                }
            }
            return null;
        }

        /**
         * Get the row attribute name.
         *
         * @return string the row attribute name
         */
        public function get_row_attribute(): string
        {
            return $this->row_attribute;
        }

        /**
         * Get the column attribute name.
         *
         * @return string the column attribute name
         */
        public function get_column_attribute(): string
        {
            return $this->column_attribute;
        }

        /**
         * Get all row keys.
         *
         * @return array the row keys
         */
        public function get_row_keys(): array
        {
            return $this->row_keys;
        }

        /**
         * Get all column keys.
         *
         * @return array the column keys
         */
        public function get_column_keys(): array
        {
            return $this->column_keys;
        }

        /**
         * Get the length of the row elements.
         *
         * @return int the length of the row
         */
        public function get_row_length(): int
        {
            return sizeof($this->row_keys);
        }

        /**
         * Get the length of the column elements.
         *
         * @return int the length of the column
         */
        public function get_column_length(): int
        {
            return sizeof($this->column_keys);
        }

        /**
         * Get a specific row key.
         *
         * @param int $index the index of the row key to get
         *
         * @return string|null null if the row key does not exist, the row key otherwise
         */
        public function get_row_key(int $index): ?string
        {
            if ($index < $this->get_row_length() && $index >= 0) {
                return $this->row_keys[$index];
            } else {
                return null;
            }
        }

        /**
         * Get a specific column key.
         *
         * @param int $index the index of the column key to get
         *
         * @return string|null null if the column key does not exist, the column key otherwise
         */
        public function get_column_key(int $index): ?string
        {
            if ($index < $this->get_column_length() && $index >= 0) {
                return $this->column_keys[$index];
            } else {
                return null;
            }
        }

        /**
         * Get an element of the matrix.
         *
         * @param int $row_index the row index for the element to get from the matrix
         * @param int $column_index the column index for the element to get from the matrix
         *
         * @return WC_Product_variation|null null if the variation does not exist, the variation otherwise
         */
        public function get_matrix_index(int $row_index, int $column_index): ?WC_Product_variation
        {
            if ($row_index >= 0 && $row_index < $this->get_row_length() && $column_index >= 0 && $column_index < $this->get_column_length()) {
                return $this->matrix[$row_index][$column_index];
            }
            return null;
        }
    }
}

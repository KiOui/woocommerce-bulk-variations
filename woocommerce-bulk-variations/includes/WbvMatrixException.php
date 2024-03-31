<?php
/**
 * WooCommerce Bulk Variations Matrix Exception class
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WbvMatrixException' ) ) {
	/**
	 * WbvMatrixException class.
	 *
	 * @class WbvMatrixException
	 */
	class WbvMatrixException extends Exception {}
}

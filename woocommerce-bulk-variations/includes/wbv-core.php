<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WBV Core class
 *
 * @class WBVCore
 */
if (!class_exists("WBVCore")) {
    class WBVCore
    {
        /**
         * Plugin version
         *
         * @var string
         */
        public string $version = '0.0.1';

        /**
         * The single instance of the class
         *
         * @var WBVCore|null
         */
        protected static ?WBVCore $_instance = null;

        /**
         * WooCommerce Bulk Variations Core instance
         *
         * Uses the Singleton pattern to load 1 instance of this class at maximum
         *
         * @static
         * @return WBVCore
         */
        public static function instance(): WBVCore
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        private function __construct()
        {
            $this->define_constants();
            $this->init_hooks();
            $this->actions_and_filters();
        }

        /**
         * Initialise WooCommerce Bulk Variations
         */
        public function init()
        {
            $this->initialise_localisation();
            do_action('wbv_init');
        }

        /**
         * Initialise the localisation of the plugin.
         */
        private function initialise_localisation()
        {
            load_plugin_textdomain('woocommerce-bulk-variations', false, plugin_basename(dirname(WBV_PLUGIN_FILE)) . '/languages/');
        }

        /**
         * Define constants of the plugin.
         */
        private function define_constants()
        {
            $this->define('WBV_ABSPATH', dirname(WBV_PLUGIN_FILE) . '/');
            $this->define('WBV_VERSION', $this->version);
            $this->define('WBV_FULLNAME', 'woocommerce-bulk-variations');
        }

        /**
         * Define if not already set
         *
         * @param string $name
         * @param string $value
         */
        private static function define(string $name, string $value)
        {
            if (! defined($name)) {
                define($name, $value);
            }
        }

        /**
         * Initialise activation and deactivation hooks.
         */
        private function init_hooks()
        {
            register_activation_hook(WBV_PLUGIN_FILE, array( $this, 'activation' ));
            register_deactivation_hook(WBV_PLUGIN_FILE, array( $this, 'deactivation' ));
        }

        /**
         * Activation hook call.
         */
        public function activation()
        {
        }

        /**
         * Deactivation hook call.
         */
        public function deactivation()
        {
        }

        /**
         * Add pluggable support to functions
         */
        public function pluggable()
        {
            include_once WBV_ABSPATH . 'includes/wbv-functions.php';
        }

        /**
         * Add actions and filters.
         */
        private function actions_and_filters()
        {
            add_action('after_setup_theme', array( $this, 'pluggable' ));
            add_action('init', array( $this, 'init' ));
            if (is_admin()) {
                add_action('wbv_init', array($this, 'add_meta_box_support'));
            } else {
                include_once WBV_ABSPATH . 'includes/wbv-bulk-form.php';
                $bulk_form_class = new WBVBulkForm();
                $bulk_form_class->actions_and_filters();

                if (isset($_POST['add-variations-to-cart']) && $_POST['add-variations-to-cart']) {
                    add_action('wp_loaded', array( $this, 'process_matrix_submission' ), 99);
                }
            }
        }

        /**
         * Add Bulk Form meta boxes to products.
         */
        public function add_meta_box_support()
        {
            include_once WBV_ABSPATH . '/includes/wbv-metaboxes.php';
            new WBVMetabox('wbv_metabox', array(
                array(
                    'label' => __('Bulk variation form', 'woocommerce-bulk-variations'),
                    'id'    => 'wbv_enable_bulk_form',
                    'type'  => 'checkbox'
                ),
                array(
                    'label' => __('Singular variation form', 'woocommerce-bulk-variations'),
                    'id'    => 'wbv_enable_singular_form',
                    'type'  => 'checkbox'
                ),
                array(
                    'label' => __('Bulk variation form column', 'woocommerce-bulk-variations'),
                    'id'    => 'wbv_bulk_form_column',
                    'type'  => 'select',
                    'options' => 'wbv_get_bulk_variation_options'
                ),
                array(
                    'label' => __('Bulk variation form row', 'woocommerce-bulk-variations'),
                    'id'    => 'wbv_bulk_form_row',
                    'type'  => 'select',
                    'options' => 'wbv_get_bulk_variation_options'
                ),
            ), 'product', __('Bulk variation form Settings', 'woocommerce-bulk-variations'), 'side');
        }

        /**
         * Process matrix submission and add products to cart.
         */
        public function process_matrix_submission()
        {
            $items = $_POST['order_info'];
            $product_id = $_POST['product_id'];
            $product = wc_get_product($product_id);

            if ($product) {
                foreach ($items as $item) {
                    $amount = absint($item['quantity']) ?: 0;
                    if ($amount != 0) {
                        $variation_id = empty($item['variation_id']) ? '' : absint($item['variation_id']);
                        if (!empty($variation_id)) {
                            $product_variation = wc_get_product($variation_id);
                            if (apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $amount, $variation_id)) {
                                try {
                                    WC()->cart->add_to_cart($product->get_id(), $amount, $product_variation->get_id());
                                } catch (Exception $e) {
                                    wc_add_notice(sprintf(__("Failed to add %s to cart.", 'woocommerce-bulk-variations'), $product_variation->get_name()));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

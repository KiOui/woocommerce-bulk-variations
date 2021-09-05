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
            }
            else {
                include_once WBV_ABSPATH . 'includes/wbv-bulk-form.php';
                $bulk_form_class = new WBVBulkForm();
                add_action('woocommerce_before_single_product', array($bulk_form_class, 'render_bulk_form'), 99);
            }
        }

        public function add_meta_box_support() {
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
    }
}

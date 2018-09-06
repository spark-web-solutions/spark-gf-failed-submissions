<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions {
    /**
     * The unique identifier of this plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct() {
        if (defined('SPARK_GF_FAILED_SUBMISSIONS_VERSION')) {
            $this->version = SPARK_GF_FAILED_SUBMISSIONS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'spark-gf-failed-submissions';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_global_hooks();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_db_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Spark_Gf_Failed_Submissions_i18n. Defines internationalization functionality.
     * - Spark_Gf_Failed_Submissions_Admin. Defines all hooks for the admin area.
     * - Spark_Gf_Failed_Submissions_Public. Defines all hooks for the public side of the site.
     * - Spark_Gf_Failed_Submissions_Db. Custom DB handling.
     * - Spark_Gf_Failed_Submissions_Gfaddon. GF Addon where the magic happens.
     *
     * @since 1.0.0
     * @access private
     */
    private function load_dependencies() {
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-spark-gf-failed-submissions-i18n.php');

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'admin/class-spark-gf-failed-submissions-admin.php');

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'public/class-spark-gf-failed-submissions-public.php');

        /**
         * The class responsible for custom DB logic
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-spark-gf-failed-submissions-db.php');

        /**
         * The GF Addon class where the magic happens
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-spark-gf-failed-submissions-gfaddon.php');

        /**
         * API class for public data access
         */
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-spark-gf-failed-submissions-api.php');
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Spark_Gf_Failed_Submissions_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function set_locale() {
        $plugin_i18n = new Spark_Gf_Failed_Submissions_i18n();

        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    /**
     * Register all the hooks that apply site-wide - i.e. both front- and back-end
     *
     * @since 1.0.0
     */
    private function define_global_hooks() {
        add_action('gform_loaded', array($this, 'load_gf_addon'));
    }

    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     *
     * @since 1.0.0
     */
    private function define_admin_hooks() {
        $plugin_admin = new Spark_Gf_Failed_Submissions_Admin($this->get_plugin_name(), $this->get_version());

//         add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
//         add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        add_action('admin_init', array($plugin_admin, 'check_updates'));
        add_action('wpmu_new_blog', array($plugin_admin, 'on_create_blog'), 10, 6);
        add_filter('wpmu_drop_tables', array($plugin_admin, 'on_delete_blog'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     *
     * @since 1.0.0
     */
    private function define_public_hooks() {
        $plugin_public = new Spark_Gf_Failed_Submissions_Public($this->get_plugin_name(), $this->get_version());

//         add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
//         add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
    }

    /**
     * Set up/upgrade custom DB tables.
     *
     * @since 1.0.0
     */
    private function define_db_hooks() {
        add_action('init', array('Spark_Gf_Failed_Submissions_Db', 'register_tables'));
        add_action('switch_blog', array('Spark_Gf_Failed_Submissions_Db', 'register_tables'));
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since 1.0.0
     * @return string The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since 1.0.0
     * @return string The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function load_gf_addon() {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return;
        }

        GFAddOn::register('Spark_Gf_Failed_Submissions_Gfaddon');
    }
}

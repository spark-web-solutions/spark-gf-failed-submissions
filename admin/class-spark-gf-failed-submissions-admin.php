<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/admin
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_Admin {
    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Option name for storing the installed version in the DB
     * @var string
     */
    public static $version_option_name = 'spark_gf_failed_submissions_version';

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/spark-gf-failed-submissions-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/spark-gf-failed-submissions-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Create our custom tables when a new blog is created (multisite only)
     *
     * @param integer $blog_id
     * @param integer $user_id
     * @param string $domain
     * @param string $path
     * @param integer $site_id
     * @param array $meta
     *
     * @since 1.0.0
     */
    public function on_create_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        switch_to_blog($blog_id);
        Spark_Gf_Failed_Submissions_Db::create_tables();
        restore_current_blog();
    }

    /**
     * Check whether a new version has just been installed and if so perform any necessary updates
     */
    public function check_updates() {
        if (is_multisite()) {
            global $wpdb;
            // Get all blogs in the network and run our logic on each one
            $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $this->process_updates();
                restore_current_blog();
            }
        } else {
            $this->process_updates();
        }
    }

    private function process_updates() {
        $installed_version = get_option(self::$version_option_name);

        if (!$installed_version) {
            // No installed version - we'll assume it's just been freshly installed
            add_option(self::$version_option_name, SPARK_GF_FAILED_SUBMISSIONS_VERSION);
        } elseif ($installed_version != SPARK_GF_FAILED_SUBMISSIONS_VERSION) {
            // Just updated - run updates

            // Make sure table structure is up to date
            Spark_Gf_Failed_Submissions_Db::create_tables();

            // Custom updates
//                 if (version_compare('1.1', $installed_version, '>')) {
//                     // Code to upgrade to version 1.1
//                 }

            // Database is now up to date - update installed version to latest version
            update_option(self::$version_option_name, SPARK_GF_FAILED_SUBMISSIONS_VERSION);
        }
    }

    /**
     * Delete our custom tables when deleting a blog (multisite)
     *
     * @param array $tables
     * @return array
     *
     * @since 1.0.0
     */
    public function on_delete_blog($tables) {
        global $wpdb;
        $tables[] = $wpdb->spark_gf_failed_submissions;
        $tables[] = $wpdb->spark_gf_failed_submissions_fields;
        return $tables;
    }
}

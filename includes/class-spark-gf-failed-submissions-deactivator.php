<?php

/**
 * Fired during plugin deactivation
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_Deactivator {
    /**
     * Do not try and instantiate an instance of this class - all methods are static
     * @throws Exception
     * @return boolean
     */
    private function __construct() {
        throw new Exception(sprintf(__('%s cannot be instantiated - all methods are static.', 'spark-gf-failed-submissions'), __CLASS__));
        return false;
    }

    /**
     * Do necessary stuff on deactivation
     *
     * @param boolean $network_wide True if the plugin has been deactivated for the entire network (multisite only) else false
     * @since 1.0.0
     */
    public static function deactivate($network_wide = false) {}

    /**
     * Do necessary stuff on uninstall
     *
     * @since 1.0.0
     */
    public static function uninstall() {
        global $wpdb;
        if (is_multisite()) {
            // Get all blogs in the network and run our logic on each one
            $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::clean_up();
                restore_current_blog();
            }
        } else {
            self::clean_up();
        }
    }

    /**
     * Clean up data we don't want to leave behind when plugin is removed
     */
    private static function clean_up() {
        Spark_Gf_Failed_Submissions_Db::remove_tables();
        delete_option(Spark_Gf_Failed_Submissions_Admin::$version_option_name);
    }
}

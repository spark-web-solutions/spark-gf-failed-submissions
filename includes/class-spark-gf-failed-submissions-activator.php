<?php
/**
 * Fired during plugin activation
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_Activator {
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
     * Do necessary stuff on activation
     *
     * @param boolean $network_wide True if the plugin has been activated for the entire network (multisite only) else false
     * @since 1.0.0
     */
    public static function activate($network_wide = false) {
        global $wpdb;
        if (is_multisite() && $network_wide) {
            // Get all blogs in the network and run our logic on each one
            $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::set_up();
                restore_current_blog();
            }
        } else {
            self::set_up();
        }
    }

    /**
     * Set up the data that we need for the plugin to operate
     */
    private static function set_up() {
        Spark_Gf_Failed_Submissions_Db::create_tables();
    }
}

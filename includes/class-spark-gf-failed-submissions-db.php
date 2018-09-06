<?php

/**
 * Handle all the custom DB stuff
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * This class defines all code necessary to handle the custom DB stuff.
 *
 * Shouldn't ever be instantiated - all methods are static
 *
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_Db {
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
     * Register our custom table names with $wpdb
     *
     * @since 1.0.0
     */
    public static function register_tables() {
        global $wpdb;
        $wpdb->spark_gf_failed_submissions = $wpdb->prefix.'spark_gf_failed_submissions';
        $wpdb->spark_gf_failed_submissions_fields = $wpdb->prefix.'spark_gf_failed_submissions_fields';
    }

    /**
     * Create our custom tables
     *
     * @since 1.0.0
     */
    public static function create_tables() {
        // Make sure table names are set - doesn't get called automatically during activation
        self::register_tables();

        global $wpdb;
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->spark_gf_failed_submissions;
        $sql = "
        CREATE TABLE $table_name (
            id int(10) unsigned NOT NULL AUTO_INCREMENT,
            form_id mediumint(8) unsigned NOT NULL,
            post_id bigint(20) unsigned DEFAULT NULL,
            date_created datetime NOT NULL,
            date_created_gmt datetime NOT NULL,
            source_url varchar(200) NOT NULL DEFAULT '',
            user_ip varchar(39) DEFAULT NULL,
            user_agent varchar(250) NOT NULL DEFAULT '',
            submitted_by bigint(20) unsigned DEFAULT NULL,
            email varchar(250) NOT NULL DEFAULT '',
            validation_message VARCHAR(250) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY form_date (form_id, date_created)
        ) $charset_collate";
        dbDelta($sql);

        $table_name = $wpdb->spark_gf_failed_submissions_fields;
        $sql = "
        CREATE TABLE $table_name (
            id int(10) unsigned NOT NULL AUTO_INCREMENT,
            submission_id int(10) unsigned NOT NULL,
            field_id mediumint(8) unsigned NOT NULL,
            validation_message VARCHAR(250) NOT NULL DEFAULT '',
            submitted_value mediumtext NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY submission_id (submission_id)
        ) $charset_collate";
        dbDelta($sql);
    }

    public static function remove_tables() {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS '.$wpdb->spark_gf_failed_submissions);
        $wpdb->query('DROP TABLE IF EXISTS '.$wpdb->spark_gf_failed_submissions_fields);
    }
}

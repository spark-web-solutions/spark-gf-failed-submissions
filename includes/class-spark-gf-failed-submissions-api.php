<?php

/**
 * This file is where all the publicly available data interactions happen.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * This class defines all the publicly available data interactions. If it's not available here you shouldn't be using it from outside this plugin.
 *
 * Shouldn't ever be instantiated - all methods are static
 *
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_Api {
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
     * Get failed submissions for the selected form
     * @param integer $form_id
     * @return array|boolean Array of failed submission objects or false if form doesn't exist
     */
    public static function get_submissions($form_id) {
        $form_id = (int)$form_id;
        if (GFAPI::form_id_exists($form_id)) {
            global $wpdb;
            $query = $wpdb->prepare('SELECT * FROM '.$wpdb->spark_gf_failed_submissions.' WHERE form_id = %d ORDER BY date_created_gmt DESC', $form_id);
            $results = $wpdb->get_results($query);
            return $results;
        }
        return false;
    }
}

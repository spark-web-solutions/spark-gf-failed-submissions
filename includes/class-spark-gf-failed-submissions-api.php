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
     * @since 1.0.0
     */
    private function __construct() {
        /* translators: %s: Name of the PHP class */
        throw new Exception(sprintf(__('%s cannot be instantiated - all methods are static.', 'spark-gf-failed-submissions'), __CLASS__));
        return false;
    }

    /**
     * Get failed submissions for the selected form
     * @param integer $form_id Form to retrieve failed submissions for
     * @param integer $limit Optional. Number of items to retrieve. Default 20, maximum 200.
     * @param integer $offset Optional. Number of items to skip. Default 0.
     * @return array|boolean Array of failed submission objects or false if form doesn't exist
     * @since 1.0.0
     * @version 1.2.0 Added $limit and $offset parameters
     */
    public static function get_submissions($form_id, $limit = 20, $offset = 0, &$total_count = null) {
        $form_id = (int)$form_id;
        if (GFAPI::form_id_exists($form_id)) {
            global $wpdb;
            $limit = absint($limit);
            if ($limit > 200) { // Restrict to a sensible maximum
            	$limit = 200;
            }
            $offset = absint($offset);
            $query = $wpdb->prepare('SELECT * FROM '.$wpdb->spark_gf_failed_submissions.' WHERE form_id = %d ORDER BY date_created_gmt DESC LIMIT '.$offset.', '.$limit, $form_id);
            $results = $wpdb->get_results($query);

            $query = $wpdb->prepare('SELECT count(id) FROM '.$wpdb->spark_gf_failed_submissions.' WHERE form_id = %d', $form_id);
            $total_count = $wpdb->get_var($query);

            return $results;
        }
        return false;
    }

    /**
     * Get a single submission record
     * @param integer $submission_id Failed submission to retrieve
     * @return object|null Submission record or null if doesn't exist
     * @since 1.1.0
     */
    public static function get_submission($submission_id) {
        global $wpdb;
        $query = $wpdb->prepare('SELECT * FROM '.$wpdb->spark_gf_failed_submissions.' WHERE id = %d', $submission_id);
        $result = $wpdb->get_row($query);
        return $result;
    }

    /**
     * Get field details for selected submission
     * @param integer $submission_id Failed submission to retrieve details for
     * @return array|null Array of field objects or null on error
     * @since 1.1.0
     */
    public static function get_submission_fields($submission_id) {
        global $wpdb;
        $query = $wpdb->prepare('SELECT * FROM '.$wpdb->spark_gf_failed_submissions_fields.' WHERE submission_id = %d', $submission_id);
        $result = $wpdb->get_results($query);
        return $result;
    }

    /**
     * Count the mumber of recent failed submissions matching a given filter
     * @param array $filter List of filter criteria, e.g. array('email' => 'plugins@sparkweb.com.au', 'user_ip' => '127.0.0.1')
     * @param integer $timeframe Optional. How far back (in minutes) to include submissions for. Default 5.
     * @param string $relation Optional. Type of matching - either AND (match all filters) or OR (match any filter). Default AND.
     * @return integer Number of matching failed submissions
     * @since 1.2.0
     */
    public static function count_recent_submissions(array $filter, $timeframe = 5, $relation = 'AND') {
    	global $wpdb;
    	$where = $data = array();
    	$time = new DateTime(current_time('mysql', true));
    	$time->sub(new DateInterval('PT'.$timeframe.'M'));
    	$data[] = $time->format('Y-m-d H:i:s');
    	foreach ($filter as $field => $value) {
    		$where[] = $field.' = %s';
    		$data[] = $value;
    	}
    	$relation = strcasecmp($relation, 'OR') === 0 ? ' OR ' : ' AND ';
    	$query = $wpdb->prepare('SELECT count(id) FROM '.$wpdb->spark_gf_failed_submissions.' WHERE date_created_gmt >= %s AND ('.implode($where, $relation).')', $data);
    	$result = $wpdb->get_var($query);
    	return $result;
    }

    /**
     * Remove a single submission record from the database
     * @param integer $submission_id ID of record to delete
     * @return integer|boolean Number of submission records deleted or false on error
     * @since 1.2.0
     */
    public static function delete_submission($submission_id) {
        global $wpdb;
        $result = $wpdb->delete($wpdb->spark_gf_failed_submissions_fields, array('submission_id' => $submission_id));
        if ($result !== false) {
            $result = $wpdb->delete($wpdb->spark_gf_failed_submissions, array('id' => $submission_id));
        }
        return $result;
    }

    /**
     * Remove multiple submission records from the database
     * @param array $submission_ids List of IDs of records to delete
     * @return integer|boolean Number of submission records deleted or false on error
     * @since 1.2.0
     */
    public static function delete_submissions(array $submission_ids) {
        global $wpdb;
        $ids = implode(',', array_map('absint', $submission_ids));
        $result = $wpdb->query('DELETE FROM '.$wpdb->spark_gf_failed_submissions_fields.' WHERE submission_id IN ('.$ids.')');
        if ($result !== false) {
            $result = $wpdb->query('DELETE FROM '.$wpdb->spark_gf_failed_submissions.' WHERE id IN ('.$ids.')');
        }
        return $result;
    }
}

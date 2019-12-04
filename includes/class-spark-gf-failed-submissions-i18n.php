<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://sparkweb.com.au
 * @since      1.0.0
 *
 * @package    Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 * @author     Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Gf_Failed_Submissions_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain('spark-gf-failed-submissions', false, dirname(dirname(plugin_basename(__FILE__))).'/languages/');
	}
}

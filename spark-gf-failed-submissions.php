<?php
/**
 * @link https://sparkweb.com.au
 * @since 1.0.0
 * @package Spark_Gf_Failed_Submissions
 *
 * Plugin Name: Spark GF Failed Submissions
 * Description: Track failed form submissions and get notified when they reach a customisable threshold. Requires Gravity Forms.
 * Version: 1.1.0
 * Author: Spark Web Solutions
 * Author URI: https://sparkweb.com.au
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: spark-gf-failed-submissions
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die();
}

/**
 * Current plugin version.
 */
define('SPARK_GF_FAILED_SUBMISSIONS_VERSION', '1.1.0');

/**
 * Text Domain
 */
define('SPARK_GF_FAILED_SUBMISSIONS_TEXTDOMAIN', 'spark-gf-failed-submissions');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-spark-gf-failed-submissions-activator.php
 */
function activate_spark_gf_failed_submissions() {
    require_once(plugin_dir_path(__FILE__).'includes/class-spark-gf-failed-submissions-activator.php');
    Spark_Gf_Failed_Submissions_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-spark-gf-failed-submissions-deactivator.php
 */
function deactivate_spark_gf_failed_submissions() {
    require_once(plugin_dir_path(__FILE__).'includes/class-spark-gf-failed-submissions-deactivator.php');
    Spark_Gf_Failed_Submissions_Deactivator::deactivate();
    Spark_Gf_Failed_Submissions_Deactivator::uninstall(); // @todo testing only - comment out before release!
}

/**
 * The code that runs during plugin uninstallation.
 * This action is documented in includes/class-spark-gf-failed-submissions-deactivator.php
 */
function uninstall_spark_gf_failed_submissions() {
    require_once(plugin_dir_path(__FILE__).'includes/class-spark-gf-failed-submissions-deactivator.php');
    Spark_Gf_Failed_Submissions_Deactivator::uninstall();
}

register_activation_hook(__FILE__, 'activate_spark_gf_failed_submissions');
register_deactivation_hook(__FILE__, 'deactivate_spark_gf_failed_submissions');
register_uninstall_hook(__FILE__, 'uninstall_spark_gf_failed_submissions');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once(plugin_dir_path(__FILE__) . 'includes/class-spark-gf-failed-submissions.php');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_spark_gf_failed_submissions() {
    $plugin = new Spark_Gf_Failed_Submissions();
}
run_spark_gf_failed_submissions();

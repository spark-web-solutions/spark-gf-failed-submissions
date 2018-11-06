<?php
/**
 * The Magic Happens Here
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Gf_Failed_Submissions
 * @subpackage Spark_Gf_Failed_Submissions/includes
 */
if (class_exists('GFForms')) {
    GFForms::include_addon_framework();

    /**
     * This class contains all the core logic for hooking into Gravity Forms for tracking failed submissions
     *
     * @since 1.0.0
     * @package Spark_Gf_Failed_Submissions
     * @subpackage Spark_Gf_Failed_Submissions/includes
     * @author Spark Web Solutions <plugins@sparkweb.com.au>
     */
    class Spark_Gf_Failed_Submissions_Gfaddon extends GFAddOn {
        protected $_version = SPARK_GF_FAILED_SUBMISSIONS_VERSION;
        protected $_min_gravityforms_version = '1.9';
        protected $_slug = 'spark-gf-failed-submissions';
        protected $_path = 'spark-gf-failed-submissions/spark-gf-failed-submissions.php';
        protected $_full_path = __FILE__;
        protected $_url = 'https://sparkweb.com.au/';
        protected $_title = 'Spark GF Failed Submissions';
        protected $_short_title = 'Failed Submissions';

        private static $_instance = null;
        private $interval_transient = 'spark-gf-failed-submissions-last-notification';

        public static function get_instance() {
            if (self::$_instance == null) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }

        public function pre_init() {
            parent::pre_init();
            $this->_title = __('Spark GF Failed Submissions', 'spark-gf-failed-submissions');
            $this->_short_title = __('Failed Submissions', 'spark-gf-failed-submissions');
            add_filter('gform_validation', array($this, 'check_for_failed_submission'), 9999); // Run our check after all validation logic

            // Add our page to view failed submissions
            add_action('gform_form_actions', array($this, 'add_form_action'), 10, 4);
            add_filter('gform_toolbar_menu', array($this, 'toolbar_menu'), 10, 2);
            add_filter('gform_addon_navigation', array($this, 'create_menu'));
        }

        public function init() {
            parent::init();
            $this->setDefaults();
        }

        public function scripts() {
            // This isn't the intended use of this function, but it works well enough for now
            // @todo find a better place to enqueue these
            if (is_admin()) {
                $scripts = array();
                switch ($this->get_page()) {
                    case 'submission_list':
                        $scripts = array(
                        'wp-lists',
                        'wp-ajax-response',
                        'thickbox',
                        'gform_json',
                        'gform_field_filter',
                        'sack',
                        );
                        break;
                    case 'submission_detail':
                        $scripts = array(
                        'gform_json',
                        'sack',
                        'postbox',
                        );
                        break;
                }
                foreach ($scripts as $script) {
                    wp_enqueue_script($script);
                }
            }
            return parent::scripts();
        }

        public function styles() {
            // This isn't the intended use of this function, but it works well enough for now
            // @todo find a better place to enqueue these
            if (is_admin()) {
                wp_enqueue_style('gform_admin');
            }
            return parent::styles();
        }

        public function form_settings_fields($form) {
            $plugin_settings = $this->get_plugin_settings();
            return array(
                    array(
                            'title'  => __('Failed Submissions', 'spark-gf-failed-submissions'),
                            'description' => __('Use these settings to override the global per-form settings that control when notifications will be sent for failed submissions. If left blank the global setting will be used, while setting either option to zero will disable notifications for this form. Note that even if notifications are disabled, failed submissions will still be tracked for this form and will still be included in the calculations for the site-wide notifications.', 'spark-gf-failed-submissions'),
                            'fields' => array(
                                    array(
                                            'name'    => 'form_fail_count',
                                            'tooltip' => sprintf(__('Enter the number of failed submissions required on this form to trigger a notification. Leave blank to use the global setting (currently %d) or set to zero to disable notifications for this form.', 'spark-gf-failed-submissions'), $plugin_settings['form_fail_count']),
                                            'label'   => __('Failure Threshold', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'default_value' => '',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                                    array(
                                            'name'    => 'form_fail_time',
                                            'tooltip' => sprintf(__('Enter the length of time (in minutes) that failed submissions are included in the check. Leave blank to use the global setting (currently %d) or set to zero to disable notifications for this form.', 'spark-gf-failed-submissions'), $plugin_settings['form_fail_time']),
                                            'label'   => __('Timeframe', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'default_value' => '',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                            ),
                    ),
            );
        }

        public function plugin_settings_fields() {
            return array(
                    array(
                            'title'  => __('Site-Wide Notifications', 'spark-gf-failed-submissions'),
                            'description' => __('These settings define when notifications will be sent for failed submissions across all forms. If either field is left blank or set to zero, site-wide notifications will be disabled.', 'spark-gf-failed-submissions'),
                            'fields' => array(
                                    array(
                                            'name'    => 'site_fail_count',
                                            'tooltip' => __('Enter the number of failed submissions required across all forms to trigger a notification', 'spark-gf-failed-submissions'),
                                            'label'   => __('Failure Threshold', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                                    array(
                                            'name'    => 'site_fail_time',
                                            'tooltip' => __('Enter the length of time (in minutes) that failed submissions are included in the check', 'spark-gf-failed-submissions'),
                                            'label'   => __('Timeframe', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                            ),
                    ),
                    array(
                            'title'  => __('Per-Form Notifications', 'spark-gf-failed-submissions'),
                            'description' => __('These settings define when notifications will be sent for failed submissions on a single form. If either field is left blank or set to zero, per-form notifications will be disabled. Individual forms can be configured to override these settings.', 'spark-gf-failed-submissions'),
                            'fields' => array(
                                    array(
                                            'name'    => 'form_fail_count',
                                            'tooltip' => __('Enter the number of failed submissions required on an individual form to trigger a notification', 'spark-gf-failed-submissions'),
                                            'label'   => __('Failure Threshold', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                                    array(
                                            'name'    => 'form_fail_time',
                                            'tooltip' => __('Enter the length of time (in minutes) that failed submissions are included in the check', 'spark-gf-failed-submissions'),
                                            'label'   => __('Timeframe', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                            ),
                    ),
                    array(
                            'title'  => __('General Settings', 'spark-gf-failed-submissions'),
                            'fields' => array(
                                    array(
                                            'name'    => 'notification_email',
                                            'tooltip' => __('Enter the email address to receive notifications. You can include multiple email addresses separated by commas.', 'spark-gf-failed-submissions'),
                                            'label'   => __('Send Notifications To', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'large',
                                            'required' => true,
                                            'default_value' => get_option('admin_email'),
                                            'validation_callback' => array($this, 'validate_email'),
                                    ),
                                    array(
                                            'name'    => 'email_interval',
                                            'tooltip' => __('Enter the length of time (in minutes) that the system should wait between sending failure notifications. E.g. if set to 5 (default) it will send no more than one email every 5 mintues.', 'spark-gf-failed-submissions'),
                                            'label'   => __('Interval Between Emails', 'spark-gf-failed-submissions'),
                                            'type'    => 'text',
                                            'class'   => 'small',
                                            'required' => true,
                                            'default_value' => 5,
                                            'validation_callback' => array($this, 'validate_number'),
                                    ),
                            ),
                    ),
            );
        }

        /**
         * Set default values for required settings
         */
        private function setDefaults() {
            $dirty = false;
            $settings = $this->get_plugin_settings();
            if (empty($settings['notification_email'])) {
                $settings['notification_email'] = get_option('admin_email');
                $dirty = true;
            }
            if (empty($settings['email_interval'])) {
                $settings['email_interval'] = 5;
                $dirty = true;
            }
            if ($dirty) {
                $this->update_plugin_settings($settings);
            }
        }

        public function check_for_failed_submission($validation_result) {
            if (!$validation_result['is_valid']) {
                // Validation failed - let's do our thing!

                /* === Tracking === */
                $form = $validation_result['form'];

                // First we'll store the submission
                global $wpdb;

                // Get some extra data to track
                $email = '';
                $failed_fields = array();
                foreach ($form['fields'] as $field) {
                    if (empty($email) && $field->type == 'email') {
                        $email = rgpost($field->id);
                    }
                    if ($field->failed_validation) {
                        $failed_fields[$field->id] = $field;
                    }
                }

                // Try and work out the main form validation message
                $validation_message = strip_tags(gf_apply_filters(array('gform_validation_message', $form['id']), esc_html__('There was a problem with your submission.', 'gravityforms').' '.esc_html__('Errors have been highlighted below.', 'gravityforms'), $form));

                // Get datetime object from site timezone
                $datetime = new DateTime('now', new DateTimeZone($this->wp_get_timezone_string()));
                $datetime_gmt = clone $datetime;
                $datetime_gmt->setTimezone(new DateTimeZone('UTC'));

                // Track the error
                $data = array(
                        'form_id' => $form['id'],
                        'post_id' => get_the_ID(),
                        'date_created' => $datetime->format('Y-m-d H:i:s'),
                        'date_created_gmt' => $datetime_gmt->format('Y-m-d H:i:s'),
                        'source_url' => GFFormsModel::get_current_page_url(),
                        'user_ip' => GFFormsModel::get_ip(),
                        'user_agent' => strlen($_SERVER['HTTP_USER_AGENT']) > 250 ? substr($_SERVER['HTTP_USER_AGENT'], 0, 250) : $_SERVER['HTTP_USER_AGENT'],
                        'submitted_by' => get_current_user_id(),
                        'email' => $email,
                        'validation_message' => strlen($validation_message) > 250 ? substr($validation_message, 0, 250) : $validation_message,
                );

                $format = array(
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                );

                $wpdb->insert($wpdb->spark_gf_failed_submissions, $data, $format);
                $submission_id = $wpdb->insert_id;

                // Track the specific field details
                $format = array(
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                );
                foreach ($failed_fields as $field) {
                    $field_id = $field->id;
                    $message = $field->validation_message;
                    if (!empty($field->inputs)) {
                        $value = array();
                        foreach ($field->inputs as $input) {
                            $input_value = rgpost('input_'.str_replace('.', '_', $input['id']));
                            if ($field->type == 'creditcard' && $input['id'] == $field->id.'.1') { // Make sure we're not storing credit card numbers!
                                $input_value = str_replace(' ', '', $input_value);
                                $card_number_length = strlen($input_value);
                                $input_value = substr($input_value, - 4, 4);
                                $input_value = str_pad($input_value, $card_number_length, 'X', STR_PAD_LEFT);
                            }
                            $value[$input['id']] = $input_value;
                        }
                    } else {
                        $value = rgpost('input_'.$field_id);
                    }
                    $value =
                    $data = array(
                            'submission_id' => $submission_id,
                            'field_id' => $field_id,
                            'validation_message' => $message,
                            'submitted_value' => maybe_serialize($value),
                    );
                    $wpdb->insert($wpdb->spark_gf_failed_submissions_fields, $data, $format);
                }

                /* === Notifications === */

                // First make sure we haven't sent an email too recently
                $settings = $this->get_plugin_settings();
                $last_notification = get_transient($this->interval_transient);
                if (false === $last_notification || (int)$last_notification < current_time('timestamp')-$settings['email_interval']*MINUTE_IN_SECONDS) {
                    // Check if we've hit the site-wide threshold
                    $threshold = intval($settings['site_fail_count']);
                    $minutes = intval($settings['site_fail_time']);

                    if (!empty($threshold) && !empty($minutes)) { // Empty value in either field means notifications are disabled
                        $cutoff = clone $datetime_gmt;
                        $cutoff->sub(new DateInterval('PT'.$minutes.'M'));

                        $sql = 'SELECT COUNT(*) FROM '.$wpdb->spark_gf_failed_submissions.' WHERE date_created_gmt >= %s';
                        $query = $wpdb->prepare($sql, $cutoff->format('Y-m-d H:i:s'));
                        $count = intval($wpdb->get_var($query));
                        if ($count >= $threshold) {
                            $this->send_site_notification($count);
                        }
                    }

                    // And check if we've hit the individual form threshold
                    $form_settings = $this->get_form_settings($form);
                    if ($form_settings['form_fail_count'] !== '0' && $form_settings['form_fail_time'] !== '0') { // Form setting of zero means notifications are disabled for this form (but blank means use global settings)
                        $threshold = !empty(intval($form_settings['form_fail_count'])) ? intval($form_settings['form_fail_count']) : intval($settings['form_fail_count']);
                        $minutes = !empty(intval($form_settings['form_fail_time'])) ? intval($form_settings['form_fail_time']) : intval($settings['form_fail_time']);

                        if (!empty($threshold) && !empty($minutes)) { // Empty value in either field means notifications are disabled
                            $cutoff = clone $datetime_gmt;
                            $cutoff->sub(new DateInterval('PT'.$minutes.'M'));

                            $sql = 'SELECT COUNT(*) FROM '.$wpdb->spark_gf_failed_submissions.' WHERE form_id = %d AND date_created_gmt >= %s';
                            $query = $wpdb->prepare($sql, $form['id'], $cutoff->format('Y-m-d H:i:s'));
                            $count = intval($wpdb->get_var($query));
                            if ($count >= $threshold) {
                                $this->send_form_notification($count, $form);
                            }
                        }
                    }
                }
            }

            return $validation_result;
        }

        public function add_form_action($actions, $form_id) {
            $actions['spark_gf_failed_submissions'] = $this->get_menu_item($form_id);
            return $actions;
        }

        public function toolbar_menu($menu_items, $form_id) {
            $menu_items['spark_gf_failed_submissions'] = $this->get_menu_item($form_id);
            return $menu_items;
        }

        private function get_menu_item($form_id) {
            return array(
                    'label' => __('Failed Submissions', 'spark-gf-failed-submissions'),
                    'title' => __('View failed submissions for this form', 'spark-gf-failed-submissions'),
                    'icon' => '<i class="fa fa-exclamation-circle fa-lg"></i>',
                    'url' => self_admin_url('admin.php?page=spark_gf_failed_submissions&id='.$form_id),
                    'menu_class' => 'gf_form_toolbar_failed_submissions',
                    'link_class' => rgget('page') == 'spark_gf_failed_submissions' ? 'gf_toolbar_active' : '',
                    'capabilities' => array('gravityforms_view_entries', 'gravityforms_edit_entries', 'gravityforms_delete_entries'),
                    'priority' => 799, // immediately after Entries
            );
        }

        public function create_menu($menus) {
            $menus[] = array('name' => 'spark_gf_failed_submissions', 'label' => __('Failed Submissions', 'spark-gf-failed-submissions'), 'callback' => array($this, 'admin_page'));
            return $menus;
        }

        public function admin_page() {
            if (!GFCommon::ensure_wp_version()) {
                return;
            }

            $forms = RGFormsModel::get_forms(null, 'title');
            $form_id = RGForms::get('id');
            if (sizeof($forms) == 0) {
?>
<div style="margin: 50px 0 0 10px;">
	<?php echo sprintf( esc_html__("You don't have any active forms. Let's go %screate one%s", 'gravityforms'), '<a href="?page=gf_new_form">', '</a>'); ?>
</div>
<?php
            } else {
                if (empty($form_id)) {
                    $form_id = $forms[0]->id;
                }
                global $wpdb;

                $form = GFFormsModel::get_form_meta($form_id);

//                 wp_print_styles(array('thickbox'));

                echo GFCommon::get_remote_message();
?>
<div class="wrap <?php echo GFCommon::get_browser_class() ?>">
<?php
                GFCommon::form_page_title($form);
                GFCommon::display_admin_message();
                GFCommon::display_dismissible_message();
                GFForms::top_toolbar();

                switch ($this->get_page()) {
                    case 'submission_detail':
                        $id = (int)$_GET['sid'];
                        $form_id = (int)$_GET['id'];
                        $submission = Spark_Gf_Failed_Submissions_Api::get_submission($id);
                        $fields = Spark_Gf_Failed_Submissions_Api::get_submission_fields($id);
                        $form = GFAPI::get_form($form_id);
                        $form_fields = array();
                        foreach ($form['fields'] as $field) {
                            $form_fields[$field->id] = $field->label;
                        }
                        $screen = get_current_screen();
?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			toggleNotificationOverride(true);
			if (typeof postboxes != 'undefined') {
				jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				postboxes.add_postbox_toggles( <?php echo json_encode($screen->id); ?>);
			}
		});
    </script>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <table class="widefat fixed entry-detail-view" cellspacing="0">
                    <thead>
                        <tr>
                            <th id="details" colspan="2"><?php echo $form['title']; ?> : <?php echo sprintf(__('Failed Submission # %d', 'spark-gf-failed-submissions'), $id); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                        foreach ($fields as $field) {
?>
                        <tr>
                            <td colspan="2" class="entry-view-field-name"><?php echo $form_fields[$field->field_id]; ?></td>
                        </tr>
                        <tr>
                            <td class="entry-view-field-value"><?php echo maybe_unserialize($field->submitted_value); ?></td>
                            <td class="entry-view-field-value"><?php echo $field->validation_message; ?></td>
                        </tr>
<?php
                        }
?>
                   </tbody>
                </table>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <div id="side-sortables">
                    <div id="submitdiv" class="postbox">
                        <h2><span><?php echo __('Failed Submission', 'spark-gf-failed-submissions'); ?></span></h2>
                        <div class="inside">
                            <div id="submitcomment" class="submitbox">
                                <div id="minor-publishing" style="padding:10px;">
                                    <?php echo __('Submission ID', 'spark-gf-failed-submissions'); ?>: <?php echo $id; ?><br><br>
                                    <?php echo __('Submitted on', 'gravityforms'); ?>: <?php echo get_date_from_gmt($submission->date_created_gmt, get_option('date_format').' '.get_option('time_format')); ?><br><br>
                                    <?php echo __('User IP', 'gravityforms'); ?>: <?php echo $submission->user_ip; ?><br><br>
<?php
                        if (!empty($submission->submitted_by)) {
                            $user = new WP_User($submission->submitted_by);
                            $user_details = '<a href="'.get_edit_user_link($user->ID).'" target="_blank">'.$user->display_name.' ('.$user->user_email.')</a>';
                        } else {
                            $user_details = $submission->user_email;
                        }
                        if (!empty($user_details)) {
?>
                                    <?php echo __('User', 'gravityforms'); ?>: <?php echo $user_details; ?><br><br>
<?php
                        }
?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
                        break;
                    case 'submission_list':
                        $column_headings  = '            <tr>'."\n";
                        $column_headings .= '                <th style="" class="manage-column column-primary" id="id" scope="col">'.__('Submission ID', 'spark-gf-failed-submissions').'</th>'."\n";
                        $column_headings .= '                <th style="" class="manage-column column-date" id="date" scope="col">'.__('Date', 'spark-gf-failed-submissions').'</th>'."\n";
                        $column_headings .= '                <th style="" class="manage-column" id="user" scope="col">'.__('Submitted By', 'spark-gf-failed-submissions').'</th>'."\n";
                        $column_headings .= '                <th style="" class="manage-column" id="message" scope="col">'.__('Error Message', 'spark-gf-failed-submissions').'</th>'."\n";
                        $column_headings .= '                <th style="" class="manage-column" id="ip" scope="col">'.__('IP Address', 'spark-gf-failed-submissions').'</th>'."\n";
                        $column_headings .= '            </tr>'."\n";

                        $failed_submissions = Spark_Gf_Failed_Submissions_Api::get_submissions($form_id);
                        echo '    <div class="tablenav top"></div>'."\n";
                        echo '    <table class="wp-list-table widefat fixed striped">'."\n";
                        echo '        <thead>'."\n";
                        echo $column_headings;
                        echo '        </thead>'."\n";
                        echo '        <tbody id="the-list">'."\n";
                        if (empty($failed_submissions)) {
                            echo '<tr class="no-items"><td class="colspanchange" colspan="4">'.__('This form does not have any failed submissions yet.', 'spark-gf-failed-submissions').'</td></tr>'."\n";
                        } else {
                            foreach ($failed_submissions as $failed_submission) {
                                $submission_date = get_date_from_gmt($failed_submission->date_created_gmt, get_option('date_format').' '.get_option('time_format'));
                                if (!empty($failed_submission->submitted_by)) {
                                    $user = new WP_User($failed_submission->submitted_by);
                                    $user_details = '<a href="'.get_edit_user_link($user->ID).'" target="_blank">'.$user->display_name.' ('.$user->user_email.')</a>';
                                } else {
                                    $user_details = $failed_submission->user_email;
                                }
                                echo '            <tr class="type-page status-publish hentry iedit author-other level-0" id="lineitem-'.$failed_submission->id.'">'."\n";
                                echo '                <td class="">'.$failed_submission->id.'</td>'."\n";
                                echo '                <td class="date"><a href="'.$this->generate_submission_detail_link($failed_submission->id).'">'.$submission_date.'</a></td>'."\n";
                                echo '                <td class="">'.$user_details.'</td>'."\n";
                                echo '                <td class="">'.$failed_submission->validation_message.'</td>'."\n";
                                echo '                <td class="">'.$failed_submission->user_ip.'</td>'."\n";
                                echo '            </tr>'."\n";
                            }
                        }
                        echo '        </tbody>'."\n";
                        echo '        <tfoot>'."\n";
                        echo $column_headings;
                        echo '        </tfoot>'."\n";
                        echo '    </table>'."\n";
                    break;
                }
?>
</div>
<?php
            }
        }

        private function check_number($value) {
            if (!is_numeric($value)) {
                return false;
            }
            return is_int($value+0);
        }

        public function validate_number($field, $value) {
            if (empty($value)) {
                return;
            }
            if (!$this->check_number($value)) {
                $message = __('Must be an integer greater than or equal to zero', 'spark-gf-failed-submissions');
                $this->set_field_error($field, $message);
            }
        }

        public function validate_email($field, $value) {
            $value_clean = is_email($value);
            if ($value !== $value_clean) {
                $message = __('Invalid email address', 'spark-gf-failed-submissions');
                $this->set_field_error($field, $message);
            }
        }

        private function send_site_notification($count) {
            $subject = __('Site-wide failed submissions threshold reached', 'spark-gf-failed-submissions');
            $message = sprintf(__('%d form submissions have failed in the last %d minutes on %s', 'spark-gf-failed-submissions'), $count, $this->get_plugin_setting('site_fail_time'), get_site_url());
            return $this->send_notification($subject, $message);
        }

        private function send_form_notification($count, array $form) {
            $subject = sprintf(__('Failed submissions threshold reached for form: %s', 'spark-gf-failed-submissions'), $form['title']);
            $message = sprintf(__('%d form submissions have failed in the last %d minutes on %s for the form "%s"', 'spark-gf-failed-submissions'), $count, $this->get_plugin_setting('form_fail_time'), get_site_url(), $form['title']);
            return $this->send_notification($subject, $message);
        }

        private function send_notification($subject, $message) {
            // Track send time
            set_transient($this->interval_transient, current_time('timestamp'), $this->get_plugin_setting('email_interval')*MINUTE_IN_SECONDS);

            // Send email
            $to = $this->get_plugin_setting('notification_email');
            return wp_mail($to, $subject, $message);
        }

        /**
         * Returns the timezone string for a site, even if it's set to a UTC offset
         *
         * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
         *
         * @return string valid PHP timezone string
         */
        private function wp_get_timezone_string() {
            // if site timezone string exists, return it
            if ($timezone = get_option('timezone_string')) {
                return $timezone;
            }

            // get UTC offset, if it isn't set then return UTC
            if (0 === ($utc_offset = get_option('gmt_offset', 0))) {
                return 'UTC';
            }

            // adjust UTC offset from hours to seconds
            $utc_offset *= 3600;

            // attempt to guess the timezone string from the UTC offset
            if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
                return $timezone;
            }

            // last try, guess timezone string manually
            $is_dst = date('I');

            foreach (timezone_abbreviations_list() as $abbr) {
                foreach ($abbr as $city) {
                    if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) {
                        return $city['timezone_id'];
                    }
                }
            }

            // fallback to UTC
            return 'UTC';
        }

        private function get_page() {
            if (rgget('page') == 'spark_gf_failed_submissions' && (!rgget('view') || rgget('view') == 'submissions')) {
                return 'submission_list';
            }

            if (rgget('page') == 'spark_gf_failed_submissions' && rgget('view') == 'submission') {
                return 'submission_detail';
            }

            return false;
        }

        /**
         * Get link to view failed submission detail
         * @param integer $submission_id
         * @return string
         * @since 1.1.0
         */
        private function generate_submission_detail_link($submission_id) {
            $submission = Spark_Gf_Failed_Submissions_Api::get_submission($submission_id);
            if ($submission) {
                return self_admin_url('admin.php?page=spark_gf_failed_submissions&view=submission&id='.$submission->form_id.'&sid='.$submission_id);
            }
        }
    }
}

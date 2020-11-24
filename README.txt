=== Spark GF Failed Submissions ===
Contributors: markparnell
Tags: gravity forms, gravity, forms, validation, failed submissions, logging
Requires at least: 3.0.1
Tested up to: 5.6
Requires PHP: 7.0
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track failed form submissions and get notified when they reach a customisable threshold. Requires Gravity Forms.

== Description ==

> This plugin requires [Gravity Forms](https://gravityforms.com/).

Ever wonder how often people try to fill in your forms but get an error when they hit submit? Want to be notified when failures suddenly increase?

So do we, which is why we built this plugin.

== Installation ==

We recommend you install the plugin via the automated installer interface within Wordpress. Just search for "Spark GF Failed Submissions", then install and activate.
If for some reason you are unable to use this and need to install the plugin manually, you can follow these steps:

1. Download the `spark-gf-failed-submissions.zip` to your computer and unzip it
1. Upload the `spark-gf-failed-submissions` folder into your site's `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

It sets what we believe to be sensible defaults, but once installed you'll want to make sure the plugin is set up to best meet your needs:

1. Configure notification settings in Forms -> Settings -> Failed Submissions
1. Override notification settings for individual forms as needed
1. Configure failed submission blocking on each form as needed

== Screenshots ==

1. Plugin settings page
2. Form settings page

== Changelog ==

= 1.3.1 =
25th November 2020

* Updates for compatibility with Gravity Forms 2.5

= 1.3.0 =
4th September 2020

* Added ability to filter failed submission list by ID, error message or IP address
* Updated password fields to hide submitted value

= 1.2.0 =
13th May 2020

* Added ability to delete individual failed submissions from both list and individual detail view
* Added ability to bulk delete failed submissions
* Added pagination to failed submissions list
* Added ability to automatically block submissions when failures reach defined threshold

= 1.1.1 =
4th December 2019

* Fixed display of submitted values for complex fields
* Fixed PHP warnings when no specific form settings have been configured

= 1.1.0 =
25th February 2019

* Added setting to limit emails to a specified interval
* Added failed submission detail view
* Fixed issue where GF admin scripts/styles were being enqueued on the front end
* Fixed capture and display of submitted email address

= 1.0.0 =
7th September 2018

* Initial release

== Frequently Asked Questions ==

= What is a "failed submission"? =

Any time someone tries to submit a Gravity Form resulting in a validation error.

= Why would I want to track failed submissions? =

If you process any sort of payments through Gravity Forms, you want to know as soon as there's a problem.
If your site suddenly stops talking to your payment gateway for example, you want to be notified immediately, rather than having to wait for customers to tell you.

The tracked data also enables deeper analysis of your forms, giving you insight into where submissions fail. 
You can use this information to craft better forms, increasing your conversion rate!

= Can this plugin block repeated failed submissions? =

Yes! In the settings for each form you can set a limit on the number of failed submissions from the same user in a given timeframe. Once the limit is reached, further attempts to submit the form by the same user will result in an error, even if the submitted data is otherwise valid.

= How can I help? =

The most important thing you can do is to download and use the plugin! Beyond that...

* If you are able to help with translating the plugin into another language please contact us at plugins@sparkweb.com.au.
* If you have any suggestions for new features, or you believe you've found a bug, please check the support forums to make sure it hasn't already been requested/reported by someone else. If you believe it's a new one, create a new support topic with as much detail as possible. In the case of bug reports, please include specific steps to reproduce the issue.

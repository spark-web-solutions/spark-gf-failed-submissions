=== Spark GF Failed Submissions ===
Contributors: markparnell
Tags: gravity forms, gravity, forms, validation, failed submissions, logging
Requires at least: 3.0.1
Tested up to: 5.3
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track failed form submissions and get notified when they reach a customisable threshold. Requires Gravity Forms.

== Description ==

> This plugin requires [Gravity Forms](https://gravityforms.com/).

Ever wonder how often people try to fill in your forms but get an error when they hit submit? Want to be notified when failures suddenly increase?

So do we, which is why we built this plugin.

== Installation ==

We recommend you install the plugin via the automated installer interface within Wordpress.
If for some reason you are unable to use this and need to install the plugin manually, you can follow these steps:

1. Download the `spark-gf-failed-submissions.zip` to your computer and unzip it
1. Upload the `spark-gf-failed-submissions` folder into your site's `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

Once installed, you'll want to make sure the plugin is set up to best meet your needs:

1. Configure plugin settings in Forms -> Settings -> Failed Submissions
1. Override per-form as needed

== Screenshots ==

1. Plugin settings page
2. Form settings page

== Changelog ==

= 1.1.0 =
* New setting to limit emails to a specified interval
* New failed submission detail view
* Fixed issue where GF admin scripts/styles were being enqueued on the front end
* Fixed capture and display of submitted email address

= 1.0.0 =
* Initial release

== Frequently Asked Questions ==

= What is a "failed submission"? =

Any time someone tries to submit a Gravity Form resulting in a validation error.

= Why would I want to track failed submissions? =

If you process any sort of payments through Gravity Forms, you want to know as soon as there's a problem.
If your site suddenly stops talking to your payment gateway for example, you want to be notified immediately, rather than having to wait for customers to tell you.

The tracked data also enables deeper analysis of your forms, giving you insight into where submissions fail. 
You can use this information to craft better forms, increasing your conversion rate!

= How can I help? =

The most important thing you can do is to download and use the plugin! Beyond that...

* If you are able to help with translating the plugin into another language please contact us at plugins@sparkweb.com.au
* If you have any suggestions for new features, or you believe you've found a bug, please check the support forums to make sure it hasn't already been requested/reported by someone else. If you believe it's a new one, create a new support topic with as much detail as possible. In the case of bug reports, please include specific steps to reproduce the issue.

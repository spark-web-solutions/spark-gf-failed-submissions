=== Spark GF Failed Submissions ===
Contributors: markparnell
Donate link: https://sparkweb.com.au
Tags: gravity forms, gravity, forms, validation, failed submissions, logging
Requires at least: 3.0.1
Tested up to: 4.9.8
Requires PHP: 7.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track failed form submissions and get notified when they reach a customisable threshold. Requires Gravity Forms.

== Description ==

> This plugin requires [Gravity Forms](https://gravityforms.com/).

Ever wonder how often people try to fill in your forms but get an error when they hit submit? Want to be notified 
when failures suddenly increase?

So do we, which is why we built this plugin.

== Installation ==

1. Upload `spark-gf-failed-submissions.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure plugin settings in Forms -> Settings -> Failed Submissions
1. Override per-form as needed

== Screenshots ==

1. Plugin settings page
2. Form settings page

== Changelog ==

= 1.0.0 =
* Initial release

== Frequently Asked Questions ==

= What is a "failed submission"? =

Any time someone tries to submit a Gravity Form resulting in a validation error.

= Why would I want to track failed submissions? =

If you process any sort of payments through Gravity Forms, you want to know as soon as there's a problem.
If your site suddenly stops talking to your payment gateway for example, you want to be notified immediately,
rather than having to wait for customers to tell you.

The tracked data also enables deeper analysis of your forms, giving you insight into where submissions fail. 
You can use this information to craft better forms, increasing your conversion rate!

= Why isn't it available in my language? =

Most likely because no one has offered to translate it into that language yet! We're always looking for people
to help us translate it into more languages - if you're able to help please contact us at plugins@sparkweb.com.au.

=== Mailman Registration ===
Contributors: heikoch
Tags: mailman, newsletter
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows prospective customers to register on to a Mailman list.

== Description ==

The plugin creates a txt file where the E-mail addresses are stored in the associated widget form in the uploads folder of WordPress.
This txt file can then be used to write to the interested parties through a cron job on the mailing list.

== Installation ==

1. Upload the content from the plugin zip file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin in the admin section in your WordPress
1. Place the Widget in your Sidebar

== Frequently Asked Questions ==

= What is the MailMan command for the cron job? =
/usr/sbin/add_members -r /pathtoyourwordpress/wp-content/uploads/yourfilename.txt nameofthemailinglist && echo "">/pathtoyourwordpress/wp-content/uploads/yourfilename.txt
The exact path you see in the admin area -> Settings -> MM-Registration.
= What is the Shortcode? =
[mm_registration msg_thank_you="your_msg_thank_you" msg_error="your_msg_error" msg_email_not_valid="your_msg_email_not_valid"]

== Screenshots ==

== Changelog ==
= 0.1 =
    * Initial Release
= 0.2 =
    * Add Shortcode functionality

== Upgrade Notice ==



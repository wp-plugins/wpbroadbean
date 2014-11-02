=== Plugin Name ===
Contributors: wpmarkuk
Donate link: http://markwilkinson.me/saythanks
Tags: jobs, recruitment
Requires at least: 3.9
Tested up to: 3.9.1
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

** Beta Plugin - it works but is rusty! **
WP Broadbean is a plugin allowing jobs added to Broadbean to show in your WordPress site.

== Description ==

Developed by [Mark Wilkinson](http://markwilkinson.me) WP Broadbean as a plugin to integrate your WordPress website with the [Broadbean Adcourier](http://www.broadbean.com/agency-recruiters-us.html) system from Broadbean Technology.

Broadband Adcourier allows you to integrate a Broadbean feed from your Adcourier account to send jobs added there to your WordPress website and have them show on your own site.

= What Does the Plugin Do? =

The plugin adds a custom post type to deal with the jobs delivered by the Broadbean feed as well as a number of custom taxonomies so jobs can be grouped. The plugin also provides a custom post edit screen for jobs to allow specific custom meta data to easily be edited and added for each job such as salary and contact email addresses etc.

WP Broadbean also provides an end point for accept a job feed sent by Broadbean in order for jobs added in Broadbean to appear in your WordPress website.

== Installation ==

To install the plugin:

1. Upload `wpbroadbean` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the settings page under WP Broadbean > Settings
4. Enter a username and password as well as choosing a page to use for your application form.
5. Add the application form short code to your apply page
6. Complete your [Broadbean feed request here](http://api.adcourier.com/docs/index.cgi?page=jobboards_register)

== Frequently Asked Questions ==

= Do Broadbean charge for this? =

Yes they do. When you choose to include your own site in a multi job posting Broadbean have the setup a "feed" to your site and they charge a one off fee for this.

= What is the Posting URL which Broadbean should send their feed to? =

The plugin adds an endpoints at the following URL:

http://domain.com/?wpbb=broadbean

Of course replacing domain.com for your sites domain. This is the URL which Broadbean should post the feed to when a job is added to the Adcourier system.

= Is there some test XML data I could test the plugin on my site with? =

Yes you can use the following XML to try a test the feed works:

`
<?xml version="1.0" encoding="UTF-8"?>
<job>
    <command>add</command>
    <username>XXXXXXXXX</username>
    <password>XXXXXXXXXX</password>
    <contact_name>Bob Smith</contact_name>
    <contact_email>bob@smith.com</contact_email>
    <application_email>bob.12345.123@smith.aplitrak.com</application_email>
    <job_reference>ZZZZZZ</job_reference>
    <job_title>Another Test Engineer</job_title>
    <job_type>Contract</job_type>
    <job_duration>6 Months</job_duration>
    <job_startdate>ASAP</job_startdate>
    <job_description>This is the detailed description</job_description>
    <job_short_description>This is the short description</job_short_description>
    <job_location>London</job_location>
    <job_category>Marketing</job_category>
    <salary_currency>gbp</salary_currency>
    <salary>XXXXXX</salary>
    <salary_per>annum</salary_per>
    <featured_job>1</featured_job>
</job>
`

= What if I want to use different fields that provided in the end point in wpbb-inbox.php? =

The plugin is extensible and should you wish to use a completely bespoke end point file you can add a file named `inbox.php` into a folder named `wpbb` in your theme and this will be used instead to process the feed.

== Screenshots ==

Coming soon…

== Changelog ==

= 0.8 =
* Add the select2 js library for select input in the metaboxes

= 0.7 =
* Corrected a type in the email header function that resulted in a semi-colon appearing before the email content.

= 0.6 =
* Added the ability to use WYSIWYG when adding your own settings to the setting page
* Removed the post type support filters as post type support can be added with add_post_type_support()

= 0.5 =
* Corrected issue where using an inbox.php file from the theme folder would not work.

= 0.4 =
* Added filters for meta box fields in applications and job post types. This allows devs to be able to add to or remove existing fields from a metabox.

= 0.3 =
* Removed the admin stylesheet - use dashicons for the admin menu icon
* Removed filterable post type labels, not needed as core provides this functionality
* Add additional filters for post title and post editor content
* General bug fixes and code comment updates

= 0.2 =
* Minor bug fixes

= 0.1 =
* Initial Beta Release

== Upgrade Notice ==
Update through the WordPress admin as notified.
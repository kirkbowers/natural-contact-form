=== Natural Contact Form ===
Contributors: kirkbowers
Tags: contact form,page guard,spam protection,email,MailChimp,opt in,optin
Requires at least: 3.1.0
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Natural Contact Form provides contact forms that are easy to create and use.  The email messages you receive from your site's visitors are formatted like regular emails and set up so you can just "Reply" naturally.

(Requires PHP version 5.3 or greater.)

== Description ==

Natural Contact Form provides contact forms that are easy to create and use.  Unlike general purpose form generators, it focuses on one thing (contact forms) and does it well.

Features include:

#### More Natural "Reply To"

Most WordPress plugins that generate input forms, especially those that are general purpose (not specialized to strictly "contact me" forms), typically send you an email from yourself (or some address in your domain like `donotreply`) and spew user's input as key value pairs in the email body.  Something like:

    Name: Arthur Dent
    Email: dent@example.com
    Message: I seem to have misplaced my towel...
    
This makes replying to the message awkward.  Natural Contact Form does just one thing, contact forms, and does it in (as the name suggests) a more natural way.  The user's name and email are set as the `reply-to` field in the email headers, and the email body is just the user's message.  

This makes replying no different than it normally is when someone emails you directly.

#### Spam protection

Natural Contact Form has built in "Honey pot" spam protection.  If a bot fills in the hidden honey pot form field, you don't receive the mail.

#### Unlimited contact forms for different parts of the site

Each contact form is configured separately in the WordPress dashboard.  In order to specify which form should appear on a given page in the site, the form's ID is given to the plugin's shortcode:

    [natural-contact-form id="case-study"]
    
#### Easy styling beyond what your theme may provide

Most themes do some degree of styling on text inputs.  However, they rarely put any whitespace between the different form fields, or have a way to highlight a field where an error occurred.  This plugin provides two ways to deal with that.

One, if you aren't a developer, there are options to easily size the contact form input fields, the space between them, and change the color to highlight them when a required field is left blank.  No coding necessary.

Or two, if you are a developer or work with one, the plugin makes it easy to add arbitrary CSS to style each contact form individually.

#### Optional "Page Guard" disallows direct navigation to the "Thank you" page

Typically the "thank you" page displayed after a contact form is a regular WordPress "Page" that can be visited simply by typing in it's URL directly into the browser address bar.  Usually this is harmless, but if you have something on a "thank you" page, like a lead magnet download, that you don't want visitors to get to without actually providing their contact info, it can be a problem.  Plus, direct navigation to "thank you" pages can skew analytics.

The "Page Guard" features allows you to require that a particular contact form be filled in successfully before displaying the guarded page.  If direct navigation is attempted, the visitor will either be shown a 404 or be bounced to a different page (likely the home or the contact page), depending on your settings.  Behind the scenes, this is handled with a cookie.  The contact form sets the cookie, and if the "thank you" page doesn't find the required cookie, the visitor is redirected.

#### Optional integration with popular Email Service Providers

If you want to be contacted directly through an opt-in so you can follow up with new subscribers personally, contact forms can be configured to serve as both a "contact me" and an email list sign up.

Currently MailChimp is supported.  Other email providers coming soon!

#### Best of all, it's free!

General purpose form generators charge a premium for providing functionality you don't need if all you need is a contact form.

#### Full User Guide

For a more detailed user guide, visit [the Natural Contact Form homepage](http://www.kirkbowers.com/plugins/natural-contact-form).

== Installation ==

#### As a plugin from the WordPress dashboard

1. Visit 'Plugins > Add New'
2. Search for 'Natural Contact Form'
3. Activate Natural Contact Form from your Plugins page

#### As a plugin from WordPress.org

1. Download Natural Contact Form and unzip the file
2. Upload the `natural-contact-form` folder to the `/wp-content/plugins` directory
3. Activate Natural Contact Form from your Plugins page

== Frequently Asked Questions ==

= Can I collect either someone's name as one field or first name/last name separately? =

Yes.  Whether to ask for "Name" or "First Name" and "Last Name" is a configurable option.  In fact, you can even change how the input fields are labeled.  You can call it "Full Name" or "Given name" if you like.

= Can I get rid of the "Message" field if I'm using the contact form as an opt-in for a newsletter? =

Yes.  Or you can change the label on the message field to anything you like, such as "Ask me anything" or "What's your fondest wish?"

= It doesn't currently support my email service provider. Will you ever add support for "XYZ" in the future? =

Most likely, yes.  I started with support for MailChimp for a few reasons. Someone I'm working with had a direct need for it, MailChimp's developer tools are fantastic, and it's free to set up a test account with them to develop an integration.

If you need this plugin to integrate with your email provider, drop me a note [at my contact form](http://www.kirkbowers.com/contact) and tell me which provider you need me to add.  I'll have to look into their developer tools and see if they allow something similar to what I did with MailChimp.  If so, I'll hook you up!

= You mentioned your own contact page in the last FAQ. Is that powered by this plugin? =

Naturally!

== Screenshots ==

1. Highly configurable.  Change what fields visitors need to fill out, how they are displayed, what they are called... On a form by form basis.
2. Change the look of your contact forms without digging into the stylesheet.
3. Optionally hook your contact form into a 3rd party email service provider.
4. Adding a contact form to a page is as simple as using the [natural-contact-form] WordPress shortcode.
5. Optionally disallow direct navigation to the "thank you" page that visitors are sent to after filling out a contact form.
6. Beautiful contact forms with optional highlighting when a visitor doesn't fill out a required field.

== Changelog ==

= 1.0.0 =
* Initial release

= 1.1.0 =
* Added an optional phone number field to the contact form.
* Fixed a minor incompatibility with WooCommerce.

== Upgrade Notice ==

= 1.0.0 =
Initial release, nothing to upgrade!

= 1.1.0 =
The "Phone" field will default to "off".  Upgrade will be backwards compatible.




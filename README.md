# natural-contact-form

Natural Contact Form is a WordPress plugin providing contact forms that are easy to create and use. The email messages you receive from your site's visitors are formatted like regular emails and set up so you can just "Reply" naturally.

Features include:

## More natural reply to

Most WordPress plugins that generate input forms, especially those that are general purpose (not specialized to strictly "contact me" forms), typically send you an email from yourself (or some address in your domain like `donotreply`) and spew user's input as key value pairs in the email body.  Something like:

    Name: Arthur Dent
    Email: dent@example.com
    Message: I seem to have misplaced my towel...
    
This makes replying to the message awkward.  Natural Contact Form does just one thing, contact forms, and does it in (as the name suggests) a more natural way.  The user's name and email are set as the `reply-to` field in the email headers, and the email body is just the user's message.  This makes replying no different than it normally is when someone emails you directly.

## Spam protection

Natural Contact Form has built in "Honey pot" spam protection.  If a bot fills in the hidden honey pot form field, you don't receive the mail.

## Unlimited contact forms for different parts of the site

Each contact form is configured separately in the WordPress dashboard.  In order to specify which form should appear on a given page in the site, the form's ID is given to the plugin's shortcode:

    [natural-contact-form id="case-study"]
    
## Optional "Page Guard" disallows direct navigation to "Thank you" page

Typically the "thank you" page displayed after a contact form is a regular WordPress "Page" that can be visited simply by typing in it's URL directly into the browser address bar.  Usually this is harmless, but if you have something on a "thank you" page, like a lead magnet download, that you don't want visitors to get to without actually providing their contact info, it can be a problem.  Plus, direct navigation to "thank you" pages can skew analytics.

The "Page Guard" features allows you to require that a particular contact form be filled in successfully before displaying the guarded page.  If direct navigation is attempted, the visitor will either be shown a 404 or be bounced to a different page (likely the home or the contact page), depending on the settings.  Behind the scenes, this is handled with a cookie.  The contact form sets the cookie, and if the "thank you" page doesn't find the required cookie, the visitor is redirected.

## Optional integration with popular Email Service Providers

If you want to be contacted directly through an opt-in so you can follow up with new subscribers personally, contact forms can be configured to serve as both a "contact me" and an email list sign up.

Currently MailChimp is supported.  Other email providers coming soon!


# MailGun Mailing List Subscriptions

Add a Mailgun subscription form to your WordPress site. Your visitors can use the form to subscribe to your lists using the Mailgun API.

## Installation and Setup

Install and activate just as a normal WordPress plugin.

You'll find the "Mailgun Lists" settings page in the Settings admin menu. Here, you can setup your API keys, control which lists you're making available, and create custom descriptions for your lists.

## Subscription Form Widget

The plugin creates a widget called "Mailgun List Subscription Form". It includes options to set the title, an optional description, and the mailing lists that will be available in the widget.

## Shortcode

The plugin creates a shortcode: `[mailgun_subscription_form]`. This displays the same form as the widget. Optional parameters include:

* `lists` - The email addresses of the lists that should be available in the form.
* `description` - The description that will display above the form.

### Hooks

`mailgun_form_message` - This action is fired to display notices and errors above the form.

`mailgun_form_content` - This action is fired to display the actual form.

`mailgun_enqueue_assets` - This action enqueues the plugin CSS when the form will be rendered.

`mailgun_css_path` - Filter the path to the plugin CSS file.

`mailgun_subscription_form_description` - Filter the rendering of the form description.

## Confirmation Emails

### Confirmation Email

You can set up templates for two emails the plugin will send.

When a user first submits the subscription form, the "Confirmation Email" is sent. Your template should contain the following shortcodes:

* `[link]` - This becomes a link back to your site with a unique code to confirm the user's subscription request.
* `[email]` - This is the user's email address.
* `[lists]` - This is a list of the lists the user opted to subscribe to.

#### Filters

`mailgun_confirmation_email_subject` - Edit the subject of the confirmation email.

`mailgun_confirmation_email_template` - Edit the confirmation email template.

`mailgun_confirmation_email_lists` - Edit the list of mailing lists in the email template.

### Welcome Email

After the user confirms, the "Welcome Email" is sent. This template can include:

* `[email]` - This is the user's email address.
* `[lists]` - This is a list of the lists the user opted to subscribe to.

#### Filters

`mailgun_welcome_email_subject` - Edit the subject of the welcome email.

`mailgun_welcome_email_template` - Edit the welcome email template.

`mailgun_welcome_email_lists` - Edit the list of mailing lists in the email template.

## Confirmation Page

The confirmation page is a standard WordPress Page. You can create your own, or the plugin will automatically create one for you. On this page, these shortcodes are supported (in addition to all other shortcodes you may have):

* `[mailgun_email]` - This is the user's email address.
* `[mailgun_lists]` - These are the lists the user subscribed to.

If a user visits the confirmation page without a valid confirmation URL, an error message will be displayed instead of the standard page contents.

# MailGun Post Notifications

Add notifications for new posts to a site with the Mailgun Subscriptions plugin.

## Installation and Setup

First install and activate the Mailgun Subscriptions plugin.

Then install and activate this plugin just as a normal WordPress plugin.

You'll find the "Mailgun Lists" settings page in the Settings admin menu. After setting up your Mailgun API settings, you can select a list that will receive notifications of new posts.

## Hooks

### `should_send_mailgun_post_notification`

Return `FALSE` to this filter to prevent a notification from sending.

```php
function my_filter_for_should_send_mailgun_post_notification( $should_send, $post_id ) {
	if ( get_post_meta( $post_id, '_some_interesting_meta_key', TRUE ) == 1 ) {
		$should_send = FALSE;
	}
	return $should_send;
}
add_filter( 'should_send_mailgun_post_notification', 'my_filter_for_should_send_mailgun_post_notification', 10, 2 );
```

### `mailgun_post_notification_post_types`

Filter the post types for which notifications will be sent. Defaults to just `post`.

```php
function my_filter_for_mailgun_post_notification_post_types( $post_types ) {
	$post_types[] = 'page'; // notify subscribers when a new Page is published
	return $post_types;
}
add_filter( 'mailgun_post_notification_post_types', 'my_filter_for_mailgun_post_notification_post_types', 10, 1 );
```

### `mailgun_post_notification_api_arguments`

Filter any of the arguments sent to Mailgun to send the email (e.g., filter the 'from' or 'reply-to' headers)

```php
function my_filter_for_mailgun_post_notification_api_arguments( $args ) {
	unset($args['text']); // never send text version of the email
	return $args;
}
add_filter( 'mailgun_post_notification_api_arguments', 'my_filter_for_mailgun_post_notification_api_arguments', 10, 1 );
```

## Templates

All emails are sent with both a text version and an HTML version. You can independently manage the templates for each.

The default templates can be found in the plugin's `email-templates` directory. To override the default templates, created the directory `mailgun` in your theme, and copy the `html` and `text` directories from the plugin into this directory. Your theme's directory structure should look like:

```
[your_theme]
   - mailgun
   |  - html
   |  |  - new-post.php
   |  - text
   |  |  - new-post.php
   - index.php
   - style.css
   - etc.
```

Within your templates, you have access to the global `$post` object, and normal template tags like `the_title()`, `the_permalink()`, and `the_excerpt()` will all work.
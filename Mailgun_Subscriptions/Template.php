<?php


namespace Mailgun_Subscriptions;


abstract class Template {
	public static function confirmation_email() {
		return __("Thank you for subscribing. Please visit [link] to confirm your subscription for [email].", 'mailgun-subscriptions');
	}

	public static function welcome_email() {
		return __("Your email address, [email], has been confirmed. You are now subscribed.", 'mailgun-subscriptions');
	}

	public static function confirmation_page() {
		return __("<p>Thank you for confirming your subscription. <strong>[mailgun_email]</strong> is now subscribed to this website.", 'mailgun-subscriptions');
	}
}

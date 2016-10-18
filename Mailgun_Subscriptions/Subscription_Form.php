<?php


namespace Mailgun_Subscriptions;


class Subscription_Form {
	public function display( $args ) {
		$args = wp_parse_args( $args, array(
			'description' => '',
			'list' => array(),
		));

		do_action( 'mailgun_enqueue_assets' );

		if ( !empty($_GET['mailgun-message']) ) {
			do_action( 'mailgun_form_message', $_GET['mailgun-message'], !empty($_GET['mailgun-error']), $this );
		}

		if ( empty($_GET['mailgun-message']) || !empty($_GET['mailgun-error']) ) {
			do_action( 'mailgun_form_content', $args, $this );
		}
	}

	/**
	 * @param string $message_code
	 * @param bool $error
	 * @param self $form
	 *
	 * @return void
	 */
	public static function form_message_callback( $message_code, $error, $form ) {
		$form->show_form_message( $message_code, $error );
	}

	/**
	 * @param string $message
	 * @param bool $error
	 *
	 * @return void
	 */
	protected function show_form_message( $message, $error = FALSE ) {
		if ( !is_array($message) ) {
			$message = array($message);
		}
		$error_class = $error ? ' error' : '';
		foreach ( $message as $code ) {
			echo '<p class="mailgun-message'.$error_class.'">', $this->get_message_string($code), '</p>';
		}
	}

	protected function get_message_string( $code ) {
		switch ( $code ) {
			case 'submitted':
				$message = 'Please check your email for a link to confirm your subscription.';
				break;
			case 'no-list':
				$message = 'Please select a mailing list.';
				break;
			case 'no-email':
				$message = 'Please enter your email address.';
				break;
			case 'invalid-email':
				$message = 'Please verify your email address.';
				break;
			case 'unsubscribed':
				$message = 'You have previously unsubscribed. Please contact us to reactivate your account.';
				break;
			case 'already-subscribed':
				$message = 'You are already subscribed. Please contact us if you have trouble receiving messages.';
				break;
			default:
				$message = $code;
				break;
		}
		$message = apply_filters( 'mailgun_message', $message, $code, 'widget' );
		return $message;
	}

	/**
	 * @param array $instance
	 * @param self $form
	 *
	 * @return void
	 */
	public static function form_contents_callback( $instance, $form ) {
		$form->do_form_contents($instance);
	}

	/**
	 * @param array $instance
	 *
	 * @return void
	 */
	protected function do_form_contents( $instance ) {
		static $instance_counter = 0;
		$instance_counter++;

		$description = apply_filters( 'mailgun_subscription_form_description', $instance['description'] );
		if ( $description ) {
			echo '<div class="mailgun-form-description">'.$description.'</div>';
		}

		printf('<form class="mailgun-subscription-form" method="post" action="%s">', $this->get_form_action());
		echo '<input type="hidden" name="mailgun-action" value="subscribe" />';
		printf( '<input type="hidden" value="%s" name="mailgun-list" />', esc_attr(Plugin::instance()->get_list_address()) );
		echo '<p class="email-address">';
		printf( '<label for="mailgun-email-address-%d">%s</label> ', $instance_counter, 'Email Address' );
		$default_email = '';
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$default_email = $user->user_email;
		}
		printf( '<input type="text" value="%s" name="mailgun-subscriber-email" size="20" id="mailgun-email-address-%d" />', $default_email, $instance_counter );
		echo '</p>';
		printf( '<p class="submit"><input type="submit" value="%s" /></p>', apply_filters( 'mailgun_subscription_form_button_label', 'Subscribe' ) );
		echo '</form>';
	}

	protected function get_form_action() {
		$url = $_SERVER['REQUEST_URI'];
		foreach ( array('mailgun-message', 'mailgun-error', 'mailgun-action', 'ref') as $key ) {
			$url = remove_query_arg($key, $url);
		}
		return $url;
	}
}

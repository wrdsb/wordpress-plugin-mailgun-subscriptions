<?php


namespace Mailgun_Subscriptions;


class Confirmation_Handler {
	protected $submission = array();
	protected $errors = array();

	/** @var Confirmation */
	protected $confirmation = NULL;

	public function __construct( $submission ) {
		$this->submission = $submission;
	}

	public function handle_request() {
		$this->get_confirmation();
		$this->validate_confirmation();
		if ( empty($this->errors) ) {
			$this->do_subscription();
		}
		if ( empty($this->errors) ) {
			$this->send_welcome_email();
			$this->confirmation->mark_confirmed();
		}
	}

	public function get_confirmation() {
		if ( !isset($this->confirmation) ) {
			$ref = $this->get_confirmation_id();
			if ( $ref ) {
				$this->confirmation = new Confirmation( $ref );
			} else {
				$this->confirmation = new Null_Confirmation();
			}
		}
		return $this->confirmation;
	}

	public function has_errors() {
		return !empty($this->errors);
	}

	protected function get_confirmation_id() {
		$id = isset( $this->submission['ref'] ) ? $this->submission['ref'] : '';
		return $id;
	}

	protected function validate_confirmation() {
		if ( !$this->confirmation->get_address() ) {
			$this->errors[] = 'not_found';
			return;
		}
		if ( !$this->confirmation->get_list() ) {
			$this->errors[] = 'no_list';
			return;
		}
		//if ( $this->confirmation->confirmed() ) {
			//$this->errors[] = 'already_confirmed';
			//return;
		//}
		if ( $this->confirmation->expired() ) {
			$this->errors[] = 'expired';
			return;
		}
	}

	protected function do_subscription() {
		$address = $this->confirmation->get_address();
		$list = $this->confirmation->get_list();
		$api = Plugin::instance()->api();
		$response = $api->post("lists/$list/members", array(
			'address' => $address,
			'upsert' => 'yes',
		));
		if ( !$response && $response['response']['code'] != 200 ) {
			$this->errors[] = 'subscription_failed';
		}
	}

	protected function send_welcome_email() {
		$address = $this->confirmation->get_address();
		$message = $this->get_welcome_email_message();
		if ( !empty($message) ) {
			wp_mail( $address, $this->get_welcome_email_subject(), $message );
		}
	}

	protected function get_welcome_email_subject() {
		return apply_filters( 'mailgun_welcome_email_subject', sprintf( '[%s] Your Subscription Is Confirmed', get_bloginfo('name') ) );
	}

	protected function get_welcome_email_message() {
		$message = $this->get_welcome_message_template();
		$message = str_replace( '[email]', $this->confirmation->get_address(), $message );
		$message = str_replace( '[list]', $this->get_formatted_list(), $message );
		return $message;
	}

	protected function get_welcome_message_template() {
		$template = get_option( 'mailgun_welcome_email_template', Template::welcome_email() );
		return apply_filters( 'mailgun_welcome_email_template', $template );
	}

	protected function get_formatted_list() {
		$requested_list = $this->confirmation->get_list();
		return $requested_list;
	}

	public function setup_page_data( $post ) {
		if ( empty($this->errors) ) {
			$page_content = "Thank you for confirming your subscription.";
			$post->post_content = $page_content;
			$GLOBALS['pages'] = array( $post->post_content );
			$post->post_title = "Subscription Confirmation";
		} else {
			$messages = array();
			if ( !isset($_GET['mailgun-message']) ) { // otherwise we got here from the subscription form
				foreach ( $this->errors as $error_code ) {
					$messages[] = '<p class="mailgun-message error">'.$this->get_message($error_code).'</p>';
				}
			}
			$page_content = implode($messages);
			$post->post_content = $page_content;
			$GLOBALS['pages'] = array( $post->post_content );
			$post->post_title = apply_filters('mailgun_error_page_title', __('Error Confirming Your Subscription', 'mailgun-subscriptions'));
		}
	}

	protected function get_message( $code ) {
		switch ( $code ) {
			case 'not_found':
				$message = 'Your request could not be found. Please try again.';
				break;
			case 'no_list':
				$message = 'There is no mailing list associated with your request. Please try again.';
				break;
			case 'already_confirmed':
				$message = 'You have already confirmed your request.';
				break;
			case 'expired':
				$message = 'Your request has expired. Please try again.';
				break;
			case 'subscription_failed':
				$message = 'We experienced a problem setting up your subscription. Please try again.';
				break;
			default:
				$message = $code;
				break;
		}
		return apply_filters( 'mailgun_message', $message, $code, 'confirmation' );
	}

	protected function get_subscription_form() {
		$list = Plugin::instance()->get_list();
		$form = new Subscription_Form();
		ob_start();
		$form->display(array(
			'description' => 'Description goes here.',
			'list' => $list,
		));
		return ob_get_clean();
	}
} 

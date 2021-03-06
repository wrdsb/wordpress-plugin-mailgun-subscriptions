<?php
namespace Mailgun_Subscriptions;

class Notification {
	protected $post_id = 0;

	public function __construct( $post_id ) {
		$this->post_id = (int)$post_id;
	}

	public function send() {
		$address = $this->get_list_address();
		if ( empty($address) ) {
			return;
		}

		$this->setup_post_global();
		$subject = $this->get_subject();
		$html = $this->get_html();
		$text = $this->get_text();
		$list_tag = $this->get_list_address();
		wp_reset_postdata();

		$notification_args = array(
			'from' => $this->get_from_header(),
			'to' => $address,
			'subject' => $subject,
			'text' => $text,
			'html' => $html,
			'h:Reply-To' => $address,
			# Can only accept one tag in our associative array
			'o:tag' => $list_tag,
		);

		$notification_args = apply_filters( 'mailgun_post_notification_api_arguments', $notification_args, $this->post_id );

		$api = \Mailgun_Subscriptions\Plugin::instance()->api();
		$api->post( $this->get_domain($address).'/messages', $notification_args);
	}

	protected function get_html() {
		global $post;
		$template = $this->get_template('html/new-post.php');
		ob_start();
		include($template);
		return $this->sanitize_string(ob_get_clean());
	}

	protected function get_text() {
		global $post;
		$template = $this->get_template('text/new-post.php');
		ob_start();
		include($template);
		return $this->sanitize_string(ob_get_clean());
	}

	protected function setup_post_global() {
		global $post;
		$post = get_post($this->post_id);
		setup_postdata( $post );
	}

	protected function get_template( $path ) {
		$file = locate_template('mailgun'.DIRECTORY_SEPARATOR.$path);
		if ( $file ) {
			return $file;
		}
		$plugin_path = Plugin::path( 'email-templates'.DIRECTORY_SEPARATOR.$path );
		if ( file_exists($plugin_path) ) {
			return $plugin_path;
		}
		return FALSE;
	}

	protected function get_list_address() {
		return Plugin::instance()->get_list_address();
	}

	protected function get_from_header() {
		$from_name = get_bloginfo( 'name' );
		$from_address = Plugin::instance()->get_list_address();
		return sprintf( '%s <%s>', $this->sanitize_string($from_name), $from_address );
	}

	protected function get_subject() {
		$subject = get_the_title();
		return $this->sanitize_string($subject);
	}

	protected function get_domain( $address ) {
		$parts = explode('@', $address);
		return end($parts);
	}

	protected function sanitize_string( $string ) {
		$string = html_entity_decode($string);
		return $string;
	}
}

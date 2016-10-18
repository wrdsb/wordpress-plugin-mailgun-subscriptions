<?php


namespace Mailgun_Subscriptions;


class Confirmation {
	const POST_TYPE = 'mailgun-confirmation';

	protected $id = '';
	protected $post_id = '';
	protected $address = '';
	protected $confirmed = FALSE;
	protected $list = '';

	public function __construct( $confirmation_id = '' ) {
		$this->id = $confirmation_id;
		if ( $this->id ) {
			$this->load();
		}
	}

	public function set_address( $address ) {
		$this->address = $address;
	}

	public function get_address() {
		return $this->address;
	}

	public function set_list( $list ) {
		$this->list = $list;
	}

	public function get_list() {
		return $this->list;
	}


	public function save() {
		if ( !$this->post_id ) {
			$this->id = empty($this->id) ? $this->generate_id() : $this->id;
			$this->post_id = wp_insert_post(array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'post_author' => 0,
				'post_title' => $this->id,
				'post_name' => $this->id,
			));
		}
		update_post_meta( $this->post_id, '_mailgun_subscriber_address', $this->address );
		update_post_meta( $this->post_id, '_mailgun_subscription_confirmed', $this->confirmed );
		update_post_meta( $this->post_id, '_mailgun_subscriber_list', $this->list );
	}

	protected function generate_id() {
		return wp_generate_password(32, false, false);
	}

	protected function load() {
		if ( empty($this->post_id) ) {
			$results = get_posts(array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'name' => $this->id,
				'posts_per_page' => 1,
				'fields' => 'ids',
			));
			if ( !$results ) {
				return;
			}
		}
		$this->post_id = reset($results);
		$this->address = get_post_meta($this->post_id, '_mailgun_subscriber_address', true);
		$this->list = get_post_meta($this->post_id, '_mailgun_subscriber_list', true);
		$this->confirmed = get_post_meta($this->post_id, '_mailgun_subscription_confirmed', true);
	}

	public function get_id() {
		return $this->id;
	}

	public function confirmed() {
		return $this->confirmed;
	}

	public function mark_confirmed() {
		$this->confirmed = TRUE;
		if ( $this->post_id ) {
			update_post_meta( $this->post_id, '_mailgun_subscription_confirmed', TRUE );
		}
	}

	public function expired() {
		if ( $this->post_id ) {
			$created = get_post_time('U', TRUE, $this->post_id);
			$age = time() - $created;

			$days = get_option( 'mailgun_confirmation_expiration', 7 );
			$threshold = $days * 24 * 60 * 60;
			return $age > $threshold;
		} else {
			return FALSE;
		}
	}
} 

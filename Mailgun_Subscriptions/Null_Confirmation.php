<?php


namespace Mailgun_Subscriptions;


class Null_Confirmation extends Confirmation {
	public function __construct( $confirmation_id = '' ) {
		$this->id = $confirmation_id;
	}

	public function set_address( $address ) {
		// do nothing
	}

	public function get_address() {
		return '';
	}

	public function set_list( $list ) {
		// do nothing
	}

	public function get_list() {
		return array();
	}

	public function save() {
		// do nothing
	}

	public function confirmed() {
		return FALSE;
	}

	public function expired() {
		return TRUE;
	}
} 

<?php
namespace Mailgun_Subscriptions;

class Confirmation_Page {
	function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'rewrite' ) );
		add_action( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_include', array( $this, 'change_template' ) );
	}

	function activate() {
		set_transient( 'vpt_flush', 1, 60 );
	}

	function rewrite() {
		add_rewrite_rule( '^subscription-confirmation$', 'index.php?subscription-confirmation=1', 'top' );

		if(get_transient( 'vpt_flush' )) {
			delete_transient( 'vpt_flush' );
			flush_rewrite_rules();
		}
	}

	function query_vars($vars) {
		$vars[] = 'subscription-confirmation';

		return $vars;
	}

	function change_template( $template ) {

		if( get_query_var( 'subscription-confirmation', false ) !== false ) {

			$newTemplate = locate_template( array( 'template-confirmation-page.php' ) );
			if( '' != $newTemplate )
				return $newTemplate;

			//Check plugin directory next
			$newTemplate = plugin_dir_path( __FILE__ ) . 'page-templates/template-confirmation-page.php';
			if( file_exists( $newTemplate ) )
				return $newTemplate;


		}

		//Fall back to original template
		return $template;

	}

}

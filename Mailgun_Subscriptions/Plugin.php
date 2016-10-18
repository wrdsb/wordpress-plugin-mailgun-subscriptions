<?php


namespace Mailgun_Subscriptions;


class Plugin {
	const VERSION = '2.0';

	/** @var Plugin */
	private static $instance = NULL;

	private static $plugin_file = '';

	/** @var Submission_Handler */
	private $submission_handler = NULL;

	/** @var Confirmation_Handler */
	private $confirmation_handler = NULL;

	/** @var Notifier */
	private $notifier = NULL;

	public function api( $public = FALSE ) {
		if ( $public ) {
			return new API(MAILGUN_SUBSCRIPTIONS_API_PUBLIC_KEY);
		} else {
			return new API(MAILGUN_SUBSCRIPTIONS_API_KEY);
		}
	}

	public function submission_handler() {
		return $this->submission_handler;
	}

	public function confirmation_handler() {
		return $this->confirmation_handler;
	}

	public function notifier() {
		if ( !isset($this->notifier) ) {
			$this->notifier = new Notifier();
		}
		return $this->notifier;
	}

	private function setup( $plugin_file ) {
		self::$plugin_file = $plugin_file;
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
		add_action( 'init', array( $this, 'setup_confirmations' ) );
		Cleanup::init();
		$this->setup_frontend_ui();

		if ( !is_admin() ) {
			if ( !empty($_POST['mailgun-action']) && $_POST['mailgun-action'] == 'subscribe' ) {
				$this->setup_submission_handler();
			}
			if ( !empty($_GET['mailgun-action']) && $_GET['mailgun-action'] == 'confirm' ) {
				$this->setup_confirmation_handler();
				add_action( 'wp', array( $this, 'setup_confirmation_page' ), 10, 0 );
			}
		}
		$this->setup_notification_listener();
	}

	private function setup_frontend_ui() {
		add_action( 'mailgun_form_message', array( __NAMESPACE__.'\\Subscription_Form', 'form_message_callback' ), 10, 3 );
		add_action( 'mailgun_form_content', array( __NAMESPACE__.'\\Subscription_Form', 'form_contents_callback' ), 10, 2 );
		$this->setup_widget();
		add_action( 'mailgun_enqueue_assets', array( $this, 'enqueue_assets' ), 10, 0 );
	}

	public function enqueue_assets() {
		$css_path = plugins_url( 'assets/mailgun-subscriptions.css', dirname(__FILE__) );
		$css_path = apply_filters( 'mailgun_css_path', $css_path );
		if ( $css_path ) {
			wp_enqueue_style( 'mailgun-subscriptions', $css_path, array(), self::VERSION );
		}
	}

	private function setup_widget() {
		add_action( 'widgets_init', array( __NAMESPACE__.'\\Widget', 'register' ), 10, 0 );
	}

	public function setup_confirmations() {
		$pt = new Post_Type_Registrar();
		$pt->register();
	}

	public function setup_confirmation_handler() {
		$this->confirmation_handler = new Confirmation_Handler($_GET);
		add_action( 'parse_request', array( $this->confirmation_handler, 'handle_request' ), 10, 0 );
	}

	private function setup_submission_handler() {
		$this->submission_handler = new Submission_Handler($_POST);
		add_action( 'parse_request', array( $this->submission_handler, 'handle_request' ), 10, 0 );
	}

	public function setup_confirmation_page() {
		add_filter( 'the_post', array( $this->confirmation_handler, 'setup_page_data' ), 10, 1 );
	}

	private function setup_notification_listener() {
		add_action( 'save_post', array( $this->notifier(), 'listen_for_saved_post' ), 10, 2 );
		add_action( 'shutdown', array( $this->notifier(), 'send_notifications' ), 0, 0 );
	}

	public function get_list() {
		$list = array(
			'address' => $this->get_list_address(),
			'name' => $this->get_list_name(),
			'description' => $this->get_list_description(),
		);
		return $list;
	}

	public function get_list_address() {
		$blog_details = get_blog_details(get_current_blog_id());
		$my_domain = $blog_details->domain;
		$my_slug = str_replace('/','',$blog_details->path);
		switch ($my_domain) {
			case "www.wrdsb.ca":
				if (empty($my_slug)) {
					return "www@hedwig.wrdsb.ca";
				} else {
					return "www-".$my_slug."@hedwig.wrdsb.ca";
				}
			case "staff.wrdsb.ca":
				if (empty($my_slug)) {
					return "staff@hedwig.wrdsb.ca";
				} else {
					return "staff-".$my_slug."@hedwig.wrdsb.ca";
				}
			case "schools.wrdsb.ca":
				if (empty($my_slug)) {
					return "schools@hedwig.wrdsb.ca";
				} else {
					return $my_slug."@hedwig.wrdsb.ca";
				}
			case "teachers.wrdsb.ca":
				if (empty($my_slug)) {
					return "teachers@hedwig.wrdsb.ca";
				} else {
					return "teachers-".$my_slug."@hedwig.wrdsb.ca";
				}
			case "wcssaa.ca":
				return "wcssaa@hedwig.wrdsb.ca";
			case "labs.wrdsb.ca":
				return "wplabs-mailgun-lab@hedwig.wrdsb.ca";
			case "www.stswr.ca":
				return "www@bigbus.stswr.ca";
			default:
				return "no-list@hedwig.wrdsb.ca";
			}
	}

	public function get_list_name() {
		return 'List Name';
	}

	public function get_list_description() {
		return 'List Description';
	}

	public static function init( $file ) {
		self::instance()->setup( $file );
	}

	public static function instance() {
		if ( !isset(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function autoload( $class ) {
		if (substr($class, 0, strlen(__NAMESPACE__)) != __NAMESPACE__) {
			//Only autoload libraries from this package
			return;
		}
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		//$path = dirname(self::$plugin_file) . DIRECTORY_SEPARATOR . $path . '.php';
		$path = self::path() . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($path)) {
			require $path;
		}
	}

	/**
 	* Get the absolute system path to the plugin directory, or a file therein
 	* @static
 	* @param string $path
 	* @return string
	*/
	public static function path( $path = '' ) {
		$base = dirname(self::$plugin_file);
		if ( $path ) {
			return trailingslashit($base).$path;
		} else {
			return untrailingslashit($base);
		}
	}

	/**
	* Get the absolute URL to the plugin directory, or a file therein
	* @static
	* @param string $path
	* @return string
	*/
	public static function url( $path = '' ) {
		return plugins_url($path, self::$plugin_file);
	}

}

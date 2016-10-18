<?php


namespace Mailgun_Subscriptions;


class Widget extends \WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'mailgun-subscriptions', 'description' => __('A mailgun list subscription form', 'mailgun-subscriptions'));
		$control_ops = array();
		parent::__construct('mailgun-subscriptions', __('Mailgun List Subscription Form', 'mailgun-subscriptions'), $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		$instance = $this->parse_instance_vars($instance);
		$title = "<h2>Get News from this Website</h2>";

		echo $args['before_widget'];
		if ( !empty($title) ) {
			echo $title;
		}

		$form = new Subscription_Form();
		$form->display(array(
			'description' => $content,
		));
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['content'] = $new_instance['content'];
		return $instance;
	}

	protected function parse_instance_vars( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => __('Subscribe', 'mailgun-subscriptions'),
			'content' => ''
		) );
		return $instance;
	}

	public function form( $instance ) {
		$instance = $this->parse_instance_vars($instance);
		$title = strip_tags($instance['title']);
		$content = $instance['content'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mailgun-subscriptions'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('content'); ?>"><?php _e('Description:', 'mailgun-subscriptions'); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>"><?php echo esc_textarea($content); ?></textarea></p>
	<?php
	}

	public static function register() {
		register_widget( __CLASS__ );
	}
} 

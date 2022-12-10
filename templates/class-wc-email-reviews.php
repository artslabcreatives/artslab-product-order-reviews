<?php
/**
 * Class WC_Email_Order_Review file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Order_Review', false ) ) :

	/**
	 * Customer Order Review.
	 *
	 * An email sent to the customer once an order is completed with request to review the products.
	 *
	 * @class       WC_Email_Order_Review
	 * @version     3.5.0
	 * @package     WooCommerce\Classes\Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Order_Review extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_order_review';
			$this->customer_email = true;
			$this->title          = __( 'Order Review Email', 'woocommerce' );
			$this->description    = __( 'Allows you to send email after the order is completed to the customer requesting ordered products to be reviewed.', 'woocommerce' );
			$this->template_html  = 'reviews.php';
	        $this->template_plain = 'plain/reviews.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

	        $this->template_base  = plugin_dir_path( __FILE__ ) . '';
			add_filter( 'woocommerce_locate_template', [$this, 'my_custom_email_template_location'], 10, 3 );

			// Triggers for this email.
			add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}
			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Greetings, Review your {site_title} products!', 'woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Support us by reviews!', 'woocommerce' );
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				)
			);
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( 'We look forward to seeing you at our new purchase.', 'woocommerce' );
		}

		function my_custom_email_template_location( $template, $template_name, $template_path ) {
		    if ( $template_name == 'reviews.php' ) {
		        $template = plugin_dir_path( __FILE__ ) . 'reviews.php';
		    }
		    return $template;
		}
	}

endif;

return new WC_Email_Order_Review();

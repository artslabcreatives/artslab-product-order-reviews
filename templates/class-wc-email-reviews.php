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

			//add_filter( 'woocommerce_email_settings', [$this, 'custom_email_settings'], 10, 2 );
			// Call parent constructor.
			parent::__construct();
		}



		public function init_form_fieldsf() {
			$form = $this->form_fields;
			$extra_form_fields = array(
	            'custom_email_setting_1' => array(
	                'title' => 'Custom Email Setting 1',
	                'type'  => 'text',
	            ),
	            'custom_email_setting_2' => array(
	                'title' => 'Custom Email Setting 2',
	                'type'  => 'text',
	            ),
	        );
	        return $this->form_fields; //array_merge($form, $extra_form_fields);
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
					'top_content'		 => $this->top_status(),
					'top_content'		 => $this->get_top_content(),
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
					'top_content'		 => $this->top_status(),
					'top_content'		 => $this->get_top_content(),
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

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_top_status() {
			return __( 'We have completed your order including delivery.' );
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_top_content() {
			return __( 'Many thanks for choosing ZAHAARA Sanctuary for your home/resort. I hope you love the new addition at your place. This is a kind request to please leave us a review by clicking on the link below. It helps us take our handmade items to more homes as yours. Many thanks!', 'woocommerce' );
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
			$this->form_fields = array(
				'enabled'            => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce' ),
					'default' => 'yes',
				),
				'subject'            => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'top_status' => array(
					'title'       => __( 'Top status content', 'woocommerce' ),
					'description' => __( 'Top status text appear above the main email order table.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_top_status(),
					'desc_tip'    => true,
				),
				'top_content' => array(
					'title'       => __( 'Top content', 'woocommerce' ),
					'description' => __( 'Text to appear above the main email order table.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_top_content(),
					'desc_tip'    => true,
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'woocommerce' ),
					'description' => __( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true,
				),
				'email_type'         => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
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

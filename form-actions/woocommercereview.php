<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor form store review action.
 *
 * Custom Elementor form action which will store review an external server.
 *
 * @since 1.0.0
 */
class Store_Review_After_Submit extends \ElementorPro\Modules\Forms\Classes\Action_Base {

    /**
     * Get action name.
     *
     * Retrieve store review action name.
     *
     * @since 1.0.0
     * @access public
     * @return string
     */
    public function get_name() {
        return 'woocommercereviewaction';
    }

    /**
     * Get action label.
     *
     * Retrieve store review action label.
     *
     * @since 1.0.0
     * @access public
     * @return string
     */
    public function get_label() {
        return esc_html__( 'Store Review', 'elementor-forms-review-action' );
    }

    /**
     * Processes reviews that have been submitted using a form.
     *
     * This function should be called by the Elementor Forms plugin when a form is submitted.
     *
     * @since 1.0.0
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record The Elementor Forms record object representing the submitted form data.
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler The Elementor Forms AJAX handler object.
     */
    public function run( $record, $ajax_handler ) {

        // Get submitted form data.
        $raw_fields = $record->get( 'fields' );

        $rw = $raw_fields['product_reviews']['raw_value'];
        // Normalize form data.
        $fields = [];
        foreach ( $rw as $id => $fie ) {
            foreach ($fie as $key => $value) {
                // code...

                $fields[$key] = [
                    'review' => $rw['review'][$key],
                    'product' => $rw['product_id'][$key],
                    'customer' => $rw['customer_id'][$key],
                ];
            }
        }
        
        // loop through the submitted reviews
        foreach( $fields as $product_id => $review_content ) {
            // create a new comment object

            $comment_data = array(
                'comment_post_ID' => $review_content['product'],
                'comment_content' => $review_content['review'],
                'comment_approved' => 0,
                'user_id' => $review_content['customer'],
            );
            $comment_id = wp_insert_comment( $comment_data );

            // add the comment as a review for the product
            add_comment_meta( $comment_id, 'rating', 5 );
        }
    }

    /**
     * Register action controls.
     *
     * Store review action has no input fields to the form widget.
     *
     * @since 1.0.0
     * @access public
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section( $widget ) {}

    /**
     * On export.
     *
     * Store review action has no fields to clear when exporting.
     *
     * @since 1.0.0
     * @access public
     * @param array $element
     */
    public function on_export( $element ) {}
}
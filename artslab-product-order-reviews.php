<?php

/**
 * Plugin Name: Artslab Product Order Reviews
 * Plugin URI: https://artslabcreatives.com
 * Description: Allows you send emails regarding orders and get customers to review the orders on a single page for each of the products they have ordered
 * Version: 1.0.1
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author: Artslab Creatives
 * Author URI: https://artslabcreatives.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://artslabcreatives.com
 * Text Domain: artslab-product-order-reviews
 * Domain Path: localization
 *
 * Elementor tested up to: 3.7.0
 * Elementor Pro tested up to: 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/// Register a custom menu page
function wc_completed_orders_menu_page() {
    add_menu_page(
        'Completed Orders',
        'Completed Orders',
        'manage_options',
        'completed-orders',
        'wc_completed_orders_page',
        'dashicons-cart',
        30
    );
}

add_action( 'admin_menu', 'wc_completed_orders_menu_page' );

// Display the custom menu page
function wc_completed_orders_page() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get completed orders
    $completed_orders = wc_get_orders( array(
        'status' => 'completed',
    ) );

    // Display completed orders
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Date</th> 
                    <th>Äctions</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $completed_orders as $order ) : ?>
                    <tr>
                        <td><?php echo esc_html( $order->get_id() ); ?></td>
                        <td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
                        <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                        <td><?php echo esc_html( wc_format_datetime( $order->get_date_completed() ) ); ?></td>
                        <td><a href="<?php echo esc_url(add_query_arg('order_id', $order->get_id())); ?>" style="font-weight: bold;">Send Review Request</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <?php
    // Check if the 'order_id' parameter is present in the query string
    if (isset($_GET['order_id'])) {
        $order_id = sanitize_text_field($_GET['order_id']);
        wc_order_list_callback_function($order_id);
    }
}

function product_order_review( $email_classes ) {
    // Include the custom email class
    require_once 'templates/class-wc-email-reviews.php';

    // Add the custom email class to the list of email classes
    $email_classes['WC_Email_Order_Review'] = new WC_Email_Order_Review();

    return $email_classes;
}
add_filter( 'woocommerce_email_classes', 'product_order_review' );


// This is a callback function that is called when a user clicks on an order in a list.
function wc_order_list_callback_function($order_id) {
    // When this function is called, it receives the ID of the order that was clicked on.
    $order = wc_get_order($order_id); // Retrieve the order data using the order ID.

    // The code below sends an email using the "WC_Email_Order_Review" email type.
    $email = WC()->mailer()->get_emails()['WC_Email_Order_Review']; // Get the email object.

    // Send the email to the customer.
    $email->trigger($order_id, $order); // Send the email using the order data.

    // Output a message to the user indicating that the email has been sent.
    echo '<br/><p> An email has been sent to '.$order->get_billing_first_name().' for the order: ' . $order_id . ' </p>';
}


/**
 * Add new form action after form submission.
 *
 * @since 1.0.1
 * @param ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar
 * @return void
 */
function add_new_store_review_action( $form_actions_registrar ) {
    include_once( __DIR__ .  '/form-actions/woocommercereview.php' );
    $form_actions_registrar->register( new \Store_Review_After_Submit() );

}
add_action( 'elementor_pro/forms/actions/register', 'add_new_store_review_action' );

/**
 * Shortcode for displaying a form for submitting reviews for the products in an order.
 *
 * The shortcode should be used like this: [order_reviews]
 *
 * The shortcode accepts the following URL parameters:
 * - order_id: The ID of the order to display reviews for.
 * - customer_id: The ID of the customer who is submitting the reviews.
 */
add_shortcode( 'order_reviews', 'display_order_reviews' );
function display_order_reviews() {
    // Get the order ID and customer ID from the URL parameters.
    $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
    $customer_id = isset( $_GET['customer_id'] ) ? intval( $_GET['customer_id'] ) : 0;

    // If the order ID or customer ID is not set, return nothing.
    if ( ! $order_id || ! $customer_id ) {
        return;
    }

    // Get the order object using the order ID.
    $order = wc_get_order( $order_id );

    // If the order cannot be found, return nothing.
    if ( ! $order ) {
        return;
    }

    // Start a form for submitting reviews.
    $html = '<form method="post" action="">';

    // Loop through the products in the order.
    foreach( $order->get_items() as $item ) {
        // Get the product object.
        $product = $item->get_product();

        // Add a review field for the product.
        $html .= '<p>';
        $html .= '<label>' . $product->get_name() . '</label>';
        $html .= '<input type="hidden" name="customer_id" value="' . $customer_id . '">';
        $html .= '<textarea name="reviews[' . $product->get_id() . ']"></textarea>';
        $html .= '</p>';
    }

    // Add a submit button for the form.
    $html .= '<p><input type="submit" value="Submit Reviews"></p>';

    // End the form.
    $html .= '</form>';

    // Return the HTML.
    return $html;
}

/**
 * Processes reviews that have been submitted using the [order_reviews] shortcode.
 *
 * This function should be hooked to the 'template_redirect' action.
 */
add_action( 'template_redirect', 'process_order_reviews' );
function process_order_reviews() {
    // If the 'reviews' or 'customer_id' parameters are not set, return early.
    if ( ! isset( $_POST['reviews'] ) || ! isset( $_POST['customer_id'] ) ) {
        return;
    }

    // Get the customer ID from the form.
    $customer_id = intval( $_POST['customer_id'] );

    // Loop through the submitted reviews.
    foreach( $_POST['reviews'] as $product_id => $review_content ) {
        // Set up the data for the comment to be inserted.
        $comment_data = array(
            'comment_post_ID' => $product_id, // The ID of the product being reviewed.
            'comment_content' => $review_content, // The review text.
            'comment_approved' => 1, // Approve the comment automatically.
            'user_id' => $customer_id, // The ID of the customer who is submitting the review.
        );

        // Insert the comment.
        $comment_id = wp_insert_comment( $comment_data );

        // Add a 'rating' meta field to the comment with a value of 5.
        add_comment_meta( $comment_id, 'rating', 5 );
    }
}

/**
 * Add new `local-tel` field to Elementor form widget.
 *
 * @since 1.0.1
 * @param \ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $form_fields_registrar
 * @return void
 */
function add_new_form_field( $form_fields_registrar ) {
    require_once( __DIR__ . '/form-fields/review-list.php' );
    $form_fields_registrar->register( new \Elementor_Local_Tel_Field() );
}
add_action( 'elementor_pro/forms/fields/register', 'add_new_form_field' );

require_once( __DIR__ . '/updater.php' );
new ALCPORUpdater();
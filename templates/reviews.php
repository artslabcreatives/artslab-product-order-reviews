<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<p>
    <?php
    /**
     * Show user-defined additional content - this is set in each email's settings.
     */
    if ( $top_status ) {
        echo wp_kses_post( wpautop( wptexturize( $top_status ) ) );
    }
    ?>
</p>
<p>
    <?php
    /**
     * Show user-defined additional content - this is set in each email's settings.
     */
    if ( $top_content ) {
        echo wp_kses_post( wpautop( wptexturize( $top_content ) ) );
    }
    ?>
</p>
<p><a href="<?php echo site_url("?p=4434&order_id=".$order->get_id()."&customer_id=".$order->get_customer_id());?>">Review Products</a></p>
<p><?php esc_html_e( 'Below are the products you ordered.', 'woocommerce' ); ?></p>
<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $order,
                array(
                    'show_sku'      => $sent_to_admin,
                    'show_image'    => false,
                    'image_size'    => array( 32, 32 ),
                    'plain_text'    => $plain_text,
                    'sent_to_admin' => $sent_to_admin,
                )
            );
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="td" scope="col" colspan="3">
                    <?php
                    /**
                     * Show user-defined additional content - this is set in each email's settings.
                     */
                    if ( $additional_content ) {
                        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
                    }
                    ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
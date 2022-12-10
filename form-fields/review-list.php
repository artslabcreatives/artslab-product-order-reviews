<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Form Field - Local Tel
 *
 * Add a new "Local Tel" field to Elementor form widget.
 *
 * @since 1.0.0
 */
class Elementor_Local_Tel_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

    /**
     * Get field type.
     *
     * Retrieve local-tel field unique ID.
     *
     * @since 1.0.0
     * @access public
     * @return string Field type.
     */
    public function get_type() {
        return 'local-tel';
    }

    /**
     * Get field name.
     *
     * Retrieve local-tel field label.
     *
     * @since 1.0.0
     * @access public
     * @return string Field name.
     */
    public function get_name() {
        return esc_html__( 'Local Tel', 'elementor-form-local-tel-field' );
    }

    /**
     * Render field output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     * @param mixed $item
     * @param mixed $item_index
     * @param mixed $form
     * @return void
     */
    public function render( $item, $item_index, $form ) {
        $form_id = $form->get_id();
        $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
        $customer_id = isset( $_GET['customer_id'] ) ? intval( $_GET['customer_id'] ) : 0;

        if ( ! $order_id || ! $customer_id ) {
            return;
        }

        // get the order object
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        // loop through the products in the order
        foreach( $order->get_items() as $key => $productitem ) {
            // get the product object
            $product = $productitem->get_product();

            $image = $product->get_image('thumbnail');

            $form->add_render_attribute(
                'textarea' . $item_index,
                [
                    'name' => $this->get_attribute_name( $item )."[review][]",
                    'id' => $this->get_attribute_id( $item ),
                    'class' =>  [
                        'elementor-field-textual',
                        'select2',
                        'elementor-size-' . $item['input_size'],
                    ],
                    'for' => $form_id . $item_index,
                    'type' => 'textarea',
                    'size' => '1',
                    'class' => 'elementor-field-textual',
                    'title' => esc_html__( $product->get_name(), 'elementor-form-local-tel-field' ),

                ]
            );

            $form->add_render_attribute(
                'input_hidden' . $item_index,
                [
                    'name' => $this->get_attribute_name( $item )."[product_id][]",
                    'id' => $this->get_attribute_id( $item ),
                    'for' => $form_id . $item_index,
                    'type' => 'hidden',
                    'size' => '1',
                    'class' => 'elementor-field-textual',
                    'title' => esc_html__( $product->get_name(), 'elementor-form-local-tel-field' ),

                ]
            );

            $form->add_render_attribute(
                'input_customer_hidden' . $item_index,
                [
                    'name' => $this->get_attribute_name( $item )."[customer_id][]",
                    'id' => $this->get_attribute_id( $item ),
                    'for' => $form_id . $item_index,
                    'type' => 'hidden',
                    'size' => '1',
                    'class' => 'elementor-field-textual',
                    'title' => esc_html__( $product->get_name(), 'elementor-form-local-tel-field' ),

                ]
            );

            echo '<h6 style="padding-bottom:5px; margin-bottom: 0px; width: 100%;">'.$product->get_name().'</h6>';
            echo "<div>".$image."</div>";
            echo '<textarea style="margin-top: 10px;" ' . $form->get_render_attribute_string( 'textarea' . $item_index ) . '></textarea>';
            echo '<input style="margin-top: 10px; display: none;" value='.$product->get_id().' ' . $form->get_render_attribute_string( 'input_hidden' . $item_index ) . ' />';

            echo '<input style="margin-top: 10px; display: none;" value='.$customer_id.' ' . $form->get_render_attribute_string( 'input_customer_hidden' . $item_index ) . ' />';
        }
    }

    public function get_attribute_name( $item ) {
        return "form_fields[{$item['custom_id']}]";
    }

    public function get_attribute_id( $item ) {
        return 'form-field-' . $item['custom_id'];
    }

    /**
     * Field validation.
     *
     * Validate local-tel field value to ensure it complies to certain rules.
     *
     * @since 1.0.0
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Field_Base   $field
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     * @return void
     */
    public function validation( $field, $record, $ajax_handler ) {
        if ( empty( $field['value'] ) ) {
            return;
        }

    }

    /**
     * Field constructor.
     *
     * Used to add a script to the Elementor editor preview.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
    }

    /**
     * Elementor editor preview.
     *
     * Add a script to the footer of the editor preview screen.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function editor_preview_footer() {
        add_action( 'wp_footer', [ $this, 'content_template_script' ] );
    }

    /**
     * Content template script.
     *
     * Add content template alternative, to display the field in Elemntor editor.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function content_template_script() {
        ?>
        <script>
        jQuery( document ).ready( () => {

            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                function ( inputField, item, i ) {
                    const fieldId    = `form_field_${i}`;
                    const fieldClass = `elementor-field-textual elementor-field ${item.css_classes}`;
                    const size       = '1';
                    const pattern    = '[0-9]{3}-[0-9]{3}-[0-9]{4}';
                    const title      = "<?php echo esc_html__( 'Format: 123-456-7890', 'elementor-forms-local-tel-field' ); ?>";

                    return `<input id="${fieldId}" class="${fieldClass}" size="${size}" pattern="${pattern}" title="${title}">`;
                }, 10, 3
            );

        });
        </script>
        <?php
    }

}

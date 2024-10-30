<?php
/**
 * Plugin Name: Cense Conversational AI Chatbot
 * Plugin URI:  https://cense.ai/
 * Description: Chatbot powered by Cense AI
 * Version:     10.6
 * Author:      Cense AI
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

class Cense_AI_Chat_Plugin {

public function __construct() {
    // Hook into the admin menu
    add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
    add_action( 'admin_init', array( $this, 'setup_sections' ) );
    add_action( 'admin_init', array( $this, 'setup_fields' ) );
    // add_action('init', array( $this, 'load_widget')); 
    !is_admin() and add_action('wp_head', array($this, 'load_widget'));
    add_action('wp_footer', array( $this, 'generate'));
}

public function create_plugin_settings_page() {
    // Add the menu item and page
    $page_title = 'Add/Edit Chatbot License Key';
    $menu_title = 'Cense AI Chatbot';
    $capability = 'manage_options';
    $slug = 'cense_ai_chatbot';
    $callback = array( $this, 'plugin_settings_page_content' );
    $icon = 'dashicons-admin-plugins';
    $position = 100;

    add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
}

public function plugin_settings_page_content() 
{ ?>
    <div class="wrap">
        <div>
            <h1>Cense AI</h1>
        </div>
        <h4>To get your license key, register yourself o <a href="https://portal.cense.ai/bot/register" target="blank">Cense AI </a>
        </h4>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'cense_ai_chatbot' );
            do_settings_sections( 'cense_ai_chatbot' );
            submit_button();
            ?>
        </form>
    </div> 
<?php
}

public function setup_sections() {
    add_settings_section( 'our_first_section', '', array( $this, 'section_callback' ), 'cense_ai_chatbot' );
}

public function section_callback( $arguments ) { 
    switch( $arguments['id'] ){
        case 'our_first_section':
            break;
    }
}

public function setup_fields() {
    $fields = array(
        array(
            'uid' => 'cense_ai_chatbot_key',
            'label' => 'License Key',
            'section' => 'our_first_section',
            'type' => 'text',
            'options' => false,
            'placeholder' => 'Enter license key here ...',
            'helper' => 'Does this help?',
            'supplemental' => 'I am underneath!',
            'default' => ''
        )
    );
    foreach( $fields as $field ){
        add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'cense_ai_chatbot', $field['section'], $field );
        register_setting( 'cense_ai_chatbot', $field['uid'] );
    }
}


public function field_callback( $arguments ) {
    echo '<input name="cense_ai_chatbot_key" id="cense_ai_chatbot_key" type="text" value="' . get_option( 'cense_ai_chatbot_key' ) . '" />';
    register_setting( 'cense_ai_chatbot', 'cense_ai_chatbot_key' );
}

public function load_widget() {
    wp_register_script( 'app', 'https://resource.cense.ai/js/app.js', true );
    wp_enqueue_script('app');
    // wp_register_style( 'chat_widget_style', plugin_dir_url( __FILE__ ) .'css/app.css' );
    wp_register_style( 'chat_widget_style', 'https://resource.cense.ai/css/app.css', true );
    wp_enqueue_style('chat_widget_style');
    $current_user = wp_get_current_user();
    $woo_current_user_cart = WC()->cart;
    $widget_data = array(
        'source'=> 'Web',
        'license_key'=> get_option( 'cense_ai_chatbot_key' ),
        'woo_current_user' => $current_user,
        'woo_current_user_cart' => $woo_current_user_cart
    );
    ?>
        <bot-chat></bot-chat>
    <?php
    wp_localize_script('app', 'widget_data', $widget_data );
    wp_register_script( 'generate_widget', plugins_url('generate_widget.js',__FILE__ ));
    wp_enqueue_script('generate_widget');
    // wp_localize_script('app', 'widget_data', $widget_data );
    // return printf(
    //     '<bot-chat></bot-chat>',
    //     var_export( $GLOBALS['wp_query'], true )
    // );
}

public function generate() {
    // echo get_option( 'cense_ai_chatbot_key' );
    return printf(
        '<script type="text/javascript">
        var widget = new GenerateWidget(widget_data)
        </script>',
        var_export( $GLOBALS['wp_query'], true )
    );
}

}
// class add_more_to_cart {

//     private $prevent_redirect = false; //used to prevent WC from redirecting if we have more to process

//     function __construct() {
//         if ( ! isset( $_REQUEST[ 'add-to-cart' ] ) ) return; //don't load if we don't have to
//         $this->prevent_redirect = 'no'; //prevent WC from redirecting so we can process additional items
//         add_action( 'wp_loaded', [ $this, 'add_more_to_cart' ], 21 ); //fire after WC does, so we just process extra ones
//         add_action( 'pre_option_woocommerce_cart_redirect_after_add', [ $this, 'intercept_option' ], 9000 ); //intercept the WC option to force no redirect
//     }

//     function intercept_option() {
//         return $this->prevent_redirect;
//     }

//     function add_more_to_cart() {
//         $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
//         $count       = count( $product_ids );
//         $number      = 0;

//         foreach ( $product_ids as $product_id ) {
//             if ( ++$number === $count ) $this->prevent_redirect = false; //this is the last one, so let WC redirect if it wants to.
//             $_REQUEST['add-to-cart'] = $product_id; //set the next product id
//             WC_Form_Handler::add_to_cart_action(); //let WC run its own code
//         }
//     }
// }

// new add_more_to_cart;


class add_more_to_cart {

//   private $prevent_redirect = false; //used to prevent WC from redirecting if we have more to process

    function __construct() {
        if ( ! isset( $_REQUEST[ 'add-to-cart' ] ) ) return; //don't load if we don't have to
        $this->prevent_redirect = 'no'; //prevent WC from redirecting so we can process additional items
        add_action( 'wp_loaded', [ $this, 'woocommerce_maybe_add_multiple_products_to_cart' ], 15 ); //fire after WC does, so we just process extra ones
        add_action( 'pre_option_woocommerce_cart_redirect_after_add', [ $this, 'intercept_option' ], 9000 ); //intercept the WC option to force no redirect
    }

    function intercept_option() {
        return $this->prevent_redirect;
    }

//   function add_more_to_cart() {
//       $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
//       $count       = count( $product_ids );
//       $number      = 0;

//       foreach ( $product_ids as $product_id ) {
//           if ( ++$number === $count ) $this->prevent_redirect = false; //this is the last one, so let WC redirect if it wants to.
//           $_REQUEST['add-to-cart'] = $product_id; //set the next product id
//           WC_Form_Handler::add_to_cart_action(); //let WC run its own code
//       }
//   }
    
    function woocommerce_maybe_add_multiple_products_to_cart() {
        // Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
        if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
            return;
        }
    
        remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
    
        $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
        $count       = count( $product_ids );
        $number      = 0;
    
        foreach ( $product_ids as $product_id ) {
            if ( ++$number === $count ) {
                // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
                $_REQUEST['add-to-cart'] = $product_id;
    
                return WC_Form_Handler::add_to_cart_action();
            }
    
            $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
            $was_added_to_cart = false;
    
            $adding_to_cart    = wc_get_product( $product_id );
    
            if ( ! $adding_to_cart ) {
                continue;
            }
    
            if ( $adding_to_cart->is_type( 'simple' ) ) {
    
                // quantity applies to all products atm
                $quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
                $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
    
                if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
                    wc_add_to_cart_message( array( $product_id => $quantity ), true );
                }
    
            } else {
    
                $variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );
                $quantity           = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // WPCS: sanitization ok.
                $missing_attributes = array();
                $variations         = array();
                $adding_to_cart     = wc_get_product( $product_id );
    
                if ( ! $adding_to_cart ) {
                continue;
                }
    
                // If the $product_id was in fact a variation ID, update the variables.
                if ( $adding_to_cart->is_type( 'variation' ) ) {
                $variation_id   = $product_id;
                $product_id     = $adding_to_cart->get_parent_id();
                $adding_to_cart = wc_get_product( $product_id );
    
                if ( ! $adding_to_cart ) {
                    continue;
                }
                }
    
                // Gather posted attributes.
                $posted_attributes = array();
    
                foreach ( $adding_to_cart->get_attributes() as $attribute ) {
                if ( ! $attribute['is_variation'] ) {
                    continue;
                }
                $attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
    
                if ( isset( $_REQUEST[ $attribute_key ] ) ) {
                    if ( $attribute['is_taxonomy'] ) {
                    // Don't use wc_clean as it destroys sanitized characters.
                    $value = sanitize_title( wp_unslash( $_REQUEST[ $attribute_key ] ) );
                    } else {
                    $value = html_entity_decode( wc_clean( wp_unslash( $_REQUEST[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // WPCS: sanitization ok.
                    }
    
                    $posted_attributes[ $attribute_key ] = $value;
                }
                }
    
                // If no variation ID is set, attempt to get a variation ID from posted attributes.
                if ( empty( $variation_id ) ) {
                $data_store   = WC_Data_Store::load( 'product' );
                $variation_id = $data_store->find_matching_product_variation( $adding_to_cart, $posted_attributes );
                }
    
                // Do we have a variation ID?
                if ( empty( $variation_id ) ) {
                throw new Exception( __( 'Please choose product options&hellip;', 'woocommerce' ) );
                }
    
                // Check the data we have is valid.
                $variation_data = wc_get_product_variation_attributes( $variation_id );
    
                foreach ( $adding_to_cart->get_attributes() as $attribute ) {
                if ( ! $attribute['is_variation'] ) {
                    continue;
                }
    
                // Get valid value from variation data.
                $attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
                $valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ]: '';
    
                /**
                 * If the attribute value was posted, check if it's valid.
                 *
                 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
                 */
                if ( isset( $posted_attributes[ $attribute_key ] ) ) {
                    $value = $posted_attributes[ $attribute_key ];
    
                    // Allow if valid or show error.
                    if ( $valid_value === $value ) {
                    $variations[ $attribute_key ] = $value;
                    } elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs() ) ) {
                    // If valid values are empty, this is an 'any' variation so get all possible values.
                    $variations[ $attribute_key ] = $value;
                    } else {
                    throw new Exception( sprintf( __( 'Invalid value posted for %s', 'woocommerce' ), wc_attribute_label( $attribute['name'] ) ) );
                    }
                } elseif ( '' === $valid_value ) {
                    $missing_attributes[] = wc_attribute_label( $attribute['name'] );
                }
                }
                if ( ! empty( $missing_attributes ) ) {
                throw new Exception( sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ) );
                }
    
            $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );
    
            if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
                wc_add_to_cart_message( array( $product_id => $quantity ), true );
            }
            }
        }
    }



}
new add_more_to_cart;

new Cense_AI_Chat_Plugin();

<?php
/**
 * Plugin Name: Email Customizer Woocommerce
 * Plugin URI: www.smackcoders.com
 * Description: Demo plugin for adding a custom WooCommerce email that sends admins an email when an order is received with expedited shipping
 * Author: Smackcoders
 * Author URI: www.smackcoders.com
 * Company: Smackcoders Technologies PVT Ltd
 * Version: 1.0.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

#smack-woocommerce-custom-mail

/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 0.1
 * @param array $email_classes available email classes
 * @return array filtered available email classes
 */
function add_product_delivered_woocommerce_email( $email_classes ) {
 
    // include our custom email class
	if (!class_exists('WC_Product_Delivered_Email'))
    require( 'includes/class-wc-product-delivered-email.php' );
 
    // add the email class to the list of email classes that WooCommerce loads
    $email_classes['WC_Product_Delivered_Email'] = new WC_Product_Delivered_Email();
 
    return $email_classes;
 
}
add_filter( 'woocommerce_email_classes', 'add_product_delivered_woocommerce_email' );

/*
add_filter( 'page_template', 'product_delivered_page_template' );
function product_delivered_page_template( $page_template )
{                                                               
    if ( is_page( 'my-custom-page-slug' ) ) {
        $page_template = dirname( __FILE__ ) . '/product-delivered-order.php';
    }
    return $page_template;

}                 



$preview = get_stylesheet_directory() . '/woocommerce/emails/woo-preview-emails.php';

if(file_exists($preview)) {
    require $preview;
}

*/


/**
 * Register new status
 * Tutorial: http://www.sellwithwp.com/woocommerce-custom-order-status-2/
**/

function register_product_delivered_order_status() {
    register_post_status( 'wc-product-delivered', array(
        'label'                     => 'Product Delivered',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Product delivered <span class="count">(%s)</span>', 'Product delivered <span class="count">(%s)</span>' )
    ) );
}
add_action( 'admin_init', 'register_product_delivered_order_status' );  

/*
function initiate_email_for_product_delivered_status(){
   // Just when you update the order_status on backoffice
   if( isset($_POST['wc_product_delivered']) ) {
        WC()->mailer();
    }
}    */


/* Add Order action to Order action meta box */
    
function add_Product_delivered_status_to_meta_box_actions($actions)
{
   $actions['product_delivered'] = 'Product Delivered';
   return $actions; 
}

add_action( 'woocommerce_order_actions', 'add_Product_delivered_status_to_meta_box_actions' );


// if order action is product delivered do the following
add_action('woocommerce_order_action_product_delivered', 'wdm_order_status_product_delivered_callback');
function wdm_order_status_product_delivered_callback( $order_id )
{
        //Here order id is sent as parameter
        //Add code for processing here
        global $woocommerce;
        $order = new WC_Order( $order_id );                  
        if($order->post_status === 'wc-product-delivered' ) {   
                // Create a mailer
                $mailer = $woocommerce->mailer();
                $orderID = $order->get_order_number();
                $get_postmeta = get_post_meta($orderID);
                $customer_name = $get_postmeta['_billing_first_name'][0] . ' ' . $get_postmeta['_billing_last_name'][0];
                $get_delivery_message = get_option('smack_encoder_configuration');
                $delivery_message = $get_delivery_message['delivery_message'];
                $message_body = "<tr>
                        <td valign='top' style='padding:40px;'>
                        <h1 style='font-size:22px; font-weight:normal; line-height:22px;'>Dear ";
                        $message_body .= $customer_name . "," . "\r\n";
$message_body .= '<br><br><br>';

                $message_body .= "<p style='font-size:12px; line-height:16px; margin:0 0 16px 0;'> Your order # ";
                $message_body .= $order->get_order_number();
                $siteURLtoLogin= site_url().'/my-account/';
                if($_SERVER['HTTP_HOST']=='www.smackcoders.com')
                $siteURLtoLogin = site_url().'/my-account.html';
                $message_body .= " has been <b>Delivered.</b><br/> \n You can check the status of your order by <a href='".$siteURLtoLogin."'> login your account.</a> <br/>";
                $message_body .= "<div style='font-size:12px; line-height:16px; margin:0 0 16px 0;'> $delivery_message </div>";
                $message_body .= "</p>
                        </td>
                        </tr>";
                $message = $mailer->wrap_message(
                                // Message head and message body.
                                sprintf( __( 'Order %s delivered' ), $order->get_order_number() ), $message_body );
                // Cliente email, email subject and message.

                $mailer->send( $order->billing_email, sprintf( __( 'Order %s delivered' ), $order->get_order_number() ), $message );
        }
}



// Add to list of WC Order statuses
function add_product_delivered_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-product-delivered'] = 'Product Delivered ';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_product_delivered_to_order_statuses' );


	
function myplugin_plugin_path() {
 
  // gets the absolute path to this plugin directory
 
  return untrailingslashit( plugin_dir_path( __FILE__ ) );
 
}
 
 
 
add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3 );
 
 
 
function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {   //  echo($template_name); die("aa"); 
 
  global $woocommerce;
 
 
 
  $_template = wc_clean($template);
 
  if ( ! $template_path ) $template_path = $woocommerce->template_url;
 
  $plugin_path  = myplugin_plugin_path() . '/templates/woocommerce/';
 
 
 
  // Look within passed path within the theme - this is priority
 
  $template = locate_template(
 
    array(
 
      $template_path . $template_name,
 
      $template_name
 
    )
 
  );
 
									//echo "<pre>"; print_r($template); die("aro"); 
 
  // Modification: Get the template from this plugin, if it exists
 
  if ( ! $template && file_exists( $plugin_path . $template_name ) )
 
    $template = $plugin_path . $template_name;                 	      //	echo($template); die("dilipan");
 
 
 
  // Use default template
 
  if ( ! $template )
 
    $template = $_template;                                   //  echo($template); die("dinesh");
 
 
 
  // Return what we found
 
  return $template;
 
}

?>

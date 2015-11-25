<?php
/**
 * Product delivered email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>
<?php
$user = get_user_by( $id,1 );
$get_usermeta = get_user_meta($user->ID);
$customer_name = $get_usermeta['first_name'][0] . ' ' . $get_usermeta['last_name'][0];
if(trim($customer_name) == '')
        $customer_name = $user->user_login;

$user_email = $user->user_email;
?>
                <!-- [ middle starts here] -->
                    <tr>
                        <td valign="top" style='padding:40px;'>
                            <h1 style="font-size:22px; font-weight:normal; line-height:22px;">Dear <?php printf( __( "%s", 'woocommerce' ), esc_html( $customer_name ) ); ?>,</h1>
                            <p style="font-size:12px; line-height:16px; margin:0 0 16px 0;"> Your  <?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?> has been <b>Delivered.</b> </p> <br/> <br/>
                            <p style="border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#f9f9f9;">
                                You can check the status of your order by <a href="<?php echo site_url();?>/my-account<?php if($_SERVER['HTTP_HOST']=='www.smackcoders.com'){echo '.html';}?>">login your account.</a> <br/>
				<?php 
				$get_delivery_message = get_option('smack_encoder_configuration');
				$delivery_message = $get_delivery_message['delivery_message']; 
				print_r($delivery_message); 
				?>
			    </p>
                        </td>
                    </tr>
<?php do_action( 'woocommerce_email_footer' ); ?>

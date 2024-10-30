<?php
/**
 * Delivery Drivers Woocommerce helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

namespace ChatOnDesk; 
if (! defined('ABSPATH') ) {
    exit;
}
if (! is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php') || ! is_plugin_active('woocommerce/woocommerce.php') ) {
    return;
}
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Smsalert_Delivery_Drivers_Woocommerce class
 */
class Smsalert_Delivery_Drivers_Woocommerce
{
    /**
     * Construct function.
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('codDefaultSettings', __CLASS__ . '::add_default_setting', 1);
        add_filter('cod_wc_variables', __CLASS__ . '::addTemplateVariable', 1, 2);

        $chatondesk_driver_notify = chatondesk_get_option('driver_notify', 'chatondesk_driver_general', 'on');

        if ('on' === $chatondesk_driver_notify ) {
            add_action('woocommerce_order_status_changed', array( $this, 'triggerOnchangeOrderStatus' ), 10, 3);
        }
        add_action('cod_addTabs', array( $this, 'addTabs' ), 100);
        add_action('ddwc_driver_dashboard_change_status_forms_bottom', array( $this, 'addCodeVerify' ), 100);
        add_filter('cod_wc_order_sms_customer_before_send', __CLASS__ . '::modifySMSTextByOrderId', 1, 2);

        $this->verifyDeliveryCode();
    }

    /**
     * Add code verify.
     *
     * @return void
     */
    public function addCodeVerify()
    {

        $order_id     = filter_input(INPUT_GET, 'orderid');
        $order        = wc_get_order($order_id);
        $order_data   = $order->get_data();
        $order_status = $order_data['status'];

        $phone_number       = get_post_meta($order_id, $key = '_billing_phone', $single = true);
        $code_verify_enable = metadata_exists('post', $order_id, '_cod_deliverycode');

        $verify_code_status = get_post_meta($order_id, '_cod_deliverycode_status', true);

        $code_message = str_replace('##phone##', $phone_number, SmsAlertMessages::showMessage('OTP_SENT_PHONE'));

        $invalid_message = SmsAlertMessages::showMessage('INVALID_OTP');
        if ('out-for-delivery' === $order_status && '0' === $verify_code_status && ! empty($code_verify_enable) ) {
            echo '<script>
				jQuery(document).ready(function(){
					var button = jQuery("input[name=ordercompleted]");					jQuery("input[name=ordercompleted]").attr("type","hidden");			jQuery("input[name=ordercompleted]").after(button.clone()).addClass("cod-otp-btn-init").html();
					jQuery(".cod-otp-btn-init").attr("type","submit");				jQuery(".cod-otp-btn-init").attr("id","submit_code").attr("name","submit_code");
					jQuery("#submit_code").click(function(e){
						e.preventDefault();
						jQuery(".modal").show();
					});
					var message = "' . esc_attr($code_message) . '";
					jQuery(".cod-message").html(message);
					jQuery(".cod_resend_btn, .cod_timer").hide();					jQuery("#cod_verify_otp").click(function(){
						var code = jQuery("#chatondesk_customer_validation_otp_token").val();
						var order_id = "' . ( ! empty($_GET['orderid']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['orderid']))) : '' ) . '";
						var invalid_message = "' . esc_attr($invalid_message) . '";
						if(code != " "){
							jQuery.ajax({
								url         : "' . esc_attr(site_url()) . '/?option=cod_verify_delivery_code",
								data        : {verify_code:code,order_id:order_id},
								dataType	: "json",
								type: "post",
								success: function(data)
								{
									if(data.result == "success"){
										jQuery("input[name= ordercompleted]").parent("form").submit();
									}else{
										jQuery(".cod-message").removeClass("woocommerce-message");								jQuery(".cod-message").addClass("woocommerce-error");
										jQuery(".cod-message").html(invalid_message);
									}
								}
							});
						}
						return false;
					});
				});
			</script>';
            add_action('wp_footer', array( 'SAVerify', 'add_shortcode_popup_html' )); 
        }
    }

    /**
     * Verify delivery code.
     *
     * @return void
     */
    public function verifyDeliveryCode()
    {
        if (! empty($_REQUEST['option']) && 'cod_verify_delivery_code' === sanitize_text_field(wp_unslash($_REQUEST['option'])) ) {

            $order_id      = ( ! empty($_REQUEST['order_id']) ) ? sanitize_text_field(wp_unslash($_REQUEST['order_id'])) : '';
            $delivery_code = get_post_meta($order_id, $key = '_cod_deliverycode', $single = true);

            $verify_code = ( ! empty($_REQUEST['verify_code']) ) ? sanitize_text_field(wp_unslash($_REQUEST['verify_code'])) : '';

            if ($verify_code === $delivery_code ) {
                update_post_meta($order_id, '_cod_deliverycode_status', 1);
                wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response('Code Validated Successfully.', 'success'));
            } else {
                wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response('Invalid Code', 'error'));
            }
        }
    }

    /**
     * Add tabs to smsalert settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs( $tabs = array() )
    {
        $delivery_drivers_param = array(
        'checkTemplateFor' => 'delivery_drivers',
        'templates'        => self::getDeliverDriversTemplates(),
        );

        $tabs['woocommerce']['inner_nav']['delivery']['title']       = 'Delivery Drivers';
        $tabs['woocommerce']['inner_nav']['delivery']['tab_section'] = 'deliverydriverstemplates';
        $tabs['woocommerce']['inner_nav']['delivery']['tabContent']  = $delivery_drivers_param;
        $tabs['woocommerce']['inner_nav']['delivery']['filePath']    = 'views/message-template.php';
        return $tabs;
    }

    /**
     * Get deliver drivers templates.
     *
     * @return array
     */
    public static function getDeliverDriversTemplates()
    {
        $current_val      = chatondesk_get_option('driver_notify', 'chatondesk_driver_general', 'on');
        $checkbox_name_id = 'chatondesk_driver_general[driver_notify]';
        $textarea_name_id = 'chatondesk_driver_message[driver_notify]';

        $text_body = chatondesk_get_option(
            'driver_notify',
            'chatondesk_driver_message',
            SmsAlertMessages::showMessage('DEFAULT_DELIVERY_DRIVER_MESSAGE')
        );

        $templates = array();

        $variables = array(
        '[first_name]'    => 'First Name',
        '[last_name]'     => 'Last Name',
        '[order_id]'      => 'Order Id',
        '[item_name_qty]' => 'Product Name with Quantity',
        '[item_name]'     => 'Product Name',
        '[store_name]'    => 'Store Name',
        );

        $templates['delivery-drivers']['title']          = 'When Order is assigned to driver';
        $templates['delivery-drivers']['enabled']        = $current_val;
        $templates['delivery-drivers']['status']         = 'driver-assigned';
        $templates['delivery-drivers']['text-body']      = $text_body;
        $templates['delivery-drivers']['checkboxNameId'] = $checkbox_name_id;
        $templates['delivery-drivers']['textareaNameId'] = $textarea_name_id;
        $templates['delivery-drivers']['token']          = $variables;

        return $templates;
    }

    /**
     * Add tabs to smsalert settings at backend.
     *
     * @param array $params   params.
     * @param int   $order_id order_id.
     *
     * @return array
     */
    public static function modifySMSTextByOrderId( $params, $order_id )
    {
        if (empty($params['sms_body']) ) {
            return $params;
        }

        $order           = new \WC_Order($order_id);
        $order_items     = $order->get_items();
        $first_item      = current($order_items);
        $post_id         = ('object' === gettype($first_item) )?$first_item['order_id']:'';
        $driver_id       = get_post_meta($post_id, 'ddwc_driver_id', true);
        $order_variables = get_user_meta($driver_id);
        $delivery_code   = get_post_meta($post_id, $key = '_cod_deliverycode', $single = true);

        $first_name = is_array(
            $order_variables
        )?current($order_variables['first_name']):'';
        $last_name  = is_array(
            $order_variables
        )?current($order_variables['last_name']):'';

        $find = array(
        '[delivery_first_name]',
        '[delivery_last_name]',
        '[delivery_boy_number]',
        '[delivery_code]',
        );

        $replace = array(
        $first_name,
        $last_name,
        '[billing_phone]',
        $delivery_code,
        );

        $content = str_replace($find, $replace, $params['sms_body']);
        
        /*
        if(!empty($order_variables))
        {
        foreach ($order_variables as &$value) {
        $value = $value[0];
        }
        unset($value);

        $order_variables = array_combine(
        array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
        $order_variables
        );
        $content = str_replace( array_keys($order_variables), array_values($order_variables), $content );

        $params['sms_body'] = $content;
        } */
        
        $params['sms_body'] = $content;
        return $params;
    }

    /**
     * Add template variable.
     *
     * @param array  $variables variables.
     * @param string $status    status.
     *
     * @return array
     */
    public static function addTemplateVariable( $variables, $status )
    {
        if ('driver-assigned' === $status ) {
            $variables = array_merge(
                $variables,
                array(
                '[delivery_first_name]' => 'Delivery Boy First Name',
                '[delivery_last_name]'  => 'Delivery Boy Last Name',
                '[delivery_boy_number]' => 'Delivery Boy Number',
                '[delivery_code]'       => 'Delivery Code',
                )
            );
        }
        return $variables;
    }

    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public static function add_default_setting( $defaults = array() )
    {
        $defaults['chatondesk_driver_general']['driver_notify'] = 'off';
        $defaults['chatondesk_driver_message']['driver_notify'] = '';
        return $defaults;
    }

    /**
     * Trigger onchange order status.
     *
     * @param int    $order_id   order_id.
     * @param string $old_status old_status.
     * @param string $new_status new_status.
     *
     * @return void
     */
    public function triggerOnchangeOrderStatus( $order_id, $old_status, $new_status )
    {
        if ('driver-assigned' === $new_status ) {
            $delivery_code = wp_rand(1, 9999);

            $order          = new \WC_Order($order_id);
            $driver_message = chatondesk_get_option('driver_notify', 'chatondesk_driver_message', '');
            $cust_message   = chatondesk_get_option('sms_body_driver-assigned', 'chatondesk_message', '');

            $order_items = $order->get_items();
            $first_item  = current($order_items);
            $post_id     = $first_item['order_id'];
            $driver_id   = get_post_meta($post_id, 'ddwc_driver_id', true);
            $driver_no   = get_the_author_meta('billing_phone', $driver_id);

            do_action('cod_send_sms', $driver_no, $this->parseSmsBody($order, $driver_message, $driver_id));

            if (strpos($cust_message, '[delivery_code]') !== false ) {
                $this->saveDeliveryCode($order_id, $delivery_code);
            }
        }
    }

    /**
     * Save delivery code.
     *
     * @param int    $order_id      order_id.
     * @param string $delivery_code delivery_code.
     *
     * @return void
     */
    public function saveDeliveryCode( $order_id, $delivery_code )
    {
        update_post_meta($order_id, '_cod_deliverycode', $delivery_code);
        update_post_meta($order_id, '_cod_deliverycode_status', 0);
    }

    /**
     * Parse sms body.
     *
     * @param object $order     order.
     * @param string $message   message.
     * @param int    $driver_id driver_id.
     *
     * @return string
     */
    public function parseSmsBody( $order, $message, $driver_id )
    {

        $order_items = $order->get_items();
        $item        = current($order_items);
        $item_name   = $item['name'];
        $order_id    = $item['order_id'];
        $quantity    = $item['quantity'];
        $first_name  = get_the_author_meta('first_name', $driver_id);
        $last_name   = get_the_author_meta('last_name', $driver_id);

        $find = array(
        '[first_name]',
        '[last_name]',
        '[item_name]',
        '[order_id]',
        '[item_name_qty]',
        );

        $replace = array(
        $first_name,
        $last_name,
        $item_name,
        $order_id,
        $item_name . ' ' . $quantity,
        );

        $message = str_replace($find, $replace, $message);
        return $message;
    }
}
new Smsalert_Delivery_Drivers_Woocommerce();

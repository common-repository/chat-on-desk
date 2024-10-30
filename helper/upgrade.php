<?php
/**
 * Upgrade helper.
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
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * SAUpgrade class
 */
class SAUpgrade
{

    /**
     * Construct function
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_init', array( $this, 'smsalertUpgrade' ), 10);
    }

    /**
     * Upgrade function.
     *
     * @return void
     */
    public static function smsalertUpgrade()
    {
        $db_version     = chatondesk_get_option('version', 'chatondesk_upgrade_settings');
        $plugin_version = \ChatOnDesk\SmsAlertConstants::SA_VERSION;

        if ($db_version === $plugin_version ) {
            return;
        }

        if ($db_version <= '3.4.0' ) {
            chatondesk_WC_Order_SMS::saCartActivate();
            if (! get_option('chatondesk_activation_date') ) {
                add_option('chatondesk_activation_date', date('Y-m-d'));
            }
        }
        if ($db_version <= '3.3.7.2' ) {
            $otp_template = chatondesk_get_option('sms_otp_send', 'chatondesk_message');
            if ('Your verification code is [otp]' === $otp_template ) {
                $output                 = get_option('chatondesk_message');
                $output['sms_otp_send'] = 'Your verification code for [shop_url] is [otp]';
                update_option('chatondesk_message', $output);
            }
        }
        
        //for update EDD settings
        if ($db_version <= '3.5.1' ) {
        
            // First let's grab the current settings
            $options = get_option('edd_settings');
            
            if (is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') && !empty($options) && function_exists('edd_get_payment_statuses')) {
                $edd_order_statuses = edd_get_payment_statuses();
                
                foreach ( $edd_order_statuses as $ks  => $vs ) {
                
                    //get sms enable or disable of customer and admin
                    $check_customer = chatondesk_get_option('edd_order_status_' . $vs, 'chatondesk_edd_general', '');
                    
                    $check_admin    = chatondesk_get_option('edd_admin_notification_' . $vs, 'chatondesk_edd_general', '');
                    
                    //get sms body of customer and admin
                    $customer_msg   = chatondesk_get_option('edd_sms_body_'.$vs, 'chatondesk_edd_message');
                    $admin_msg      = chatondesk_get_option('edd_admin_sms_body_'.$vs, 'chatondesk_edd_message');
                    
                    
                    // update sms enable or disable
                    $options[ 'edd_order_status_' . $vs ]         = $check_customer;
                    $options[ 'edd_admin_notification_' . $vs ] = $check_admin;
                    
                    // update sms body
                    $options[ 'edd_sms_body_' . $vs ]             = $customer_msg;
                    $options[ 'edd_admin_sms_body_' . $vs ]     = $admin_msg;
                    
                    update_option('edd_settings', $options);
                }
            }
        }
		if (\ChatOnDesk\SmsAlertUtility::isPlayground() == false  ) {
        if ($db_version <= '3.5.3' ) {
            global $wpdb;
            $reminder_table_name = $wpdb->prefix . "chatondesk_booking_reminder";
            $wpdb->query("ALTER TABLE $reminder_table_name ADD source varchar(50) NOT NULL DEFAULT 'woocommerce-bookings',ADD msg_sent TINYINT NOT NULL DEFAULT 0");
        }
		}

        update_option('chatondesk_upgrade_settings', array( 'version' => $plugin_version ));
    }
}
new SAUpgrade();
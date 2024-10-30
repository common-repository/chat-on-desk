<?php
/**
 * This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification after placing order via whatsapp using Chat On Desk.
 * PHP version 5
 *
 * @category Helper
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Plugin Name: Chat On Desk
 * Plugin URI: https://wordpress.org/plugins/chat-on-desk/
 * Description: This is a WooCommerce add-on. By Using this plugin admin and buyer can get whatsapp notification after placing order using Chat On Desk.
 * Version: 1.0.1
 * Author: Cozy Vision Technologies Pvt. Ltd.
 * Author URI: https://www.chatondesk.com
 * WC requires at least: 2.0.0
 * WC tested up to: 8.8.3
 * Text Domain: chat-on-desk
 * License: GPLv2
 */
/**
/**
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
namespace ChatOnDesk;
// don't call the file directly.
if (! defined('ABSPATH') ) {
    exit;
}
if (! defined('CHATONDESK_TEXT_DOMAIN') ) {
    define('CHATONDESK_TEXT_DOMAIN', 'chat-on-desk');
}
if (! defined('CHATONDESK_PLUGIN_NAME') ) {
    define('CHATONDESK_PLUGIN_NAME', 'Chat On Desk Order Notifications – WooCommerce');
}
if (! defined('CHATONDESK_ABANDONED') ) {
    define('CHATONDESK_ABANDONED', 'chatondesk_abandoned');
}
if (! defined('CHATONDESK_PLUGIN_NAME_SLUG') ) {
    define('CHATONDESK_PLUGIN_NAME_SLUG', 'chat-on-desk');
}
if (! defined('COD_CART_TABLE_NAME') ) {
    define('COD_CART_TABLE_NAME', 'cod_captured_wc_fields');
}
if (! defined('CART_CRON_INTERVAL') ) {
    define('CART_CRON_INTERVAL', 10);// run ab cart cron every 10 min.
}
if (! defined('BOOKING_REMINDER_CRON_INTERVAL') ) {
    define('BOOKING_REMINDER_CRON_INTERVAL', 10);// run booking reminder cron every 10 min.
}
// In minutes. Defines the interval at which msg function is fired.
if (! defined('CART_STILL_SHOPPING') ) {
    define('CART_STILL_SHOPPING', 10); // In minutes. Defines the time period after which an msg notice will be sent and the cart is presumed abandoned.
}
if (! defined('CART_NEW_STATUS_NOTICE') ) {
    define('CART_NEW_STATUS_NOTICE', 240); // Defining time in minutes how long New status is shown in the table.
}

if (! defined('CART_ENCRYPTION_KEY') ) {
    define('CART_ENCRYPTION_KEY', 'SgVkYp3s6v9y$B&M)H+MbQeThWmZq4t9');
}

add_action(
    'before_woocommerce_init', function () {
        if (wp_doing_ajax() ) {
            return;
        }
        if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil') && method_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil', 'declare_compatibility') ) {
        
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', plugin_basename(__FILE__), true);
        }
    }
);

/**
 * Sanitizes Array of vaues.
 *
 * @param array $arr Values to be sanitized.
 *
 * @return array
 */
function chatondesk_sanitize_array( $arr )
{
    global $wp_version;
    $older_version = ( $wp_version < '4.7' ) ? true : false;
    if (! is_array($arr) ) {
        return ( ( $older_version ) ? stripcslashes(sanitize_text_field($arr)) : stripcslashes(sanitize_textarea_field($arr)) );
    }

    $result = array();
    foreach ( $arr as $key => $val ) {
        $result[ $key ] = is_array($val) ? chatondesk_sanitize_array($val) : ( ( $older_version ) ? stripcslashes(sanitize_text_field($val)) : stripcslashes(sanitize_textarea_field($val)) );
    }

    return $result;
}

/**
 * Creates a cookie.
 *
 * @param string $cookie_key   Cookie Key name.
 * @param string $cookie_value Cookie Value.
 *
 * @return array
 */
function create_chatondesk_cookie( $cookie_key, $cookie_value )
{
    ob_start();
    setcookie($cookie_key, $cookie_value, time() + ( 15 * 60 ));
    ob_get_clean();
}

/**
 * Clears a cookie.
 *
 * @param string $cookie_key Cookie Key name.
 *
 * @return array
 */
function clear_chatondesk_cookie( $cookie_key )
{
    if (isset($_COOKIE[ $cookie_key ]) ) {
        unset($_COOKIE[ $cookie_key ]);
        setcookie($cookie_key, '', time() - ( 15 * 60 ));
    }
}

/**
 * Gets a cookie.
 *
 * @param string $cookie_key Cookie Key name.
 *
 * @return array
 */
function get_chatondesk_cookie( $cookie_key )
{
    if (! isset($_COOKIE[ $cookie_key ]) ) {
        return false;
    } else {
        return sanitize_text_field(wp_unslash($_COOKIE[ $cookie_key ]));
    }
}

/**
 * Gets key value from database.
 *
 * @param string $option  Option.
 * @param string $section Section.
 * @param string $default Default value.
 *
 * @return array
 */
function chatondesk_get_option( $option, $section, $default = '' )
{
    $options = get_option($section);

    if (isset($options[ $option ]) ) {
        return $options[ $option ];
    }
    return $default;
}

/**
 * Gets a template.
 *
 * @param string  $filepath File path.
 * @param array   $datas    Values to be used in template.
 * @param boolean $ret      Return as string.
 *
 * @return array
 */
function get_chatondesk_template( $filepath, $datas, $ret = false )
{
    if ($ret ) {
        ob_start();
    }
    extract($datas);
    include $filepath;
    if ($ret ) {
        return ob_get_clean();
    }
}

/**
 * PHP version 5
 *
 * @category Helper
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Plugin Name: ChatOnDesk - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/chat-on-desk/
 * Main class for plugin.
 */
class chatondesk_WC_Order_SMS
{

    /**
     * Constructor for the chatondesk_WC_Order_SMS class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return array
     */
    public function __construct()
    {
        // Instantiate necessary class.
        
        $this->instantiate();
        
        add_action('init', array( $this, 'registerHookSendSms' ));

        add_action('woocommerce_checkout_update_order_meta', array( $this, 'buyerNotificationUpdateOrderMeta' ));
        add_action('woocommerce_order_status_changed', array( 'ChatOnDesk\WooCommerceCheckOutForm', 'trigger_after_order_place' ), 10, 3);
        add_action('woocommerce_checkout_order_processed', array( $this, 'saWcOrderPlace' ), 10, 1);
        if (!did_action('woocommerce_checkout_order_processed') && is_admin()) {
            add_action('woocommerce_new_order', array( $this, 'saWcOrderPlace' ), 10, 1);
        }
        add_filter('cod_wc_order_sms_customer_before_send', array( 'ChatOnDesk\WooCommerceCheckOutForm', 'pharseSmsBody' ), 10, 2);
        add_filter('cod_wc_order_sms_admin_before_send', array( 'ChatOnDesk\WooCommerceCheckOutForm', 'pharseSmsBody' ), 10, 2);
        add_action('woocommerce_new_customer_note', array( 'ChatOnDesk\WooCommerceCheckOutForm', 'trigger_new_customer_note' ), 10);
        add_filter('default_checkout_billing_phone', array( $this, 'modifyBillingPhoneField' ), 1, 2); 
        add_action('user_register', array( $this, 'wcUserCreated' ), 1, 1);
        add_action('chatondesk_after_update_new_user_phone', array( $this, 'smsalertAfterUserRegister' ), 10, 2);

        include_once 'helper/formlist.php';
        include_once 'views/common-elements.php';
        include_once 'handler/forms/FormInterface.php';
        include_once 'handler/smsalert_form_handler.php';
        include_once 'helper/shortcode.php';

        if (is_admin() ) {
			add_action('admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ));
            add_filter('plugin_row_meta', array( $this, 'pluginRowMetaLink' ), 10, 4);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'addActionLinks' ));
        }
        //commented later we use for redirect after install , plugin.
        add_action('admin_init', array($this, 'smsalertPluginRedirect'));
        add_action('cod_addTabs', array( $this, 'addTabs' ), 10);
        add_filter('codDefaultSettings', array( $this, 'addDefaultSetting' ), 1);
    }
    
    /**
     * Onpage modify billing phone at checkout page when country code is enabled
     *
     * @param string $value Value of the field.
     * @param string $input Name of the field.
     *
     * @return void
     */
    public function modifyBillingPhoneField( $value, $input )
    {
        if ('billing_phone' === $input && ! empty($value) ) {
            return \ChatOnDesk\SmsAlertUtility::formatNumberForCountryCode($value);
        }
    }
    
    /**
     * This function is executed after a user is created.
     *
     * @param int $user_id User id of the user.
     *
     * @return void
     */
    public function wcUserCreated( $user_id )
    {
        $billing_phone = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : null;
        $billing_phone = apply_filters('cod_get_user_phone_no', $billing_phone, $user_id);
        $billing_phone = Chatondesk::checkPhoneNos($billing_phone);
        update_user_meta($user_id, 'billing_phone', $billing_phone);
        do_action('chatondesk_after_update_new_user_phone', $user_id, $billing_phone);
    }
    
    /**
     * This function is executed after a user has been registered.
     *
     * @param int    $user_id       Userid of the user.
     * @param string $billing_phone Phone number of the user.
     *
     * @return void
     */
    public function smsalertAfterUserRegister( $user_id, $billing_phone )
    {
        $user                = get_userdata($user_id);
        $role                = ( ! empty($user->roles[0]) ) ? $user->roles[0] : '';
        $role_display_name   = ( ! empty($role) ) ? self::get_user_roles($role) : '';
        $chatondesk_reg_notify = chatondesk_get_option('wc_user_roles_' . $role, 'chatondesk_signup_general', 'off');
        $sms_body_new_user   = chatondesk_get_option('signup_sms_body_' . $role, 'chatondesk_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

        $chatondesk_reg_admin_notify = chatondesk_get_option('admin_registration_msg', 'chatondesk_general', 'off');
        $sms_admin_body_new_user   = chatondesk_get_option('sms_body_registration_admin_msg', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));
        $admin_phone_number        = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
        $store_name = trim(get_bloginfo());

        if ('on' === $chatondesk_reg_notify && ! empty($billing_phone) ) {
            $search = array(
            '[username]',
            '[email]',
            '[billing_phone]',
            );

            $replace           = array(
            $user->user_login,
            $user->user_email,
            $billing_phone,
            );
            $sms_body_new_user = str_replace($search, $replace, $sms_body_new_user);
            $obj             = array();
            $obj['number']   = $billing_phone;
            $obj['sms_body'] = $sms_body_new_user;
            Chatondesk::sendsms($obj);
        }

        if ('on' === $chatondesk_reg_admin_notify && ! empty($admin_phone_number) ) {
            $search = array(
            '[username]',
            '[store_name]',
            '[email]',
            '[billing_phone]',
            '[role]',
            );

            $replace = array(
            $user->user_login,
            $store_name,
            $user->user_email,
            $billing_phone,
            $role_display_name,
            );

            $sms_admin_body_new_user = str_replace($search, $replace, $sms_admin_body_new_user);
            $nos                     = explode(',', $admin_phone_number);
            $admin_phone_number      = array_diff($nos, array( 'postauthor', 'post_author' ));
            $admin_phone_number      = implode(',', $admin_phone_number);
            // do_action( 'cod_send_sms', $admin_phone_number, $sms_admin_body_new_user ); //commented on 25-08-2021.
            $obj             = array();
            $obj['number']   = $admin_phone_number;
            $obj['sms_body'] = $sms_admin_body_new_user;
            Chatondesk::sendsms($obj);
        }
    }
    
    /**
     * This function adds tabs.
     *
     * @param array $tabs Default tabs.
     *
     * @return void
     */
    public static function addTabs( $tabs = array() )
    {
        $signup_param = array(
        'checkTemplateFor' => 'signup_temp',
        'templates'        => self::getSignupTemplates(),
        );

        $new_user_reg_param = array(
        'checkTemplateFor' => 'new_user_reg_temp',
        'templates'        => self::getNewUserRegisterTemplates(),
        );

        $tabs['user_registration']['nav']  = 'User Registration';
        $tabs['user_registration']['icon'] = 'dashicons-admin-users';

        $tabs['user_registration']['inner_nav']['wc_register']['title']        = __('Sign Up Notifications', 'chat-on-desk');
        $tabs['user_registration']['inner_nav']['wc_register']['tab_section']  = 'signup_templates';
        $tabs['user_registration']['inner_nav']['wc_register']['first_active'] = true;

        $tabs['user_registration']['inner_nav']['wc_register']['tabContent'] = $signup_param;
        $tabs['user_registration']['inner_nav']['wc_register']['filePath']   = 'views/message-template.php';

        $tabs['user_registration']['inner_nav']['wc_register']['icon']   = 'dashicons-admin-users';
        $tabs['user_registration']['inner_nav']['wc_register']['params'] = $signup_param;

        $tabs['user_registration']['inner_nav']['new_user_reg']['title']       = 'Admin Notifications';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tab_section'] = 'newuserregtemplates';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tabContent']  = $new_user_reg_param;
        $tabs['user_registration']['inner_nav']['new_user_reg']['filePath']    = 'views/message-template.php';
        $tabs['user_registration']['inner_nav']['new_user_reg']['params']      = $new_user_reg_param;

        return $tabs;
    }
    
    /**
     * Gets signup template.
     *
     * @return void
     */
    public static function getSignupTemplates()
    {
        $wc_user_roles = self::get_user_roles();

        $variables = array(
        '[username]'      => 'Username',
        '[store_name]'    => 'Store Name',
        '[email]'         => 'Email',
        '[billing_phone]' => 'Billing Phone',
        '[shop_url]'      => 'Shop Url',
        );

        $templates = array();
        foreach ( $wc_user_roles as $role_key  => $role ) {
            $current_val = chatondesk_get_option('wc_user_roles_' . $role_key, 'chatondesk_signup_general', 'on');

            $checkbox_name_id = 'chatondesk_signup_general[wc_user_roles_' . $role_key . ']';
            $textarea_name_id = 'chatondesk_signup_message[signup_sms_body_' . $role_key . ']';
            $text_body        = chatondesk_get_option('signup_sms_body_' . $role_key, 'chatondesk_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

            $templates[ $role_key ]['title']          = 'When ' . ucwords($role['name']) . ' is registered';
            $templates[ $role_key ]['enabled']        = $current_val;
            $templates[ $role_key ]['status']         = $role_key;
            $templates[ $role_key ]['text-body']      = $text_body;
            $templates[ $role_key ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $role_key ]['textareaNameId'] = $textarea_name_id;
            $templates[ $role_key ]['token']          = $variables;
        }
        return $templates;
    }

    /**
     * Gets new user registration template.
     *
     * @return void
     */
    public static function getNewUserRegisterTemplates()
    {
        $chatondesk_notification_reg_admin_msg = chatondesk_get_option('admin_registration_msg', 'chatondesk_general', 'on');
        $sms_body_registration_admin_msg     = chatondesk_get_option('sms_body_registration_admin_msg', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

        $templates = array();

        $new_user_variables = array(
        '[username]'      => 'Username',
        '[store_name]'    => 'Store Name',
        '[email]'         => 'Email',
        '[billing_phone]' => 'Billing Phone',
        '[role]'          => 'Role',
        '[shop_url]'      => 'Shop Url',
        );

        $templates['new-user']['title']          = 'When a new user is registered';
        $templates['new-user']['enabled']        = $chatondesk_notification_reg_admin_msg;
        $templates['new-user']['status']         = 'new-user';
        $templates['new-user']['text-body']      = $sms_body_registration_admin_msg;
        $templates['new-user']['checkboxNameId'] = 'chatondesk_general[admin_registration_msg]';
        $templates['new-user']['textareaNameId'] = 'chatondesk_message[sms_body_registration_admin_msg]';
        $templates['new-user']['token']          = $new_user_variables;

        return $templates;
    }

    /**
     * This function Adds default settings in configuration.
     *
     * @param array $defaults Default values.
     *
     * @return void
     */
    public static function addDefaultSetting( $defaults = array() )
    {
        $sms_body_registration_admin_msg = chatondesk_get_option('sms_body_registration_admin_msg', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

        $wc_user_roles = self::get_user_roles();
        foreach ( $wc_user_roles as $role_key => $role ) {
            $defaults['chatondesk_signup_general'][ 'wc_user_roles_' . $role_key ]   = 'off';
            $defaults['chatondesk_signup_message'][ 'signup_sms_body_' . $role_key ] = $sms_body_registration_admin_msg;
        }
        return $defaults;
    }

    
    /**
     * This function gets role display name from system name.
     *
     * @param bool $system_name System name of the role.
     *
     * @return void
     */
    public static function get_user_roles( $system_name = null )
    {
        global $wp_roles;
        $roles = $wp_roles->roles;

        if (! empty($system_name) && array_key_exists($system_name, $roles) ) {
            return $roles[ $system_name ]['name'];
        } else {
            return $roles;
        }
    }
    
    /**
     * Instantiate necessary Class
     *
     * @return void
     */
    public function instantiate()
    {
        spl_autoload_register(array( $this, 'chatondeskAutoload' ));
        new chatondesk_Setting_Options();
    }

    /**
     * Autoload class files on demand.
     *
     * @param string $class requested class name.
     *
     * @return void
     */
    public function chatondeskAutoload( $class )
    {

        include_once 'handler/smsalert_logic_interface.php';
        include_once 'handler/smsalert_phone_logic.php';
        include_once 'helper/sessionVars.php';
        include_once 'helper/utility.php';
        include_once 'helper/constants.php';
        include_once 'helper/messages.php';
		include_once 'helper/class-chatondesk.php';
		include_once 'classes/setting-options.php';
    }

    /**
     * Initializes the ChatOnDesk_WC_Order_SMS() class
     *
     * Checks for an existing ChatOnDesk_WC_Order_SMS() instance
     * and if it doesn't find one, creates it.
     *
     * @return void
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance ) {            
            ChatOnDesk_WC_Order_SMS::localization_setup();
            $instance = new ChatOnDesk_WC_Order_SMS();
        }
        return $instance;
    }

    /**
     * Sends an SMS.
     *
     * @param string $number   Number to send SMS.
     * @param string $content  Text of SMS to be sent.
     * @param string $schedule SMS schedule time.
     *
     * @return void
     */
    public function fnSaSendSms( $number, $content, $schedule = null )
    {
        $obj             = array();
        $obj['number']   = $number;
        $obj['sms_body'] = $content;
        $obj['schedule'] = $schedule;
        $response        = Chatondesk::sendsms($obj);
        return $response;
    }

    /**
     * Registers the send SMS hook.
     *
     * @return void
     */
    public function registerHookSendSms()
    {
        add_action('cod_send_sms', array( $this, 'fnSaSendSms' ), 10, 3);
    }
    
    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     *
     * @return void
     */
    public static function localization_setup()
    {
        load_plugin_textdomain('chat-on-desk', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Enques scripts to be loaded in admin section.
     *
     * @return void
     */
    public function adminEnqueueScripts()
    {
		wp_enqueue_style('admin-chatondesk-styles', plugins_url('css/admin.css', __FILE__), array(), \ChatOnDesk\SmsAlertConstants::SA_VERSION);
        
        wp_enqueue_style('admin-chatondesk-modal-styles', plugins_url('css/sms_alert_customer_validation_style.css', __FILE__), array(), \ChatOnDesk\SmsAlertConstants::SA_VERSION);
    
        wp_enqueue_script('admin-chatondesk-scripts', plugins_url('js/admin.js', __FILE__), array( 'jquery' ), \ChatOnDesk\SmsAlertConstants::SA_VERSION, true);
        wp_enqueue_script('admin-chatondesk-taggedinput', plugins_url('js/tagged-input.js', __FILE__), array( 'jquery' ), \ChatOnDesk\SmsAlertConstants::SA_VERSION, false);
        $user_authorize = new chatondesk_Setting_Options();
        wp_localize_script(
            'admin-chatondesk-scripts',
            'chatondesk',
            array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'whitelist_countries' => chatondesk_get_option('whitelist_country', 'chatondesk_general'),
            'alternate_channels' => chatondesk_get_option('alternate_channel', 'chatondesk_general'),
            'cod_default_countrycode' => chatondesk_get_option('default_country_code', 'chatondesk_general'),
            'islogged' => $user_authorize->is_user_authorised(),
            'pattern' => \ChatOnDesk\SmsAlertConstants::PATTERN_PHONE,
            )
        );
    }

    /**
     * Adds a meta row to plugin.
     *
     * @param string $plugin_meta Array of plugin meta.
     * @param string $plugin_file plugin base file.
     * @param string $plugin_data Array containing information about plugin.
     * @param string $status      status.
     *
     * @return void
     */
    public function pluginRowMetaLink( $plugin_meta, $plugin_file, $plugin_data, $status )
    {
        if (isset($plugin_data['slug']) && ( 'chat-on-desk' === $plugin_data['slug'] ) && ! defined('chatondesk_DIR') ) {
            $plugin_meta[] = '<a href="https://kb.smsalert.co.in/wordpress" target="_blank">' . __('Docs', 'chat-on-desk') . '</a>';
            $plugin_meta[] = '<a href="https://wordpress.org/support/plugin/chat-on-desk/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }

    /**
     * Adds an action link in admin section.
     *
     * @param array $links Array of action links.
     *
     * @return void
     */
    public function addActionLinks( $links )
    {
        $links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=chat-on-desk'));
        return $links;
    }

    /**
     * This function is executed on plugin activate.
     *
     * @return void
     */
    public static function runOnActivate()
    {
        
        if (! get_option('chatondesk_activation_date') ) {
            add_option('chatondesk_activation_date', date('Y-m-d'));
        }
        if (! wp_next_scheduled('chatondesk_balance_notify') ) {
            wp_schedule_event(time(), 'hourly', 'chatondesk_balance_notify');
        }
        if (!wp_next_scheduled('chatondesk_followup_sms') ) {
            $time_value = esc_attr(chatondesk_get_option('subscription_reminder_cron_time', 'chatondesk_general', '10:00'));
            wp_schedule_event(strtotime(get_gmt_from_date($time_value)), 'daily', 'chatondesk_followup_sms');
        }
        self::saCartActivate();
        
        //commented , use later for after plugin install.
        add_option('chatondesk_do_activation_redirect', true);
    }
    /**
     * Commented , use later for after plugin install.
     *
     * @return void
     */
    function smsalertPluginRedirect()
    {
        if (get_option('chatondesk_do_activation_redirect', false)) {
            delete_option('chatondesk_do_activation_redirect');
            wp_redirect("admin.php?page=chat-on-desk");
        }
    }                                  

    /**
     * This function is executed on plugin activate to create table for abondoned cart functionality.
     *
     * @return void
     */
    public static function saCartActivate()
    {
        global $wpdb, $table_name;

        $table_name      = $wpdb->prefix . COD_CART_TABLE_NAME;
        $tabl_name = $wpdb->prefix . "chatondesk_renewal_reminders";                                                    
        $reminder_table_name = $wpdb->prefix . "chatondesk_booking_reminder";                                                    
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			name VARCHAR(60),
			surname VARCHAR(60),
			email VARCHAR(100),
			phone VARCHAR(20),
			location VARCHAR(100),
			cart_contents LONGTEXT,
			cart_total DECIMAL(10,2),
			currency VARCHAR(10),
			time DATETIME DEFAULT '0000-00-00 00:00:00',
			session_id VARCHAR(60),
			msg_sent TINYINT NOT NULL DEFAULT 0,
			recovered TINYINT NOT NULL DEFAULT 0,
			other_fields LONGTEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

        $sql1 = "CREATE TABLE IF NOT EXISTS $tabl_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			subscription_id mediumint(9) NOT NULL,
			subscription_text text NOT NULL,
			source VARCHAR(50),
			next_payment_date date DEFAULT '0000-00-00' NOT NULL,
			notification_sent_date date DEFAULT '0000-00-00' NOT NULL,
			PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE IF NOT EXISTS $reminder_table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			booking_id mediumint(9) NOT NULL,
			phone VARCHAR(20),
			source VARCHAR(50),
			msg_sent TINYINT NOT NULL DEFAULT 0,
			start_date DATETIME DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id)
        ) $charset_collate;";        
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);           
        dbDelta($sql2);           
        dbDelta($sql);

        // Resets table Auto increment index to 1.
        $sql = "ALTER TABLE $table_name AUTO_INCREMENT = 1";
        dbDelta($sql);

        $ab_cart_fc_captured_abandoned_cart_count = get_option('ab_cart_fc_captured_abandoned_cart_count');
        if ($ab_cart_fc_captured_abandoned_cart_count ) {
            update_option('cart_captured_abandoned_cart_count', $ab_cart_fc_captured_abandoned_cart_count);
        }
        delete_option('ab_cart_fc_captured_abandoned_cart_count');

        $user_settings_notification_frequency = chatondesk_get_option('customer_notify', 'chatondesk_abandoned_cart', 'on');
        $user_cod_settings_notification_frequency = chatondesk_get_option('customer_notify', 'chatondesk_cod_to_prepaid', 'on');
        $wcbk_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_wcbk_general', 'off');
        $bc_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_bc_general', 'off');
        $rr_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_rr_general', 'off');
        $qr_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_qr_general', 'off');
        $eap_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_eap_general', 'off');
        $bcc_reminder_frequency = chatondesk_get_option('customer_notify', 'chatondesk_bcc_general', 'off');

        if ('off' === $user_settings_notification_frequency) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('ab_cart_cod_notification_sendsms_hook');
        } else {
            if (! wp_next_scheduled('ab_cart_cod_notification_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendsms_interval', 'ab_cart_cod_notification_sendsms_hook');
            }
        }
        
        if ('off' === $user_cod_settings_notification_frequency ) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('cod_to_prepaid_cart_notification_sendsms_hook');
        } else {
            if (! wp_next_scheduled('cod_to_prepaid_cart_notification_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendsms_interval',  'cod_to_prepaid_cart_notification_sendsms_hook');
            }
        }
        if (('off' === $wcbk_reminder_frequency && 'off' === $bc_reminder_frequency && 'off' === $rr_reminder_frequency && 'off' === $bcc_reminder_frequency && 'off' === $qr_reminder_frequency && 'off' === $eap_reminder_frequency) ) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('booking_reminder_sendsms_hook');
        } else {
            if (! wp_next_scheduled('booking_reminder_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendremindersms_interval', 'booking_reminder_sendsms_hook');
            }
        }
    }

    /**
     * Executes on plugin de-activate.
     *
     * @return void
     */
    public static function runOnDeactivate()
    {
        wp_clear_scheduled_hook('chatondesk_balance_notify');
        wp_clear_scheduled_hook('chatondesk_followup_sms');
        wp_clear_scheduled_hook('booking_reminder_sendsms_hook');
    }

    /**
     * Executes on plugin uninstall.
     *
     * @return void
     */
    public static function runOnUninstall()
    {
        global $wpdb;

        $main_table = $wpdb->prefix . 'cod_captured_wc_fields';

        $wpdb->query("DROP TABLE IF EXISTS $main_table");

        delete_option('cart_captured_abandoned_cart_count');
    }

    /**
     * Update Order buyer notify meta in checkout page
     *
     * @param integer $order_id Order id.
     *
     * @return void
     */
    public function buyerNotificationUpdateOrderMeta( $order_id )
    {
        if (! empty($_POST['buyer_sms_notify']) ) {
            update_post_meta($order_id, '_buyer_sms_notify', sanitize_text_field(wp_unslash($_POST['buyer_sms_notify'])));
        }
    }

    /**
     * Executes on order place event from woocommerce.
     *
     * @param integer $order_id Order id.
     *
     * @return void
     */
    public function saWcOrderPlace( $order_id )
    {
        if (! $order_id ) {
            return;
        }
        WooCommerceCheckOutForm::trigger_after_order_place($order_id, 'pending', 'pending');
    }
} // ChatOnDesk_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 *
     * @return void
 */
add_action('plugins_loaded', 'ChatOnDesk\loadCodWcOrderSms');

/**
 * Sets cron schedules.
 *
 * @param integer $intervals Interval at which cron to be executed.
 *
 * @return void
 */
function additionalCodCronIntervals( $intervals )
{
    $intervals['sendsms_interval'] = array(
    'interval' => CART_CRON_INTERVAL * 60,
    'display'  => 'Every 10 minutes',
    );
    $intervals['sendremindersms_interval'] = array(
    'interval' => BOOKING_REMINDER_CRON_INTERVAL * 60,
    'display'  => 'Every 60 minutes',
    );
    return $intervals;
}

add_filter('cron_schedules', 'ChatOnDesk\additionalCodCronIntervals');

/**
 * Executed on plugin load.
 *
 * @return void
 */
function loadCodWcOrderSms()
{
	$smsalert = ChatOnDesk_WC_Order_SMS::init();
}
register_activation_hook(__FILE__, array( 'ChatOnDesk\chatondesk_WC_Order_SMS', 'runOnActivate' ));
register_deactivation_hook(__FILE__, array( 'ChatOnDesk\chatondesk_WC_Order_SMS', 'runOnDeactivate' ));
register_uninstall_hook(__FILE__, array( 'ChatOnDesk\chatondesk_WC_Order_SMS', 'runOnUninstall' ));

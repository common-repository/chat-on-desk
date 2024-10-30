<?php
/**
 * WordPress settings API class
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
require_once ABSPATH . 'wp-admin/includes/plugin.php';
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Smsalert Setting Options class
 */
class chatondesk_Setting_Options
{
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     * @return stirng
     */
    public static function init()
    {
        include_once plugin_dir_path(__DIR__) . '/helper/class-shortcode.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/class-divi.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/class-wordpresswidget.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/countrylist.php';
        include_once plugin_dir_path(__DIR__) . '/helper/upgrade.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-backend.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/edd.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/learnpress.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/woocommerce-booking.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/events-manager.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/class-cartbounty.php';
        include_once plugin_dir_path(__DIR__) . '/helper/delivery-drivers-woocommerce.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-backinstock.php';
        include_once plugin_dir_path(__DIR__) . '/helper/wc-low-stock.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-blocks.php';
        include_once plugin_dir_path(__DIR__) . '/helper/review.php';
        include_once plugin_dir_path(__DIR__) . '/helper/share-cart.php';
		include_once plugin_dir_path(__DIR__) . '/helper/class-codpopup.php';
		include_once plugin_dir_path(__DIR__) . '/helper/class-codelementorwidget.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/class-terawallet.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/wc-subscriptions.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-abandonedcart.php';
        include_once plugin_dir_path(__DIR__) . '/helper/wc-integration.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/new-user-approve.php';
        //include_once plugin_dir_path(__DIR__) . '/helper/return-warranty.php';
        include_once plugin_dir_path(__DIR__)    .'/helper/signup-with-otp.php';
        include_once plugin_dir_path(__DIR__)    . '/helper/feedback.php';
        add_action('admin_menu', __CLASS__ . '::smsAlertWcSubmenu', 50);

        add_filter('um_predefined_fields_hook', __CLASS__ . '::myPredefinedFields', 10, 2);

       // add_action('verify_senderid_button', __CLASS__ . '::actionWoocommerceAdminFieldVerifySmsAlertUser');
        add_action('verify_chatondesk_user_button', __CLASS__ . '::actionWoocommerceAdminFieldVerifyChatondeskUser');
        add_action('admin_post_save_chatondesk_settings', __CLASS__ . '::save');
        if (! self::is_user_authorised() ) {
            add_action('admin_notices', __CLASS__ . '::showAdminNoticeSuccess');
        }

        self::chatondeskDashboardSetup();
		self::resetOTPModalStyle();

        if (array_key_exists('option', $_GET) ) {
            switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
            case 'chatondesk-woocommerce-senderlist':
                $user = isset($_GET['user']) ? sanitize_text_field(wp_unslash($_GET['user'])) : '';
                $pwd  = isset($_GET['pwd']) ? sanitize_text_field(wp_unslash($_GET['pwd'])) : '';
                wp_send_json(Chatondesk::getSenderids($user, $pwd));
                exit;	
            case 'chatondesk-woocommerce-logout':
                wp_send_json(self::logout());
                break;
			case 'chatondesk-woocommerce-countrylist':
                wp_send_json(Chatondesk::country_list());
                break;    
            }
        }
    }

    /**
     * Triggers when woocommerce is loaded.
     *
     * @return stirng
     */
    public static function action_woocommerce_loaded()
    {
        $cod_abcart = new SA_Abandoned_Cart();
        $cod_abcart->run();
    }

    /**
     * Add smsalert phone button in ultimate form.
     *
     * @param array $predefined_fields Default fields of the form.
     *
     * @return stirng
     */
    public static function myPredefinedFields( $predefined_fields )
    {
        $fields            = array(
        'billing_phone' => array(
        'title'    => 'Smsalert Phone',
        'metakey'  => 'billing_phone',
        'type'     => 'text',
        'label'    => 'Mobile Number',
        'required' => 0,
        'public'   => 1,
        'editable' => 1,
        'validate' => 'billing_phone',
        'icon'     => 'um-faicon-mobile',
        ),
        );
        $predefined_fields = array_merge($predefined_fields, $fields);
        return $predefined_fields;
    }
	
	    /**
     * RouteData function
     *
     * @return array
    */
    private static function resetOTPModalStyle()
    {
		if (!empty($_GET['action']) && $_GET['action']=='cod_reset_style') {            
            $post_name = trim(sanitize_text_field(wp_unslash($_GET['postname'])));			
            $page = get_page_by_title($post_name, OBJECT, 'chat-on-desk');
			
			if(!empty($page)){
							$post_ids       = $page->ID;
					if (!empty($post_ids) ) {							
							$delete_metadata = wp_delete_post($post_ids);                                
					}
					echo wp_json_encode(array("status"=>"success","description"=>"post deleted"));
					exit();
					
			}
            
        }
    }

    /**
     * Adds widgets to dashboard.
     *
     * @return stirng
     */
    public static function chatondeskDashboardSetup()
    {
        add_action('dashboard_glance_items', __CLASS__ . '::chatondeskAddDashboardWidgets', 10, 1);
    }

    /**
     * Prompts admin to login to Chat On Desk if not already logged in.
     *
     * @return stirng
     */
    public static function showAdminNoticeSuccess()
    {
        ?>
    <div class="notice notice-warning is-dismissible">
        <p>
        <?php
        /* translators: %s: plugin settings url */
        echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Login to Chat On Desk</a> to configure SMS Notifications', 'chat-on-desk'), 'admin.php?page=chat-on-desk'));
        ?>
        </p>
    </div>
        <?php
    }
    
    /**
     * Gets all payment gateways.
     *
     * @return stirng
     */
    public static function getAllGateways()
    {
        if (! is_plugin_active('woocommerce/woocommerce.php') ) {
            return array(); 
        }
        $gateways      = array();
        $payment_plans = WC()->payment_gateways->payment_gateways();
        foreach ( $payment_plans as $payment_plan ) {
            $gateways[] = $payment_plan->id;
        }
        return $gateways;
    }

    /**
     * Adds Chat On Desk in menu.
     *
     * @return stirng
     */
    public static function smsAlertWcSubmenu()
    {
        add_submenu_page('woocommerce', 'Chat On Desk', 'Chat On Desk', 'manage_options', 'chat-on-desk', __CLASS__ . '::settingsTab');
        
        add_submenu_page('options-general.php', 'Chat On Desk', 'Chat On Desk', 'manage_options', 'chat-on-desk', __CLASS__ . '::settingsTab');
		
        add_submenu_page(null, 'Abandoned Carts', __('Abandoned Carts', 'chat-on-desk'), 'manage_options', 'cod-ab-cart', array( 'ChatOnDesk\SA_Cart_Admin', 'display_page' ));
        add_submenu_page(null, 'Abandoned Carts', __('Abandoned Carts', 'chat-on-desk'), 'manage_options', 'cod-ab-cart-reports', array( 'ChatOnDesk\SA_Cart_Admin', 'display_reports_page' ));
    }

    /**
     * Checks if the user is logged in Chat On Desk plugin.
     *
     * @return stirng
     */
    public static function is_user_authorised()
    {
        $islogged          = false;
        $chatondesk_name     = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $chatondesk_password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway', '');
        $islogged          = false;
        if (! empty($chatondesk_name) && ! empty($chatondesk_password) ) {
            $islogged = true;
        }
        return $islogged;
    }

    /**
     * Adds Dashboard widgets.
     *
     * @param array $items Default widgets.
     *
     * @return stirng
     */
    public static function chatondeskAddDashboardWidgets( $items = array() )
    {
        if (self::is_user_authorised() ) {
            $credits = json_decode(Chatondesk::getCredits(), true);
            if (!empty($credits['description']['Wallet']['balance'])) {
                 $items[] = sprintf('<a href="%1$s" class="chatondesk-credit"><strong>Balance</strong> : %2$s %3$s</a>', admin_url('admin.php?page=chat-on-desk'), $credits['description']['Wallet']['currency'], $credits['description']['Wallet']['balance']);
            }
        }
        return $items;
    }

    /**
     * Logs out user from Chat On Desk plugin.
     *
     * @return void
     */
    public static function logout()
    {
        if (delete_option('chatondesk_gateway') ) {
            return true;
        }
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::getSettings()
     *
     * @return void
     */
    public static function settingsTab()
    {
        self::getSettings();
    }

    /**
     * Save data.
     *
     * @return void
     */
    public static function save()
    {
		$verify = check_ajax_referer('wp_save_chatondesk_settings_nonce', 'save_chatondesk_settings_nonce', false);
        if (!$verify) {
            wp_safe_redirect(admin_url('admin.php?page=chat-on-desk&m=1'));
            exit;
        }
        $_POST = chatondesk_sanitize_array($_POST);
        self::saveSettings($_POST);
    }

    /**
     * Save settings.
     *
     * @param array $options Default options.
     *
     * @return void
     */
    public static function saveSettings( $options )
    {
        if (empty($_POST) ) {
            return false;
        }

        $reset_settings = ( ! empty($_POST['chatondesk_reset_settings']) && ( 'on' === $_POST['chatondesk_reset_settings'] ) ) ? true : false;

        $defaults = array(
        'chatondesk_gateway'              => array(
        'chatondesk_name'     => '',
        'chatondesk_password' => '',
        'chatondesk_api'      => ''
        ),
        'chatondesk_message'              => array(
        'sms_admin_phone'                 => '',
        'sms_body_new_note'               => '',
        'sms_body_registration_msg'       => '',
        'sms_body_registration_admin_msg' => '',
        'sms_body_admin_low_stock_msg'    => '',
        'sms_body_admin_out_of_stock_msg' => '',
        'sms_otp_send'                    => '',
        ),
        'chatondesk_general'              => array(
        'buyer_checkout_otp'           => 'off',
        'buyer_signup_otp'             => 'off',
        'buyer_login_otp'              => 'off',
        'buyer_notification_notes'     => 'off',
        'allow_multiple_user'          => 'off',
        'admin_bypass_otp_login'       => array( 'administrator' ),
        'checkout_show_otp_button'     => 'off',
        'checkout_show_otp_guest_only' => 'off',
        'checkout_show_country_code'   => 'off',
        'enable_selected_country'      => 'off',
        'whitelist_country'            => '',
		'alternate_channel'      => '',
        'enable_chat_widget'             => 'off',
        'subscription_reminder_cron_time' => '10:00',
        'alert_email'                  => '',
        'otp_template_style'           => 'popup-1',
        'checkout_payment_plans'       => '',
        'otp_for_selected_gateways'    => 'off',
        'otp_for_roles'                => 'off',
        'otp_verify_btn_text'          => 'Click here to verify your Phone',
        'default_country_code'         => '91',
        'cod_mobile_pattern'            => '',
        'login_with_otp'               => 'off',
        'login_popup'                  => 'off',
        'hide_default_login_form'      => 'off',
        'registration_msg'             => 'off',
        'admin_registration_msg'       => 'off',
        'admin_low_stock_msg'          => 'off',
        'admin_out_of_stock_msg'       => 'off',
        'reset_password'               => 'off',
        'register_otp_popup_enabled'   => 'off',
        'post_order_verification'      => 'off',
        'pre_order_verification'       => 'off',
        ),
        'chatondesk_sync'                 => array(
        'last_sync_userId' => '0',
        ),
        'chatondesk_background_task'      => array(
        'last_updated_lBal_alert' => '',
        ),
        'chatondesk_background_dBal_task' => array(
        'last_updated_dBal_alert' => '',
        ),
        'chatondesk_edd_general'          => array(),
        );

        $defaults = apply_filters('codDefaultSettings', $defaults);
        $_POST['chatondesk_general']['checkout_payment_plans'] = isset($_POST['chatondesk_general']['checkout_payment_plans']) ? maybe_serialize($_POST['chatondesk_general']['checkout_payment_plans']) : array();
        $options = array_replace_recursive($defaults, array_intersect_key($_POST, $defaults));

        foreach ( $options as $name => $value ) {
            if ($reset_settings ) {
                delete_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
        wp_safe_redirect(admin_url('admin.php?page=chat-on-desk&m=1'));
        exit;
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return void
     */
    public static function getSettings()
    {

        global $current_user;
        wp_get_current_user();

        $chatondesk_name                                = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $chatondesk_password                            = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway', '');
        $chatondesk_api                                 = chatondesk_get_option('chatondesk_api', 'chatondesk_gateway', '');
        $alternate_channel                                 = (array)chatondesk_get_option('alternate_channel', 'chatondesk_general', null);
        $has_woocommerce                              = is_plugin_active('woocommerce/woocommerce.php');
        $has_w_p_members                              = is_plugin_active('wp-members/wp-members.php');
        $has_ultimate                                 = ( is_plugin_active('ultimate-member/ultimate-member.php') || is_plugin_active('ultimate-member/index.php') ) ? true : false;
        $has_woocommerce_bookings                     = ( is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ) ? true : false;
        $has_e_m_bookings                             = ( is_plugin_active('events-manager/events-manager.php') ) ? true : false;
        $has_w_p_a_m                                  = ( is_plugin_active('affiliates-manager/boot-strap.php') ) ? true : false;
        $has_learn_press                              = ( is_plugin_active('learnpress/learnpress.php') ) ? true : false;
        $has_cart_bounty                              = ( is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php') ) ? true : false;
        $has_booking_calendar                         = ( is_plugin_active('booking/wpdev-booking.php') ) ? true : false;
        $sms_admin_phone                              = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
        $sms_body_on_hold                             = chatondesk_get_option('sms_body_on-hold', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_ON_HOLD'));
        $sms_body_processing                          = chatondesk_get_option('sms_body_processing', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_PROCESSING'));
        $sms_body_completed                           = chatondesk_get_option('sms_body_completed', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_COMPLETED'));
        $sms_body_cancelled                           = chatondesk_get_option('sms_body_cancelled', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_CANCELLED'));
        $sms_body_registration_msg                    = chatondesk_get_option('sms_body_registration_msg', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));
        $sms_otp_send                                 = chatondesk_get_option('sms_otp_send', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_OTP'));
        $chatondesk_notification_checkout_otp           = chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general', 'on');
        $chatondesk_notification_signup_otp             = chatondesk_get_option('buyer_signup_otp', 'chatondesk_general', 'on');
        $chatondesk_notification_login_otp              = chatondesk_get_option('buyer_login_otp', 'chatondesk_general', 'on');
        $chatondesk_notification_reg_msg                = chatondesk_get_option('registration_msg', 'chatondesk_general', 'on');
        $chatondesk_notification_out_of_stock_admin_msg = chatondesk_get_option('admin_out_of_stock_msg', 'chatondesk_general', 'on');
        $chatondesk_allow_multiple_user                 = chatondesk_get_option('allow_multiple_user', 'chatondesk_general', 'on');
        $admin_bypass_otp_login                       = maybe_unserialize(chatondesk_get_option('admin_bypass_otp_login', 'chatondesk_general', array( 'administrator' )));
        $checkout_show_otp_button                     = chatondesk_get_option('checkout_show_otp_button', 'chatondesk_general', 'off');
        $checkout_show_otp_guest_only                 = chatondesk_get_option('checkout_show_otp_guest_only', 'chatondesk_general', 'on');

        $checkout_show_country_code = chatondesk_get_option('checkout_show_country_code', 'chatondesk_general', 'off');
        $enable_selected_country    = chatondesk_get_option('enable_selected_country', 'chatondesk_general', 'off');
        $enable_reset_password      = chatondesk_get_option('reset_password', 'chatondesk_general', 'off');
        $register_otp_popup_enabled = chatondesk_get_option('register_otp_popup_enabled', 'chatondesk_general', 'off');
        $otp_verify_btn_text        = chatondesk_get_option('otp_verify_btn_text', 'chatondesk_general', 'Click here to verify your Phone');
        $default_country_code       = chatondesk_get_option('default_country_code', 'chatondesk_general', '');
        $cod_mobile_pattern          = chatondesk_get_option('cod_mobile_pattern', 'chatondesk_general', '');
        $login_with_otp             = chatondesk_get_option('login_with_otp', 'chatondesk_general', 'off');
        $login_popup                = chatondesk_get_option('login_popup', 'chatondesk_general', 'off');
        $hide_default_login_form    = chatondesk_get_option('hide_default_login_form', 'chatondesk_general', 'off');
        $subscription_reminder_cron_time           = chatondesk_get_option('subscription_reminder_cron_time', 'chatondesk_general', '10:00');
        $enable_chat_widget           = chatondesk_get_option('enable_chat_widget', 'chatondesk_general', 'off');
        $alert_email                = chatondesk_get_option('alert_email', 'chatondesk_general', $current_user->user_email);
        $modal_style                = chatondesk_get_option('modal_style', 'chatondesk_general', '');
        $checkout_payment_plans     = maybe_unserialize(chatondesk_get_option('checkout_payment_plans', 'chatondesk_general', null));
        $otp_for_selected_gateways  = chatondesk_get_option('otp_for_selected_gateways', 'chatondesk_general', 'on');
        $otp_for_roles              = chatondesk_get_option('otp_for_roles', 'chatondesk_general', 'on');
        $islogged                   = false;
        $hidden                     = '';
        $credit_show                = 'hidden';
        $chatondesk_helper            = '';
        if (! empty($chatondesk_name) && ! empty($chatondesk_password) ) {
				$credits = json_decode(Chatondesk::getCredits(), true);

				if ('success' === $credits['status'] ) {
					$islogged    = true;
					$hidden      = 'hidden';
					$credit_show = '';
				}

				if ('error' === $credits['status'] ) {
					/* translators: %1$s: Chat On Desk support Email ID, %2$s: Chat On Desk support Email ID */
					$chatondesk_helper = ( ! $islogged ) ? sprintf(__('Please contact <a href="mailto:%1$s">%2$s</a> to activate your Demo Account.', 'chat-on-desk'), 'support@cozyvision.com', 'support@cozyvision.com') : '';
				}
        } else {
            /* translators: %1$s: Chat On Desk website URL, %2$s: Current website URL */
			$siteurl = 'www.chatondesk.com';
            $chatondesk_helper = ( ! $islogged ) ? sprintf(__('Please enter below your <a href="%1$s" target="_blank">'.$siteurl.'</a> login details to link it with %2$s', 'chat-on-desk'), 'https://www.chatondesk.com', get_bloginfo()) : '';
        }
        ?>
        <form method="post" id="chatondesk_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <div class="ChatOnDesk_box ChatOnDesk_settings_box">
                <div class="ChatOnDesk_nav_tabs">
        <?php
        $params = array(
         'hasWoocommerce'     => $has_woocommerce,
         'hasWPmembers'       => $has_w_p_members,
         'hasUltimate'        => $has_ultimate,
         'hasWPAM'            => $has_w_p_a_m,
         'credit_show'        => $credit_show,
         'hasCartBounty'      => $has_cart_bounty,
         'hasBookingCalendar' => $has_booking_calendar,
        );
        get_chatondesk_template('views/smsalert_nav_tabs.php', $params);
        ?>
                </div>
                <div>
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_global_box ChatOnDesk_active general">
                    <!--general tab-->
        <?php
        $params = array(
         'chatondesk_helper'   => $chatondesk_helper,
         'chatondesk_name'     => $chatondesk_name,
         'chatondesk_password' => $chatondesk_password,
         'hidden'            => $hidden,
         'chatondesk_api'      => $chatondesk_api,
         'alternate_channel'  => $alternate_channel,
         'islogged'          => $islogged,
         'sms_admin_phone'   => $sms_admin_phone,
         'hasWoocommerce'    => $has_woocommerce,
         'hasWPAM'           => $has_w_p_a_m,
         'hasEMBookings'     => $has_e_m_bookings,
        );
        get_chatondesk_template('views/chatondesk_general_tab.php', $params);
        ?>
                    </div>
                    <!--/-general tab-->
        <?php
        $tabs = apply_filters('cod_addTabs', array());
        $sno  = 1;
        foreach ( $tabs as $tab ) {
            if (array_key_exists('nav', $tab) ) {
                ?>
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>_box <?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>">
                        <div class="tabset">
                            <ul>
                <?php foreach ( $tab['inner_nav'] as $in_tab ) { ?>
                            <li>
                                <input type="radio" name="tabset<?php echo esc_attr($sno); ?>" id="tab<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" aria-controls="<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" <?php echo ( ! empty($in_tab['first_active']) ) ? 'checked' : ''; ?>>
                                <label for="tab<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>"><?php echo esc_attr($in_tab['title']); ?></label>
                            </li>    
                            
                            
                <?php } ?>
                            <li class="more_tab hide">
                                <a href="javascript:void(0)"><span class="dashicons dashicons-menu-alt"></span></a>
                                <ul style="display:none"></ul>
                            </li>
                            </ul>
                            <div class="tab-panels">
                <?php
                foreach ( $tab['inner_nav'] as $in_tab ) {
                    ?>
                                <section id="<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" class="tab-panel">
                    <?php
                    if (is_array($in_tab['tabContent']) ) {
                        get_chatondesk_template($in_tab['filePath'], $in_tab['tabContent']);
                    } else {
                        echo ( ! empty($in_tab['tabContent']) ) ? $in_tab['tabContent'] : '';
                    }
                    ?>
                                    <!--help links-->
                    <?php
                                
                    if (isset($in_tab['help_links']) ) {
                                
                        foreach ($in_tab['help_links'] as $link) {
                               echo wp_kses_post('<a href="'.$link['href'].'" alt="'.$link['alt'].'" target="'.$link['target'].'" class="'.$link['class'].'">'.$link['icon']." ".$link['label'].'</a>');
                        }
                    } 
                    ?>
                            <!--/-help links-->
                                </section>
                                                            
                <?php } ?>
                            </div>
                            <!--help links-->
                <?php
                                
                if (!empty($tab['help_links']) ) {
                                
                    foreach ($tab['help_links'] as $link) {
                        echo wp_kses_post('<a href="'.$link['href'].'" alt="'.$link['alt'].'" target="'.$link['target'].'" class="'.$link['class'].'">'.$link['icon']." ".$link['label'].'</a>');
                    }
                } 
                ?>
                            <!--/-help links-->
                            
                        </div>
                    </div>
            <?php } else { ?>
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_<?php echo esc_attr($tab['tab_section']); ?>_box <?php echo esc_attr($tab['tab_section']); ?>">
                <?php
                if (is_array($tab['tabContent']) ) {
                    get_chatondesk_template($tab['filePath'], $tab['tabContent']);
                } else {
                    echo ( ! empty($tab['tabContent']) ) ? $tab['tabContent'] : '';
                }
                ?>
                            
                <?php
                if (!empty($tab['help_links']) ) {
                                
                    foreach ($tab['help_links'] as $links) {
                        foreach ($links as $link) {
                               echo '<a href="'.esc_attr($link['href']).'" alt="'.esc_attr($link['alt']).' target="'.esc_attr($link['target']).'">'.esc_attr($link['text']).'</a>';
                        }
                    }
                } 
                ?>
                            
                            
                    </div>
            <?php } $sno++;
        } ?>
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_otp_section_box otpsection"><!--otp_section tab-->
        <?php
        $user          = wp_get_current_user();
        $off_excl_role = false;
        if (in_array('administrator', (array) $user->roles, true) ) {
            $user_id       = $user->ID;
            $user_phone    = get_user_meta($user_id, 'billing_phone', true);
            $off_excl_role = empty($user_phone) ? true : false;
        }
        if (! is_array($checkout_payment_plans) ) {
            $checkout_payment_plans = self::getAllGateways();
        }

        $params = array(
         'chatondesk_notification_checkout_otp' => $chatondesk_notification_checkout_otp,
         'chatondesk_notification_signup_otp' => $chatondesk_notification_signup_otp,
         'chatondesk_notification_login_otp'  => $chatondesk_notification_login_otp,
         'has_w_p_members'                  => $has_w_p_members,
         'has_woocommerce'                  => $has_woocommerce,
         'has_ultimate'                     => $has_ultimate,
         'has_w_p_a_m'                      => $has_w_p_a_m,
         'sms_otp_send'                     => $sms_otp_send,
         'login_with_otp'                   => $login_with_otp,
         'login_popup'                      => $login_popup,
         'hide_default_login_form'          => $hide_default_login_form,
         'enable_reset_password'            => $enable_reset_password,
         'has_learn_press'                  => $has_learn_press,
         'otp_for_selected_gateways'        => $otp_for_selected_gateways,
         'register_otp_popup_enabled'       => $register_otp_popup_enabled,
         'checkout_show_otp_button'         => $checkout_show_otp_button,
         'checkout_show_otp_guest_only'     => $checkout_show_otp_guest_only,
         'checkout_show_country_code'       => $checkout_show_country_code,
         'otp_verify_btn_text'              => $otp_verify_btn_text,
         'checkout_payment_plans'           => $checkout_payment_plans,
         'chatondesk_allow_multiple_user'     => $chatondesk_allow_multiple_user,
         'otp_for_roles'                    => $otp_for_roles,
         'off_excl_role'                    => $off_excl_role,
         'admin_bypass_otp_login'           => $admin_bypass_otp_login,
        );

        get_chatondesk_template('views/otp-section-template.php', $params);
        ?>
                    </div>
                    <!--/-otp_section tab-->
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_callbacks_box callbacks "><!--otp tab-->
                        <!--enable country code -->
                        <div class="cod-accordion">
                            <div class="accordion-section">
                                <a class="cod-accordion-body-title" href="javascript:void(0)" data-href="#accordion_10"> 
                                <input type="checkbox" name="chatondesk_general[checkout_show_country_code]" id="chatondesk_general[checkout_show_country_code]" class="notify_box" <?php echo ( ( 'on' === $checkout_show_country_code ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[checkout_show_country_code]"><?php esc_attr_e('Enable Country Code Selection', 'chat-on-desk'); ?></label><span class="expand_btn"></span>
                                </a>
                                <div id="accordion_10" class="cod-accordion-body-content" style="height:150px">
                                    <table class="form-table">
                                        <tr valign="top">
                                            <td class="td-heading" style="width:30%">
                                                <input data-parent_id="chatondesk_general[checkout_show_country_code]" type="checkbox" name="chatondesk_general[enable_selected_country]" id="chatondesk_general[enable_selected_country]" class="notify_box" <?php echo ( ( 'on' === $enable_selected_country ) ? "checked='checked'" : '' ); ?> parent_accordian="callbacks"/><label for="chatondesk_general[enable_selected_country]"><?php esc_attr_e('Show only selected countries', 'chat-on-desk'); ?></label>
                                                <span class="tooltip" data-title="Enable Selected Countries before phone field"><span class="dashicons dashicons-info"></span></span>
                                            </td>                                        
                                            <td>
        <?php
        $whitelist_country = (array) chatondesk_get_option('whitelist_country', 'chatondesk_general', null);
        $content = '<select name="'.'chatondesk_general[whitelist_country][]" id="whitelist_country" multiple class="multiselect chosen-select" data-parent_id="'.'chatondesk_general[enable_selected_country]" parent_accordian="callbacks">';
        foreach ( $whitelist_country as $key => $country_code ) {
            $content .= '<option value="' . esc_attr($country_code) . '" selected="selected"></option>';
        }
        $content .= '</select>';

        $content .= '<script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>';
        echo $content;
        ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>    
                        <!--/--enable country code -->                        
                        <div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
                        <style>.top-border{border-top:1px dashed #b4b9be;}</style>
                        <table class="form-table">
                            <tr valign="top">
                                <td scope="row" class="td-heading"><?php esc_attr_e('Default Country', 'chat-on-desk'); ?>
                                </td>
                                <td>
        <?php
        $default_country_code = chatondesk_get_option('default_country_code', 'chatondesk_general');
        $content              = '<select name="'.'chatondesk_general[default_country_code]" id="default_country_code" onchange="choseMobPattern(this)">';
        $content .= '<option value="' . esc_attr($default_country_code) . '" selected="selected">Loading...</option>';
        $content .= '</select>';
        echo $content;
        ?>
                                    <span class="tooltip" data-title="Default Country for mobile number format validation"><span class="dashicons dashicons-info"></span></span>
                                    <input type="hidden" name="chatondesk_general[cod_mobile_pattern]" id="cod_mobile_pattern" value="<?php echo esc_attr($cod_mobile_pattern); ?>"/>
                                </td>
                            </tr>                            
                            <style>
                            .otp .tags-input-wrapper {float:left;}
                            </style>
                            <tr valign="top" class="top-border">
                                <td scope="row" class="td-heading"><?php esc_attr_e('Alerts', 'chat-on-desk'); ?>
                                </td>
                                <td>
                                    <input type="text" name="chatondesk_general[alert_email]" class="admin_email " id="chatondesk_general[alert_email]" value="<?php echo esc_attr($alert_email); ?>" style="width: 40%;" parent_accordian="callbacks">

                                    <span class="tooltip" data-title="Send Alerts for low balance & daily balance etc."><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <!--Time for sending SMS Notification-->
        <?php
        if (is_plugin_active('membermouse/index.php') || is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php') || is_plugin_active('wpadverts/wpadverts.php') || is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) {
            ?>
                                    <tr valign="top" class="top-border">
                                <th scope="row">
                                        <label for="chatondesk_general[subscription_reminder_cron_time]"><?php esc_html_e('Cron run time for reminder notification:', 'chat-on-desk'); ?></label>
                                    </th>
                                    <td>
                                    <input type="time" name="chatondesk_general[subscription_reminder_cron_time]" id="chatondesk_general[subscription_reminder_cron_time]" value="<?php echo esc_attr($subscription_reminder_cron_time); ?>" ><span class="tooltip" data-title="Time to send out the reminder notification"><span class="dashicons dashicons-info"></span></span>
                                        </td>
                                </tr>
            <?php
        }     
        ?>
    
                            <!--enable chat widget-->
                            <tr valign="top" >
                                <td scope="row"> </td>
                                <td class="td-heading">
                                    <input type="checkbox" name="chatondesk_general[enable_chat_widget]" id="chatondesk_general[enable_chat_widget]" class="notify_box" <?php echo ( ( 'on' === $enable_chat_widget ) ? "checked='checked'" : '' ); ?> />
                                        <label for="chatondesk_general[enable_chat_widget]"><?php esc_attr_e('Enable Chat Widget', 'chat-on-desk'); ?></label>
                                    <span class="tooltip" data-title="Enable Chat Widget"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <tr valign="top" class="top-border">
                                <td scope="row" class="td-heading"><?php esc_attr_e('Modal Style', 'chat-on-desk'); ?></td>
                                <td class="td-heading">
            <?php
             $styles = array(
              'modal-slideIn' => 'Slide',
              'modal-fadeIn' => 'FadeIn',
              'modal-flipIn' => 'Flip',
              'modal-signIn' => 'Sign',
             )
                ?>
                                    <select name="chatondesk_general[modal_style]" id="modal_style">
                                        <option value="">Default</option>
             <?php
                foreach ( $styles as $k => $v ) {
                    ?>
                                        <option value="<?php echo esc_attr($k); ?>" <?php echo ( $modal_style === $k ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($v); ?></option>
                <?php } ?>
                                    </select>
                                    <span class="tooltip" data-title="Select Modal Style Effect"><span class="dashicons dashicons-info"></span></span>
                                    <span class="dashicons dashicons-search" onclick="previewtemplate();" style="margin-left: 25px; cursor:pointer"></span>
                                </td>
                            </tr>
                            <!--/-Modal style-->
                            <!--reset all settings-->
                            
                            <tr valign="top" class="top-border">
                                <td scope="row" class="td-heading" style="vertical-align: top;padding-top: 15px;"><?php esc_attr_e('Danger Zone', 'chat-on-desk'); ?></td>
                                <td class="td-heading">
                                <input type="checkbox" name="chatondesk_reset_settings" id="chatondesk_reset_btn" class="ChatOnDesk_box notify_box hide chatondesk_reset" />
                                    <p><?php esc_attr_e('Once you reset templates, there is no going back. Please be certain.', 'chat-on-desk'); ?></p><br/>
                                    <input type="button" name="chatondesk_reset_setting_btn" id="chatondesk_reset_settings" class="ChatOnDesk_box notify_box button button-danger" value="<?php esc_attr_e('Reset all Templates & Settings', 'chat-on-desk'); ?>"/>
                                    <span class="tooltip" data-title="Reset All Settings"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <!--/-reset all settings-->
          <?php //} ?>
                        </table>
                        </div>
                    </div><!--/-otp tab-->
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_credits_box balance <?php echo esc_attr($credit_show); ?>">        <!--credit tab-->
                        <div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
                            <table class="form-table">
                                <tr valign="top">
                                    <td>
            <?php
            if ($islogged && !empty($credits['description']['Wallet']['balance']) ) {
                echo '<h2><strong>'.__('Wallet Balance', 'chat-on-desk').'</strong></h2>';
                    ?>
                                        <div class="col-lg-12 creditlist" >
                                            <div class="col-lg-8 route">
                                                <h3><span class="dashicons dashicons-email"></span> <?php echo esc_attr(ucwords($credits['description']['Wallet']['currency'])); ?></h3>
                                            </div>
                                            <div class="col-lg-4 credit">
                                                <h3><?php echo esc_attr($credits['description']['Wallet']['balance']); ?></h3>
                                            </div>
                                        </div>
                    <?php
            }
            ?>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <td>
                                        <p><b><?php esc_attr_e('Need More balance?', 'chat-on-desk'); ?></b>
             <?php
                /* translators: %s: Chat On Desk Pricing URL */
                echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Click Here</a> to purchase. ', 'chat-on-desk'), 'https://www.chatondesk.com/pricing/'));
                ?>
                                        </p>    
                                    </td>
                                </tr>
                            </table>
                        </div>
                        </div><!--/-credit tab-->
                    <div class="ChatOnDesk_nav_box ChatOnDesk_nav_support_box support"><!--support tab-->
         <?php get_chatondesk_template('views/support.php', array()); ?>
                    </div><!--/-support tab-->
                    <script>
                    jQuery('.more_tab a').click(function(){
                        jQuery(this).next().toggle();                    
                    });    
                    /*tagged input start*/
                    // Email Alerts
                    var adminemail     = "<?php echo esc_attr($alert_email); ?>";
                    var tagInput2     = new TagsInput({
                        selector: 'chatondesk_general[alert_email]',
                        duplicate : false,
                        max : 10,
                    });
                    var email = (adminemail!='') ? adminemail.split(",") : [];
                    if (email.length >= 1){
                        tagInput2.addData(email);
                    }
                    //Send Admin SMS To
        <?php if ($islogged ) { ?>
                    var adminnumber = "<?php echo esc_attr($sms_admin_phone); ?>";
                    var tagInput1     = new TagsInput({
                        selector: 'chatondesk_message[sms_admin_phone]',
                        duplicate : false,
                        max : 10,
                    });
                    var number = (adminnumber!='') ? adminnumber.split(",") : [];
                    if (number.length > 0) {
                        tagInput1.addData(number);
                    }
        <?php } ?>
                    /*tagged input end*/
                    // on checkbox enable-disable select
                    function choseMobPattern(obj){
                        var pattern = jQuery('option:selected', obj).attr('data-pattern');
                        jQuery('#cod_mobile_pattern').val(pattern);
                    }
                    </script>
                </div>
            </div>
            <p class="submit"><input type="submit" id="chatondesk_bckendform_btn" class="button button-primary" value="Save Changes" /></p>
        </form>
        <!--reset modal-->
        <?php
		$params = array(
        'modal_id'     => 'chatondesk_reset_style_modal',
        'modal_title'  => __('Are you sure?', 'chat-on-desk'),
        'modal_body'   => __('This action can not be reversed. Default style will be set.', 'chat-on-desk'),
        'modal_footer' => '<button type="button" data-dismiss="cod-modal" class="button button-danger" id="cconfirmed">Yes</button>
				<button type="button" data-dismiss="cod-modal" class="button button-primary btn_cancel">No</button>',
        );
        get_chatondesk_template('views/alert-modal.php', $params);
        $params = array(
        'modal_id'     => 'chatondesk_reset_modal',
        'modal_title'  => __('Are you sure?', 'chat-on-desk'),
        'modal_body'   => __('This action can not be reversed. You will be logged out of Chat On Desk plugin.', 'chat-on-desk'),
        'modal_footer' => '<button type="button" data-dismiss="cod-modal" class="button button-danger" id="confirmed">Yes</button>
				<button type="button" data-dismiss="cod-modal" class="button button-primary btn_cancel">No</button>',
        );
        get_chatondesk_template('views/alert-modal.php', $params);
        add_action('admin_footer', array( 'ChatOnDesk\SAVerify', 'add_shortcode_popup_html' )); 
        wp_localize_script(
            'admin-chatondesk-scripts',
            'alert_msg',
            array(
            'otp_error'             => __('Please add OTP tag in OTP Template.', 'chat-on-desk'),
            'payment_gateway_error' => __('Please choose any payment gateway.', 'chat-on-desk'),
            'invalid_email'         => __('You have entered an invalid email address in Advanced Settings option!', 'chat-on-desk'),
            'invalid_sender'        => __('Please choose your senderid.', 'chat-on-desk'),
            'low_alert'             => __('Value must be greater than or equal to 100.', 'chat-on-desk'),
            'wcountry_err'          => __('Please choose any country.', 'chat-on-desk'),
            'dcountry_err'          => __('Please choose default country from selected countries', 'chat-on-desk'),
            'last_item'             => __('last Item Cannot be deleted.', 'chat-on-desk'),
            'global_country_err'             => __('You will have to enable Country Code Selection because you have selected global country.', 'chat-on-desk'),
            )
        );
        ?>
        <!--Choose otp token  modal-->
        <?php
        $params = array(
        'modal_id'     => 'cod_backend_modal',
        'modal_title'  => __('Alert', 'chat-on-desk'),
        'modal_body'   => '',
        'modal_footer' => '<button type="button" data-dismiss="cod-modal" class="button button-primary btn_cancel">OK</button>',
        );
        get_chatondesk_template('views/alert-modal.php', $params);
        ?>
        <script>
        var isSubmitting = false; 		
        function showAlertModal(msg)
        {
            jQuery("#cod_backend_modal").addClass("cod-show");
            jQuery("#cod_backend_modal").find(".cod-modal-body").text(msg);
            jQuery("#cod_backend_modal").after('<div class="cod-modal-backdrop cod-fade"></div>');
            jQuery(".cod-modal-backdrop").addClass("cod-show");            
        }

        jQuery('#chatondesk_bckendform_btn').click(function(){
            jQuery(".ChatOnDesk_nav_box").find(".hasError").removeClass("hasError");
            jQuery(".ChatOnDesk_nav_box").find(".hasErrorField").removeClass("hasErrorField");
            jQuery("#cod_backend_modal").find(".modal_body").text("");            
            var payment_plans = jQuery('#checkout_payment_plans :selected').map((_,e) => e.value).get();            
            var whitelist_countries = jQuery('#whitelist_country :selected').map((_,e) => e.value).get();    
            jQuery('select').removeAttr('disabled',false);            
            isSubmitting = true;            
            if (jQuery('[name="chatondesk_gateway[chatondesk_api]"]').val()=='SELECT' || jQuery('[name="chatondesk_gateway[chatondesk_api]"]').val()=='')
            {
                showAlertModal(alert_msg.invalid_sender);
                var menu_accord = jQuery('[name="chatondesk_gateway[chatondesk_api]"]').attr("parent_accordian");
                jQuery('[name="chatondesk_gateway[chatondesk_api]"]').addClass("hasErrorField");
                jQuery('[name="chatondesk_gateway[chatondesk_api]"]').parents(".ChatOnDesk_nav_box").addClass("hasError").attr("menu_accord",menu_accord);                
                jQuery('[tab_type=global]').trigger('click');
                window.location.hash = '#general';
                return false;
            }        
             else if ((jQuery('[name="chatondesk_general[default_country_code]"]').val() == '' && !jQuery('[name="chatondesk_general[checkout_show_country_code]"]').prop("checked")))
            {
                showAlertModal(alert_msg.global_country_err);                
                var menu_accord = jQuery('[name="chatondesk_general[checkout_show_country_code]"]').attr("parent_accordian");
                jQuery('[name="chatondesk_general[checkout_show_country_code]"]').addClass("hasErrorField");
                jQuery('[name="chatondesk_general[checkout_show_country_code]"]').parents(".ChatOnDesk_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;    
            } else if (jQuery('[name="chatondesk_general[buyer_checkout_otp]"]').prop("checked") && jQuery('[name="chatondesk_general[otp_for_selected_gateways]"]').prop("checked") && payment_plans.length==0)
            {
                showAlertModal(alert_msg.payment_gateway_error);                
                var menu_accord = jQuery('[name="chatondesk_general[otp_for_selected_gateways]"]').attr("parent_accordian");
                var payment_plans = jQuery('[name="chatondesk_general[otp_for_selected_gateways]"]').parents(".ChatOnDesk_nav_box").find("#checkout_payment_plans_chosen");                
                payment_plans.find(".chosen-choices").addClass("hasErrorField");
                payment_plans.parents(".ChatOnDesk_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;
            } else if (jQuery('[name="chatondesk_general[checkout_show_country_code]"]').prop("checked") && jQuery('[name="chatondesk_general[enable_selected_country]"]').prop("checked") && whitelist_countries.length==0)
            {
                showAlertModal(alert_msg.wcountry_err);                
                var menu_accord = jQuery('#whitelist_country').attr("parent_accordian");
                var whitelist_country = jQuery('#whitelist_country').parents(".ChatOnDesk_nav_box").find("#whitelist_country_chosen");                
                whitelist_country.find(".chosen-choices").addClass("hasErrorField");
                whitelist_country.parents(".ChatOnDesk_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;
            } else if (jQuery('[name="chatondesk_general[checkout_show_country_code]"]').prop("checked") && jQuery('[name="chatondesk_general[enable_selected_country]"]').prop("checked") && jQuery.inArray( jQuery("#default_country_code").val(), whitelist_countries )==-1)
            {
                showAlertModal(alert_msg.dcountry_err);                
                var menu_accord = jQuery('[name="chatondesk_general[whitelist_country]"]').attr("parent_accordian");
                var default_country_code = jQuery("#default_country_code");
                default_country_code.addClass("hasErrorField");
                default_country_code.focus();
                return false;
            } else if (jQuery('[name="chatondesk_general[alert_email]"]').val() != '')
            {
                var alert_email = jQuery('[name="chatondesk_general[alert_email]"]');
                var inputText = alert_email.val();
                var email = inputText.split(',');

                for (i = 0; i < email.length; i++) {
                    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;
                    if (!email[i].match(mailformat)) {
                        showAlertModal(alert_msg.invalid_email);                        
                        alert_email.parent().find(".tags-input-wrapper").addClass("hasErrorField");
                        //jQuery('[tab_type=callbacks]').trigger('click');
                        var menu_accord = jQuery('[name="chatondesk_general[alert_email]"]').attr("parent_accordian");
                        jQuery('[name="chatondesk_general[alert_email]"]').parents(".ChatOnDesk_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                        return false;
                    }
                }
            } else if (jQuery('#chatondesk_form')[0].checkValidity()) {
                var url     = jQuery("#chatondesk_form").attr('action');
                var hash     = window.location.hash;
                jQuery('#chatondesk_form').attr('action', url+hash);
                jQuery('#chatondesk_form').submit();
            }
        });

        //check before leave page
        jQuery('form').data('initial-state', jQuery('form').serialize());

        jQuery(window).on('beforeunload', function() {
            if (!isSubmitting && jQuery('form').serialize() != jQuery('form').data('initial-state')){
                return 'You have unsaved changes which will not be saved.';
            }
        });
        
        //modal preview
        function previewtemplate() {
            var selected_modal = '<?php echo !empty(SmsAlertUtility::get_elementor_data("form_list"))?SmsAlertUtility::get_elementor_data("form_list"):'popup-1';?>'; 
            jQuery('.otp-number').removeClass('hide');
            if (selected_modal=='popup-1')
            {
                jQuery('.chatondesk_validate_field').removeClass('digit-group');
                jQuery('.chatondesk_validate_field').removeClass('popup-3');
                jQuery('.otp-number').addClass('hide');
            } else if (selected_modal=='popup-2')
            {
                jQuery('.chatondesk_validate_field').addClass('digit-group');
                jQuery('.chatondesk_validate_field').removeClass('popup-3');
            } else if (selected_modal=='popup-3')
            {
                jQuery('.chatondesk_validate_field').addClass('digit-group popup-3');
            }
            var selectedValue = document.getElementById("modal_style").value;
                selectedValue = (selectedValue != '')? selectedValue : 'center';
            var modal_c = jQuery(".modal.chatondeskModal").attr('class');
            var modal_style = selectedValue.slice(0, -2);
            jQuery(".modal.chatondeskModal").removeClass(modal_c).addClass('modal chatondeskModal preview '+selectedValue);
            if ( selectedValue != 'center' ){
                jQuery(".modal.chatondeskModal").attr("data-modal-close", modal_style);
            }
            jQuery('.modal.chatondeskModal .cod-message').addClass('preview-message');
            jQuery(".modal.chatondeskModal").show();
        }
        jQuery(document).on("click", ".close",function(){
            jQuery(".blockUI").hide();
            var modal_style = jQuery(this).parents().find('.modal.chatondeskModal').attr('data-modal-close');
            jQuery(this).parents().find('.modal.chatondeskModal').addClass(modal_style+'Out');
            jQuery(this).parents(".modal.chatondeskModal").not('.chatondesk-modal').hide('slow');
            setTimeout(function() {
                jQuery('.modal.chatondeskModal').removeClass(modal_style+'Out');
            }, 500);
        });
        </script>
        <script>
        //add token variable on admin and customer template 21/07/2020
        window.addEventListener('message', receiveMessage, false);
        function receiveMessage(evt) {
            if (evt.data.type=='chatondesk_token')
            {
                var txtbox_id =  jQuery('.cod-accordion-body-content.open').find('textarea').attr('id');
                insertAtCaret(evt.data.token, txtbox_id);
                tb_remove();
            }
        }
        </script>
        <?php
        return apply_filters('wc_sms_alert_setting', array());
    }
	
	/**
     * Verifies if Chat On Desk credentials are correct.
     *
     * @param string $value Value.
     *
     * @return void
     */
    public static function actionWoocommerceAdminFieldVerifyChatondeskUser( $value )
    {
        global $current_user;
        wp_get_current_user();
        $chatondesk_name     = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $chatondesk_password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway', '');
        $hidden            = '';
        if (! empty($chatondesk_name) && ! empty($chatondesk_password) ) {
            $hidden = 'hidden';
        }
        ?>
            <tr valign="top" class="<?php echo esc_attr($hidden); ?>">
                <th>&nbsp;</th>
                <td>
                    <a href="#" class="button-primary woocommerce-save-button" onclick="verifyChatondeskUser(this); return false;"><?php esc_attr_e('verify and continue', 'chat-on-desk'); ?></a>
        <?php
        $link = 'https://www.chatondesk.com/?name=' . rawurlencode($current_user->user_firstname . ' ' . $current_user->user_lastname) . '&email=' . rawurlencode($current_user->user_email) . '&phone=&username=' . preg_replace('/\s+/', '_', strtolower(get_bloginfo())) . '#register';
        /* translators: %s: Chat On Desk Signup URL */
        echo wp_kses_post(sprintf(__('Don\'t have an account on Chat On Desk? <a href="%s" target="_blank">Signup Here for FREE</a> ', 'chat-on-desk'), $link));
        ?>
                <div id="verify_status"></div>
                </td>
            </tr>
        <?php
    }

    /**
     * Verifies if Chat On Desk credentials are correct.
     *
     * @param string $value Value.
     *
     * @return void
     */
    public static function actionWoocommerceAdminFieldVerifySmsAlertUser( $value )
    {
        global $current_user;
        wp_get_current_user();
        $chatondesk_name     = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $chatondesk_password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway', '');
        $hidden            = '';
        if (! empty($chatondesk_name) && ! empty($chatondesk_password) ) {
            $credits = json_decode(Chatondesk::getCredits(), true);
            if (( 'success' === $credits['status'] ) || ( is_array($credits['description']) && ( 'no senderid available for your account' === $credits['description']['desc'] ) ) ) {
                $hidden = 'hidden';
            }
        }
        ?>
            <tr valign="top" class="<?php echo esc_attr($hidden); ?>">
                <th>&nbsp;</th>
                <td>
                    <a href="#" class="button-primary woocommerce-save-button" onclick="verifyUser(this); return false;"><?php esc_attr_e('verify and continue', 'chat-on-desk'); ?></a>
        <?php
        $link = 'https://www.chatondesk.com/?name=' . rawurlencode($current_user->user_firstname . ' ' . $current_user->user_lastname) . '&email=' . rawurlencode($current_user->user_email) . '&phone=&username=' . preg_replace('/\s+/', '_', strtolower(get_bloginfo())) . '#register';
        /* translators: %s: Chat On Desk Signup URL */
        echo wp_kses_post(sprintf(__('Don\'t have an account on Chat On Desk? <a href="%s" target="_blank">Signup Here for FREE</a> ', 'chat-on-desk'), $link));
        ?>
                <div id="verify_status"></div>
                </td>
            </tr>
        <?php
    }
}
chatondesk_Setting_Options::init();
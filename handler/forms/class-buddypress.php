<?php
/**
 * This file handles buddypress sms notification
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

if (! defined('ABSPATH') ) {
    exit;
}
if (! is_plugin_active('buddypress/bp-loader.php') ) {
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
 *
 * BuddyPressRegistrationForm class.
 */
class BuddyPressRegistrationForm extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @var stirng
     */
     
    private $form_session_var = \ChatOnDesk\FormSessionVars::BUDDYPRESS_REG;
    
    /**
     * Handles registration form submit.
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter('cod_get_user_phone_no', array( $this, 'saUpdateBillingPhone' ), 10, 2);
        
        add_filter('bp_nouveau_get_signup_fields', array( $this,'smsalertBpAddSignupPhoneField' ), 10);
        
        add_action('bp_before_registration_submit_buttons', array( $this, 'bpSiteRegistrationOtp' ), 10);
        
        add_filter('login_form_bottom', array( $this, 'bpSiteLoginOtp' ), 10, 2);
        $enabled_login_with_otp = chatondesk_get_option('login_with_otp', 'chatondesk_general');        
        if ('on' === $enabled_login_with_otp ) {        
              add_action('bp_login_widget_form', array( $this, 'bpSiteLoginMobile' ), 10);
        }        
        add_filter('bp_core_validate_user_signup', array( $this, 'bpSiteRegistrationErrors' ), 10, 1);        
    }
    
    /**
     * This function displays a OTP button on registration form.
     *
     * @return void
     */
    public function bpSiteRegistrationOtp()
    {
        $enabled_register_popup = chatondesk_get_option('register_otp_popup_enabled', 'chatondesk_general');
        $buyer_signup_otp = chatondesk_get_option('buyer_signup_otp', 'chatondesk_general');
        if ('on' === $buyer_signup_otp && 'on' === $enabled_register_popup ) {
            echo do_shortcode('[cod_verify phone_selector=".billing_phone" submit_selector="signup_submit"]');    
        }      
    }
    
    /**
     * Add shortcode to buddypress form.
     *
     * @param array $content form content.
     * @param array $args    form fields.
     *
     * @return void
     */
    public function bpSiteLoginOtp($content = '', $args = array())
    {
        $default_login_otp   = chatondesk_get_option('buyer_login_otp', 'chatondesk_general');
        $enabled_login_popup = chatondesk_get_option('login_popup', 'chatondesk_general');        
        if ('on' === $default_login_otp && 'on' === $enabled_login_popup ) {
            return  do_shortcode('[cod_verify user_selector="#bp-login-widget-user-login" pwd_selector="#bp-login-widget-user-pass" submit_selector="#bp-login-widget-submit"]');
        }
    }
    
    /**
     * Display Button login with otp
     *
     * @param array $form form form.
     * @param array $args form args.
     *
     * @return void
     */
    public function bpSiteLoginMobile($form = null, $args=array())
    {
        if ($form == null || is_array($form) || $form == 'login') {
               echo '<div class="lwo-container"><div class="cod_or">OR</div><button type="button" class="button cod_myaccount_btn" name="cod_myaccount_btn_login" value="' . __('Login with OTP', 'chat-on-desk') . '" style="width: 100%;box-sizing: border-box">' . __('Login with OTP', 'chat-on-desk') . '</button></div>';
            add_action('wp_footer', array( $this, 'addLoginwithotpShortcode' ), 15);
        }
    }
    /**
     * Add login with otp shortcode.
     *
     * @return string
     */
    public static function addLoginwithotpShortcode()
    {
        echo '<div class="codloginwithotp">'.do_shortcode('[cod_loginwithotp]').'</div>';
        echo '<style>.codloginwithotp .cod_loginwithotp-form{display:none;}.codloginwithotp .cod_default_login_form{display:block;}</style>';
    }
    
    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public static function isFormEnabled()
    {    
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        return ( $islogged && chatondesk_get_option('buyer_signup_otp', 'chatondesk_general') === 'on' ) ? true : false;
    }
    
    /**
     * Update phone field
     *
     * @param string $billing_phone billing phone
     * @param int    $user_id       user id
     *
     * @return void
     */
    public function saUpdateBillingPhone($billing_phone, $user_id)
    {
        if (isset($_POST['signup_phone'])) {
            $phone = $_POST['signup_phone'];
            return ( ! empty($billing_phone) ) ? $billing_phone : $phone;
        }
        return $billing_phone;
    }

    /**
     * Show buddypress site registration errors.
     *
     * @param array $result result
     *
     * @return array
     */
    public function bpSiteRegistrationErrors($result)
    {  
        $email = ! empty($_REQUEST['signup_email']) ? sanitize_text_field(wp_unslash($_REQUEST['signup_email'])) : '';
        $username = ! empty($_REQUEST['signup_username']) ? sanitize_text_field(wp_unslash($_REQUEST['signup_username'])) : '';
        $password = ! empty($_REQUEST['signup_password']) ? sanitize_text_field(wp_unslash($_REQUEST['signup_password'])) : '';
        $user_phone = ( ! empty($_POST['signup_phone']) ) ?sanitize_text_field(wp_unslash($_POST['signup_phone'])) : '';
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (isset($_SESSION['bp_mobile_verified']) ) {              
            unset($_SESSION['bp_mobile_verified']);
            return $result;
        }        
        if (!empty($result['errors']->errors)) {
            return $result;
        }        
        $enabled_register_popup = chatondesk_get_option('register_otp_popup_enabled', 'chatondesk_general');  
        \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        if (chatondesk_get_option('allow_multiple_user', 'chatondesk_general') !== 'on' && ! \ChatOnDesk\SmsAlertUtility::isBlank($user_phone) ) {    
            $getusers = \ChatOnDesk\SmsAlertUtility::getUsersByPhone('signup_phone', $user_phone);
            if (count($getusers) > 0 ) {             
                $bp = buddypress();
                $bp->signup->errors['signup_phone'] =  __('An account is already registered with this mobile number!', 'chat-on-desk');
                $bp->signup->signup_phone = $_POST['signup_phone'];
                return $result;
            }            
        }
        return $this->processFormFields($username, $email, $password);
    }

    /**
     * Initialise the otp verification.
     *
     * @param string $username username
     * @param string $email    email
     * @param string $password password
     *
     * @return array
     */    
    public function processFormFields($username, $email, $password)
    {
        global $phoneCodLogic;
        $extra_data= null;
        $phone_no  = ( ! empty($_POST['signup_phone']) ) ? sanitize_text_field(wp_unslash($_POST['signup_phone'])) : '';
        $phone_num = preg_replace('/[^0-9]/', '', $phone_no);
        if (! isset($phone_num) || ! \ChatOnDesk\SmsAlertUtility::validatePhoneNumber($phone_num) ) {
            $bp = buddypress();
            $bp->signup->errors['signup_phone'] =  __(str_replace('##phone##', $phone_num, $phoneCodLogic->_get_otp_invalid_format_message()), 'chat-on-desk');
        }
        chatondesk_site_challenge_otp($username, $email, $password, $phone_num, 'phone', $extra_data);
    } 

    /**
     * Add Phone field to buddypress registration form.
     *
     * @param array $fields form fields.
     *
     * @return array
     */
    public function smsalertBpAddSignupPhoneField( $fields )
    {
        $fields['account_details']['signup_phone'] = array(
        'label'          => __('Phone', 'chat-on-desk'),
        'required'       => true,
        'value'          => '',
        'attribute_type' => 'phone',
        'type'           => 'text',
        'class'          => 'billing_phone',
        );
        return $fields;
    }

    /**
     * Handle after failed verification
     *
     * @param object $user_login   users object.
     * @param string $user_email   user email.
     * @param string $phone_number phone number.
     *
     * @return void
     */
    public function handle_failed_verification( $user_login, $user_email, $phone_number )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        if (isset($_SESSION[ $this->form_session_var ]) ) {
            chatondesk_site_otp_validation_form($user_login, $user_email, $phone_number, \ChatOnDesk\SmsAlertUtility::_get_invalid_otp_method(), 'phone', false);
        } 
    }

    /**
     * Handle after post verification
     *
     * @param string $redirect_to  redirect url.
     * @param object $user_login   user object.
     * @param string $user_email   user email.
     * @param string $password     user password.
     * @param string $phone_number phone number.
     * @param string $extra_data   extra hidden fields.
     *
     * @return void
     */
    public function handle_post_verification( $redirect_to, $user_login, $user_email, $password, $phone_number, $extra_data )
    {    
        \ChatOnDesk\SmsAlertUtility::checkSession();    
        if (isset($_SESSION[ $this->form_session_var ]) || ((empty($_REQUEST['option']) === false ) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'chatondesk-validate-otp-form')) {
            
            $_SESSION['bp_mobile_verified'] = true;
        }
    }

    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[ $this->tx_session_id ]);
        unset($_SESSION[ $this->form_session_var ]);
    }

    /**
     * Check current form submission is ajax or not
     *
     * @param bool $is_ajax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play( $is_ajax )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        return $is_ajax;
    }

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleFormOptions()
    {  
    }
}
    new BuddyPressRegistrationForm();

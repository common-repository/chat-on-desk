<?php
/**
 * Smsalert form handler 
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
    require_once 'forms/woocommerce/wc-checkout.php';
    require_once 'forms/woocommerce/wc-registration.php';
    require_once 'forms/class-wplogin.php';
    require_once 'forms/class-wpforms.php';
	require_once 'forms/class-elementor.php';
	require_once 'forms/class-contactform7.php';
	require_once 'forms/class-fluentform.php';
	require_once 'forms/class-formidable.php';
	require_once 'forms/class-forminator.php';
    //require_once 'forms/class-metform.php';
    require_once 'forms/class-everestform.php';
    require_once 'forms/class-jetform.php';
    //require_once 'forms/class-wployalty.php';
    //require_once 'forms/class-membermouse.php';
    //require_once 'forms/class-bookingcalendar.php';
    //require_once 'forms/class-bookitcalendar.php';
    //require_once 'forms/class-buddypress.php';
	//require_once 'forms/class-fatservicesbooking.php';
    //require_once 'forms/class-restaurantreservation.php';
    //require_once 'forms/class-quickrestaurantreservation.php';
    //require_once 'forms/class-wperp.php';
    //require_once 'forms/class-fluentcrm.php';
    //require_once 'forms/class-groundhoggcrm.php';
    //require_once 'forms/class-jetpack.php';
    //require_once 'forms/class-armember.php';
    //require_once 'forms/class-ultimatemember.php';
    //require_once 'forms/class-userregistration.php';
    //require_once 'forms/class-easyregistration.php';
    //require_once 'forms/class-vendorregistration.php';
    //require_once 'forms/class-profileregistration.php';
    //require_once 'forms/class-wcfmarketplace.php';
    //require_once 'forms/class-easyappointments.php';
    //require_once 'forms/class-userswpform.php';
    require_once 'forms/class-ninjaform.php';
    //require_once 'forms/class-wpmember.php';
    //require_once 'forms/class-pieregistration.php';
    //require_once 'forms/class-affiliatemanager.php';
    require_once 'forms/class-wpresetpassword.php';
    //require_once 'forms/class-learnpressregistration.php';    
    
    //require_once 'forms/class-gravityform.php';
    //require_once 'forms/class-wpadverts.php';
    //require_once 'forms/class-paidmembershippro.php';

    add_action('init', 'chatondesk_customer_validation_handle_form', 1);
    add_action('chatondesk_validate_otp', '_handle_cod_validation_form_action', 1, 2);

    /**
     * Generate and show OTP form.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $errors       Errors.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $password     Password.
     * @param string $extra_data   Extra form data.
     * @param string $from_both    Form name.
     *
     * @return void
     */
function chatondesk_site_challenge_otp( $user_login, $user_email, $errors, $phone_number, $otp_type, $password = '', $extra_data = null, $from_both = false )
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    $_SESSION['current_url']     = \ChatOnDesk\SmsAlertUtility::currentPageUrl();
    $_SESSION['user_email']      = $user_email;
    $_SESSION['user_login']      = $user_login;
    $_SESSION['user_password']   = $password;
    $_SESSION['phone_number_mo'] = $phone_number;
    $_SESSION['extra_data']      = $extra_data;
    _handle_cod_otp_action($user_login, $user_email, $phone_number, $otp_type, $from_both);
}

    /**
     * Handles resend OTP.
     *
     * @param string $otp_type  OTP type.
     * @param string $from_both Form name.
     *
     * @return void
     */
function _handle_cod_verification_resend_otp_action( $otp_type, $from_both )
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    $user_email   = sanitize_email($_SESSION['user_email']);
    $user_login   = sanitize_text_field($_SESSION['user_login']);
    $password     = sanitize_text_field($_SESSION['user_password']);
    $phone_number = sanitize_text_field($_SESSION['phone_number_mo']);
    $extra_data   = sanitize_text_field($_SESSION['extra_data']);
    _handle_cod_otp_action($user_login, $user_email, $phone_number, $otp_type, $from_both);
}

    /**
     * Handles OTP action.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
function _handle_cod_otp_action( $user_login, $user_email, $phone_number, $otp_type, $form )
{
	 global $phoneCodLogic;
     $phoneCodLogic->_handle_logic($user_login, $user_email, $phone_number, $otp_type, $form);
}

    /**
     * Handles Go back action.
     *
     * @return void
     */
function _handle_cod_validation_goBack_action()
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    $url = isset($_SESSION['current_url']) ? sanitize_text_field($_SESSION['current_url']) : '';
    session_unset();
    wp_safe_redirect($url);
    exit();
}

    /**
     * Handles OTP validation action.
     *
     * @param string $requestVariable Request variable.
     * @param string $from_both       Form name.
     *
     * @return void
     */
function _handle_cod_validation_form_action( $requestVariable = 'chatondesk_customer_validation_otp_token', $from_both = false )
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    $_REQUEST        = ChatOnDesk\chatondesk_sanitize_array($_REQUEST);
    $user_login      = ! \ChatOnDesk\SmsAlertUtility::isBlank($_SESSION['user_login']) ? sanitize_text_field(wp_unslash($_SESSION['user_login'])) : null;
    $user_email      = ! \ChatOnDesk\SmsAlertUtility::isBlank($_SESSION['user_email']) ? sanitize_email(wp_unslash($_SESSION['user_email'])) : null;
    $phone_number    = ( array_key_exists('billing_phone', $_REQUEST) && ! empty($_REQUEST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_REQUEST['billing_phone'])) : null;
    $phone_number    = array_key_exists('phone_number_mo', $_SESSION) && ! \ChatOnDesk\SmsAlertUtility::isBlank($_SESSION['phone_number_mo']) ? sanitize_text_field($_SESSION['phone_number_mo']) : $phone_number;
    $password        = ! \ChatOnDesk\SmsAlertUtility::isBlank($_SESSION['user_password']) ? sanitize_text_field($_SESSION['user_password']) : null;
    $extra_data      = ! \ChatOnDesk\SmsAlertUtility::isBlank($_SESSION['extra_data']) ? ChatOnDesk\chatondesk_sanitize_array($_SESSION['extra_data']) : null;
    $requestVariable = ( array_key_exists('phone', $_REQUEST) && ! array_key_exists('chatondesk_customer_validation_otp_token', $_REQUEST) ) ? sanitize_text_field(wp_unslash($_REQUEST['phone'])) : 'chatondesk_customer_validation_otp_token';

    $otp_token = ! empty($_REQUEST[ $requestVariable ]) ? sanitize_text_field(wp_unslash($_REQUEST[ $requestVariable ])) : null;
	$content = json_decode(\ChatOnDesk\Chatondesk::validateOtpToken($phone_number, $otp_token), true); 	
    if (( 'success' === $content['status'] ) && isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') === 0 ) {
        _handle_cod_success_validated($user_login, $user_email, $password, $phone_number, $extra_data);
    } else {
        _handle_cod_error_validated($user_login, $user_email, $phone_number);
    }
}

    /**
     * Handles Success validation action.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $password     Password.
     * @param string $phone_number Phone number.
     * @param string $extra_data   Extra form data.
     *
     * @return void
     */
function _handle_cod_success_validated( $user_login, $user_email, $password, $phone_number, $extra_data )
{
    $redirect_to = array_key_exists('redirect_to', $_POST) ? sanitize_text_field(wp_unslash($_POST['redirect_to'])) : '';
    do_action('otp_verification_successful', $redirect_to, $user_login, $user_email, $password, $phone_number, $extra_data);
}

    /**
     * Handles Error validation action.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     *
     * @return void
     */
function _handle_cod_error_validated( $user_login, $user_email, $phone_number )
{
    do_action('otp_verification_failed', $user_login, $user_email, $phone_number);
}



    /**
     * Handles ajax phone validation action.
     *
     * @param string $getdata Extra form data.
     *
     * @return void
     */
function _handle_cod_mo_ajax_phone_validate( $getdata )
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    $_SESSION[ \ChatOnDesk\FormSessionVars::AJAX_FORM ] = trim($getdata['billing_phone']);
    chatondesk_site_challenge_otp(
        sanitize_text_field($_SESSION['user_login']),
        null,
        null,
        trim(sanitize_text_field($data['billing_phone'])),
        'phone',
        sanitize_text_field($_SESSION['user_password']),
        null,
        null
    );
}

    /**
     * Handles ajax form validation action.
     *
     * @return void
     */
function _handle_cod_mo_ajax_form_validate_action()
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    if (isset($_SESSION[ \ChatOnDesk\FormSessionVars::WC_SOCIAL_LOGIN ]) ) {
        _handle_cod_validation_form_action();
        if ('validated' === $_SESSION[ \ChatOnDesk\FormSessionVars::WC_SOCIAL_LOGIN ] ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response('successfully validated', 'success'));
        } else {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
        }
    }
}

    /**
     * Handles create user action.
     *
     * @param string $postdata Extra form data.
     *
     * @return void
     */
function _handle_cod_mo_create_user_wc_action( $postdata )
{
    \ChatOnDesk\SmsAlertUtility::checkSession();
    if (isset($_SESSION[ \ChatOnDesk\FormSessionVars::WC_SOCIAL_LOGIN ]) && ( 'validated' === $_SESSION[ \ChatOnDesk\FormSessionVars::WC_SOCIAL_LOGIN ] ) ) {
        create_new_wc_social_customer($postdata);
    }
}

    /**
     * Handles Customer validation action.
     *
     * @return void
     */
function chatondesk_customer_validation_handle_form()
{

    $from_both = isset($_POST['from_both']) ? sanitize_text_field(wp_unslash($_POST['from_both'])) : '';
    $options   = isset($_REQUEST['option']) ? trim(sanitize_text_field(wp_unslash($_REQUEST['option']))) : '';

    if (! empty($options) ) {

        switch ( $options ) {
        case 'validation_goBack':
            _handle_cod_validation_goBack_action();
            break;
        case 'chatondesk-ajax-otp-generate':
            _handle_cod_mo_ajax_phone_validate($_GET);
            break;
        case 'chatondesk-ajax-otp-validate':
            _handle_cod_mo_ajax_form_validate_action($_GET);
            break;
        case 'chatondesk_ajax_form_validate':
            _handle_cod_mo_create_user_wc_action($_POST);
            break;
        case 'chatondesk-validate-otp-form':
            $from_both = ( true === $from_both ) ? true : false;
            _handle_cod_validation_form_action();
            break;
        case 'verification_resend_otp_phone':
            $from_both = ( true === $from_both ) ? true : false;
            _handle_cod_verification_resend_otp_action('phone', $options);
            break;
        case 'verification_resend_otp_email':
            $from_both = ( true === $from_both ) ? true : false;
            _handle_cod_verification_resend_otp_action('email', $options);
            break;
        case 'verification_resend_otp_both':
            $from_both = ( true === $from_both ) ? true : false;
            _handle_cod_verification_resend_otp_action('both', $options);
            break;
        }
    }
}


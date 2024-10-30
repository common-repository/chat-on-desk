<?php
/**
 * This file handles learnpress sms notification
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
if (! is_plugin_active('learnpress/learnpress.php') ) {
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
 * LearnpressRegistrationForm class.
 */
class LearnpressRegistrationForm extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::LEARNPRESS_DEFAULT_REG;


    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter('learn-press/new-user-data', array( $this, 'learnpressSiteRegistrationErrors' ), 8, 1);
        add_filter('learn-press/register-fields', array( $this, 'smsalertLearnpressAddPhoneField' ));
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
     * Show error message if found.
     *
     * @param string $error_hook error hook if defined.
     * @param string $err_msg    error message.
     * @param string $type       error type.
     *
     * @return object
     */
    public function show_error_msg( $error_hook = null, $err_msg = null, $type = null )
    {
        if (isset($_SESSION[ $this->form_session_var2 ]) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response($err_msg, $type));
        } else {
            return new WP_Error($error_hook, $err_msg);
        }
    }

    /**
     * Show Learnpress site registration errors.
     *
     * @param array  $datas    posted data by user.
     * @param string $username registered username.
     * @param string $password user password.
     * @param string $email    user email id.
     *
     * @return array
     */
    public function learnpressSiteRegistrationErrors( $datas = null, $username = null, $password = null, $email = null )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        $errors = array();
        if (isset($_SESSION['cod_lpress_mobile_verified']) ) {
            unset($_SESSION['cod_lpress_mobile_verified']);
            return $datas;
        }

        if (! empty($datas) ) {
            $username = $datas['user_login'];
            $email    = $datas['user_email'];
            $password = $datas['user_pass'];
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        }

        return $this->processFormFields($username, $email, $errors, $password);
    }

    /**
     * Initialise the otp verification.
     *
     * @param string $username registered username.
     * @param string $email    user email id.
     * @param object $errors   form error if found.
     * @param string $password user password.
     *
     * @return array
     */
    public function processFormFields( $username, $email, $errors, $password )
    {
        global $phoneCodLogic;
        $phone = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';

        if (! isset($phone) || ! \ChatOnDesk\SmsAlertUtility::validatePhoneNumber($phone) ) {
            return new WP_Error('billing_phone_error', str_replace('##phone##', Chatondesk::checkPhoneNos($phone), $phoneCodLogic->_get_otp_invalid_format_message()));
        }
        chatondesk_site_challenge_otp($username, $email, $errors, $phone, 'phone', $password);
    }

    /**
     * Add Phone field to learn press registration form.
     *
     * @param array $fields form fields.
     *
     * @return array
     */
    public function smsalertLearnpressAddPhoneField( $fields )
    {
        $add_fields = array(
        'billing_phone' => array(
        'title'       => __('Billing Phone', 'learnpress'),
        'type'        => 'text',
        'placeholder' => __('Billing Phone', 'learnpress'),
        'id'          => 'billing_phone',
        'required'    => true,
        ),
        );
        $fields     = array_merge($fields, $add_fields);
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
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        $_SESSION['cod_lpress_mobile_verified'] = true;
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
    new LearnpressRegistrationForm();

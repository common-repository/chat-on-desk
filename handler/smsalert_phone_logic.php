<?php
/**
 * Smsalert phone logic 
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

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Phone logic class.
 */
class PhoneLogic extends \ChatOnDesk\LogicInterface
{

    /**
     * Main Logic handler.
     *
     * @param string $user_login   User name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_logic( $user_login, $user_email, $phone_number, $otp_type, $form )
    {
		//$match = preg_match( \ChatOnDesk\SmsAlertConstants::getPhonePattern(), $phone_number );
        
        /* switch ( $match ) {
        case 0:
        $this->_handle_not_matched( $phone_number, $otp_type, $form );
        break;
        case 1:
        $this->_handle_matched( $user_login, $user_email, $phone_number, $otp_type, $form );
        break;
        } */
		
		if (! Chatondesk::checkPhoneNos($phone_number) ) {
            $this->_handle_not_matched($phone_number, $otp_type, $form);
        } else {
			$this->_handle_matched($user_login, $user_email, $phone_number, $otp_type, $form);
        }
    }

    /**
     * Handles OTP matched action.
     *
     * @param string $user_login   User name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_matched( $user_login, $user_email, $phone_number, $otp_type, $form )
    {
		$content = (array) json_decode(Chatondesk::SendOtpToken($form, '', $phone_number), true);	
        $status  = array_key_exists('status', $content) ? $content['status'] : '';

        switch ( $status ) {
        case 'success':
            $this->_handle_otp_sent($user_login, $user_email, $phone_number, $otp_type, $form, $content);
            break;
        default:
            $this->_handle_otp_sent_failed($user_login, $user_email, $phone_number, $otp_type, $form, $content);
            break;
        }
    }

    /**
     * Handles OTP not matched action.
     *
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_not_matched( $phone_number, $otp_type, $form )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();

        $message = str_replace('##phone##', $phone_number, self::_get_otp_invalid_format_message());
        if (self::_is_ajax_form() ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response($message, \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
        } else {
            chatondesk_site_otp_validation_form(null, null, null, $message, $otp_type, $form);
        }
    }

    /**
     * Handles OTP sent failed.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     * @param string $content      Content.
     *
     * @return void
     */
    public function _handle_otp_sent_failed( $user_login, $user_email, $phone_number, $otp_type, $form, $content )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (isset($content['description']['desc']) ) {
            $message = $content['description']['desc'];
        } elseif (isset($content['description']) && ! is_array($content['description']) ) {
            $message = $content['description'];
        } else {
            $message = str_replace('##phone##', Chatondesk::checkPhoneNos($phone_number), self::_get_otp_sent_failed_message());
        }

        if (self::_is_ajax_form() || ( 'ajax' === $form ) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response($message, \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
        } else {
            chatondesk_site_otp_validation_form(null, null, null, $message, $otp_type, $form);
        }
    }

    /**
     * Handles OTP sent success action.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     * @param string $content      Content.
     *
     * @return void
     */
    public function _handle_otp_sent( $user_login, $user_email, $phone_number, $otp_type, $form, $content )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();

        if (! empty($_SESSION[ \ChatOnDesk\FormSessionVars::WP_DEFAULT_LOST_PWD ]) ) {
            $number = Chatondesk::checkPhoneNos($phone_number);
            $mob    = str_repeat('x', strlen($number) - 4) . substr($number, -4);
        } else {
            $mob = Chatondesk::checkPhoneNos($phone_number);
        }

        $message = str_replace('##phone##', $mob, self::_get_otp_sent_message());
        if (self::_is_ajax_form() || ( 'ajax' === $form ) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response($message, \ChatOnDesk\SmsAlertConstants::SUCCESS_JSON_TYPE));
        } else {
            chatondesk_site_otp_validation_form($user_login, $user_email, $phone_number, $message, $otp_type, $form);
        }
    }

    /**
     * Gets OTP sent success message.
     *
     * @return void
     */
    public function _get_otp_sent_message()
    {
		if ( SmsAlertUtility::isPlayground()) {
			 return SmsAlertMessages::showMessage( 'OTP_SENT_PHONE' ).'</br> '.SmsAlertMessages::showMessage('OTP_SENT_plarground');
		 }else{
			return !empty(SmsAlertUtility::get_elementor_data("cod_ele_f_mobile_lbl")) ? SmsAlertUtility::get_elementor_data("cod_ele_f_mobile_lbl") : SmsAlertMessages::showMessage( 'OTP_SENT_PHONE' );
		 }
    }

    /**
     * Gets OTP sent failed message.
     *
     * @return void
     */
    public function _get_otp_sent_failed_message()
    {
        /* translators: %s: Plugin help URL */
        return wp_kses_post(sprintf(__("There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin. If you are the website admin, please browse <a href='%s' target='_blank'> here</a> for steps to resolve this error.", 'chat-on-desk'), 'https://kb.smsalert.co.in/knowledgebase/unable-to-send-otp-from-wordpress-plugin/'));
    }

    /**
     * Gets OTP sent failed due to invalid number format message.
     *
     * @return void
     */
    public function _get_otp_invalid_format_message()
    {
        /* translators: %1$s: tag, %2$s: tag */
        return sprintf(__('%1$sphone%2$s is not a valid phone number. Please enter a valid Phone Number', 'chat-on-desk'), '##', '##');
    }
}

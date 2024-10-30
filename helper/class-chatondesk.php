<?php
/**
 * Curl helper.
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
 * SmsAlertCodCurl class 
 */
class Chatondesk
{

    /**
     * Add tabs to smsalert settings at backend.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendtemplatemismatchemail( $template )
    {
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $to_mail  = chatondesk_get_option('alert_email', 'chatondesk_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
		$subject = 'Chat On Desk - Template Not Found';
        $emailcontent = get_chatondesk_template('template/emails/mismatch-template.php', $params, true);
        wp_mail($to_mail, $subject, $emailcontent, 'content-type:text/html');
    }

    /**
     * Send email For Invalid Credentials.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendemailForInvalidCred( $template )
    {
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $to_mail  = chatondesk_get_option('alert_email', 'chatondesk_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
		$subject = 'Chat On Desk - Wrong Credentials';
        $emailcontent = get_chatondesk_template('template/emails/invalid-credentials.php', $params, true);
        wp_mail($to_mail, $subject, $emailcontent, 'content-type:text/html');
    }
    
    /**
     * Send email For Dormant Account.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendemailForDormant( $template )
    {
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway', '');
        $to_mail  = chatondesk_get_option('alert_email', 'chatondesk_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
		$subject = 'Chat On Desk - Dormant Account';
        $emailcontent = get_chatondesk_template('template/emails/dormant-account.php', $params, true);
        wp_mail($to_mail, $subject, $emailcontent, 'content-type:text/html');
    }

    /**
     * Check Phone Numbers.
     *
     * @param string  $nos          numbers.
     * @param boolean $force_prefix force_prefix.
     *
     * @return string
     */
    public static function checkPhoneNos( $nos = null, $force_prefix = true )
    {
		$country_code         = chatondesk_get_option('default_country_code', 'chatondesk_general');
        $country_code_enabled = chatondesk_get_option('checkout_show_country_code', 'chatondesk_general');
        $nos                  = explode(',', $nos);
        $valid_no             = array();
        if (is_array($nos) ) {
			foreach ( $nos as $no ) {
                $no = ltrim(ltrim($no, '+'), '0'); // remove leading + and 0
                $no = preg_replace('/[^0-9]/', '', $no);// remove spaces and special characters

                if (! empty($no) ) {

                    //if ( 'on' === $country_code_enabled ) {
                    //$valid_no[] = $no;
                    //} 
                    //else {
                    if (! $force_prefix ) {
                        $no = ( substr($no, 0, strlen($country_code)) == $country_code ) ? substr($no, strlen($country_code)) : $no;
                    } else {
                        $no = ( substr($no, 0, strlen($country_code)) != $country_code ) ? $country_code . $no : $no;
                    }
					$match = preg_match(\ChatOnDesk\SmsAlertConstants::getPhonePattern(), $no);
					if ($match ) {
                        $valid_no[] = $no;
                    }
                    //}
                }
            }
        }
        if (sizeof($valid_no) > 0 ) {
            return implode(',', $valid_no);
        } else {
            return false;
        }
    }

    /**
     * Send sms.
     *
     * @param array $sms_data sms_data.
     *
     * @return array
     */
    public static function sendsms( $sms_data )
    {
        $response = false;
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
        $channel = chatondesk_get_option('chatondesk_api', 'chatondesk_gateway');

        $phone = self::checkPhoneNos($sms_data['number']);
        if ($phone === false ) {
            $data                = array();
            $data['status']      = 'error';
            $data['description'] = 'phone number not valid';
            return json_encode($data);
        }
        $template = json_decode($sms_data['sms_body'],true);
        // bail out if nothing provided
        if (empty($username) || empty($password) || empty($channel) || empty($template) ) {
            return $response;
        }
        $url = 'https://app.chatondesk.com/api/structuredpush.json';

        $fields       = array(
        'user'     => $username,
        'pwd'      => $password,
        'phone' => $phone,
        'channel'   => $channel,
        'type'   => 'template',
        'data'   => !empty($template['data'])?$template['data']:array(),
        'structureid' => $template['Structuredtemplate']['id'],
        );
		if (! empty($sms_data['schedule']) ) {
            $fields['schedule'] = $sms_data['schedule'];
        } //add on 27-08-20
        $json         = json_encode($fields);
        $fields       = apply_filters('cod_before_send_sms', $fields);
        $response     = self::callAPI($url, $fields, null);
        $response_arr = json_decode($response, true);

        $text = ! empty($template['Structuredtemplate']['name']) ? $template['Structuredtemplate']['name'] : '';
        apply_filters('cod_after_send_sms', $response_arr);

        if (!empty($response_arr['status']) && $response_arr['status'] === 'error' ) {
            $error = ( is_array($response_arr['description']) ) ? $response_arr['description']['desc'] : $response_arr['description'];
            if ($error === 'Unknown structure id' ) {
                self::sendtemplatemismatchemail($text);
            }
        }
        return $response;
    }

    /**
     * Smsalert send otp token.
     *
     * @param string $form  form.
     * @param string $email email.
     * @param string $phone phone.
     *
     * @return array
     */
    public static function SendOtpToken( $form, $email = '', $phone = '')
    {
		$phone                  = self::checkPhoneNos($phone);
        $cookie_value           = get_chatondesk_cookie($phone);
		$max_otp_resend_allowed = !empty(SmsAlertUtility::get_elementor_data("max_otp_resend_allowed"))?SmsAlertUtility::get_elementor_data("max_otp_resend_allowed"):chatondesk_get_option('max_otp_resend_allowed', 'chatondesk_general', '4');
        if ($cookie_value >= $max_otp_resend_allowed ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['description']['desc'] = __('Maximum OTP limit exceeded', 'chat-on-desk');
            return json_encode($data);
        }

        $response = false;
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
        $channel = (!empty($_REQUEST['channel']))?$_REQUEST['channel']:chatondesk_get_option('chatondesk_api', 'chatondesk_gateway');
		
        $template = chatondesk_get_option('sms_otp_send', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_OTP'));
        $template = json_decode($template,true);
		if ($phone === false ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['description']['desc'] = __('phone number not valid', 'chat-on-desk');
            return json_encode($data);
        }

        if (empty($username) || empty($password) || empty($channel) ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['description']['desc'] = __('Wrong ChatOnDesk credentials', 'chat-on-desk');
            return json_encode($data);
        }
        $url = 'https://app.chatondesk.com/chatbot/api/mverify.json';

        $fields       = array(
        'user'     => $username,
        'pwd'      => $password,
        'phone' => $phone,
        'channel'   => $channel,
        'type'   => 'template',
		'data'   => $template['data'],
        'structureid' => $template['Structuredtemplate']['id'],
        );
        $json         = json_encode($fields);
        $response     = self::callAPI($url, $fields, null);
        $response_arr = (array) json_decode($response, true);
		$text = ! empty($template['Structuredtemplate']['name']) ? $template['Structuredtemplate']['name'] : '';
        if (array_key_exists('status', $response_arr) && $response_arr['status'] === 'error' ) {
            $error = ( is_array($response_arr['description']) ) ? $response_arr['description']['desc'] : $response_arr['description'];
            if ($error == 'Unknown structure id' || $error == 'Ensure to send only Approved template' ) {
                self::sendtemplatemismatchemail($text);
                $response = false;
            }
        } else {
            create_chatondesk_cookie($phone, $cookie_value + 1);
        }

        return $response;
    }

    /**
     * Smsalert validate otp token.
     *
     * @param string $mobileno mobileno.
     * @param string $otpToken otpToken.
     *
     * @return array
     */
    public static function validateOtpToken( $mobileno, $otpToken )
    {
        if (empty($otpToken) ) {
            return false;
        }

        $response = false;
        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
        $channel = (!empty($_REQUEST['channel']))?$_REQUEST['channel']:chatondesk_get_option('chatondesk_api', 'chatondesk_gateway');
        $mobileno = self::checkPhoneNos($mobileno);
        if ($mobileno === false ) {
            $data                = array();
            $data['status']      = 'error';
            $data['description'] = 'phone number not valid';
            return json_encode($data);
        }

        if (empty($username) || empty($password) || empty($channel) ) {
            return $response;
        }
        $url = 'https://app.chatondesk.com/chatbot/api/mverify.json';

        $fields       = array(
        'user'     => $username,
        'pwd'      => $password,
        'phone'    => $mobileno,
        'channel'   => $channel,
        'code'     => $otpToken,
        );
        $response = self::callAPI($url, $fields, null);
		$content  = json_decode($response, true);
        if (isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') === 0 ) {
            clear_chatondesk_cookie($mobileno);
        }

        return $response;
    }

    /**
     * Get senderids.
     *
     * @param string $username username.
     * @param string $password password.
     *
     * @return array
     */
    public static function getSenderids( $username = null, $password = null )
    {
        if (empty($username) || empty($password) ) {
            return '';
        }

        $url = 'https://app.chatondesk.com/chatbot/api/channellist.json';

        $fields = array(
        'user' => $username,
        'pwd'  => $password,
        );
        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Get templates.
     *
     * @return array
     */
    public static function getTemplates()
    {
		$username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
		
        if (empty($username) || empty($password) ) {
            return '';
        }
        $url = 'https://app.chatondesk.com/chatbot/api/templatelist.json';

        $fields = array(
        'user'  => $username,
        'pwd'   => $password
        );

        $response = self::callAPI($url, $fields, null);
		return (array)json_decode($response, true);
    }

    /**
     * Get credits.
     *
     * @return array
     */
    public static function getCredits()
    {
		$username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');

        if (empty($username) || empty($password) ) {
            return '';
        }

        $url = 'https://app.chatondesk.com/chatbot/api/getbalance.json';

        $fields = array(
        'user' => $username,
        'pwd'  => $password,
        );
        $response = self::callAPI($url, $fields, null);
		return $response;
    }

    /**
     * Send sms xml.
     *
     * @param array $sms_datas sms_datas.
     *
     * @return array
     */
    public static function sendSmsXml( $sms_datas )
    {
        if (is_array($sms_datas) && sizeof($sms_datas) == 0 ) {
            return false;
        }

        $username = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
        $channel = chatondesk_get_option('chatondesk_api', 'chatondesk_gateway');
        $cnt = 0;$response = '';
        foreach ( $sms_datas as $sms_data ) {
            $phone = self::checkPhoneNos($sms_data['number']);
            if ($phone !== false ) {
                $response = self::sendsms($sms_data);
            }
        }
        return $response;
    }

    /**
     * CallAPI function.
     *
     * @param string $url     url.
     * @param array  $params  params.
     * @param array  $headers headers.
     *
     * @return array
     */
    public static function callAPI( $url, $params, $headers = array( 'Content-Type: application/json' ) )
    {
        $extra_params = array(
        'plugin'  => 'woocommerce',
        'website' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'version' =>\ChatOnDesk\SmsAlertConstants::SA_VERSION
        );
        $params       = ( ! is_null($params) ) ? array_merge($params, $extra_params) : $extra_params;
        $args         = array(
        'body'    => $params,
        'timeout' => 15,
        );
        $request      = wp_remote_post($url, $args);

        if (is_wp_error($request) ) {
            $data                = array();
            $data['status']      = 'error';
            $data['description'] = $request->get_error_message();
            return json_encode($data);
        }

        $resp     = wp_remote_retrieve_body($request);
        $response = (array) json_decode($resp, true);
        if (!empty($response['status']) && $response['status'] === 'error' && $response['description'] === 'invalid username/password.' ) {
            $template = 'you are using wrong credentials of chatondesk. Please check once.';
            self::sendemailForInvalidCred($template);
            chatondesk_Setting_Options::logout();
        } elseif (!empty($response['status']) && $response['status'] === 'error' && $response['description'] === 'dormant account.') {
            $template = 'your account status is dormant, when you will purchase plan then it will be active.';
            self::sendemailForDormant($template);
            chatondesk_Setting_Options::logout();
        }
        return $resp;
    }
	
	/**
     * Get country list.
     *
     * @return array
     */
    public static function country_list()
    {
        $url      = 'http://www.smsalert.co.in/api/countrylist.json';
        $response = self::callAPI($url, null, null);
        return $response;
    } 
}

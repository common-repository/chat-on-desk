<?php
/**
 * This file handles formidable form via sms notification
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

if (! is_plugin_active('formidable/formidable.php') ) {
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
 * Formidable class.
 */
class Formidable extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::FORMIDABLE;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        $user_authorize = new \ChatOnDesk\chatondesk_Setting_Options();
        if ($user_authorize->is_user_authorised() ) {
			$setting = new \FrmSettings();
            $recaptcha_v3 =  $setting->re_type;
		    if ( $recaptcha_v3 === 'v3' || $recaptcha_v3 === 'invisible') 
			{
			  add_filter('frm_recaptcha_js_url', array( $this, 'captchaReset'), 1);
			}
			add_filter('frm_validate_entry', array( $this, 'validateValue'), 10, 3);
            add_filter('frm_fields_to_validate', array( $this, 'byPassValidateFields' ), 10, 2);
            add_filter('frm_add_form_settings_section', array( $this, 'frmAddSettings' ), 10, 2);
            add_filter('frm_submit_button_html', array( $this, 'addCustomHtmlToSubmitButton'), 10, 2);
            add_action('frm_after_create_entry', array( $this, 'formidableFormSubmit'), 30, 2);
            add_filter('frm_validate_form', array( $this, 'smsalertFormidableShowWarnings'), 10, 2);
        }
    }
	
	/**
     * This function captchaReset.
     *
     * @param $url Form url
     *
     * @return void.
     */
	public function captchaReset($url)
	{
		return str_replace('frmRecaptcha','codRecaptcha',$url);
	}
	
	/**
     * This function by Pass Validate Fields.
     *
     * @param $fields Form fields
     * @param $args   args
     *
     * @return void.
     */
    public function byPassValidateFields($fields, $args)
    {
        \ChatOnDesk\SmsAlertUtility::checkSession(); 		
        if (isset($_SESSION['sa_mobile_verified'])  ) {
            unset($_SESSION['sa_mobile_verified']);
			foreach($fields as $key=>$field)
			{
				if($field->type == 'captcha')
				{
					unset( $fields[$key] );
				}
			}  
        }
        return $fields; 
    }
	
	/**
     * This function shows validation error message.
     *
     * @param $errors errors
     * @param $values values
     * @param $args   args
     *
     * @return void.
     */
    public function validateValue($errors, $values, $args)
    {   
        if (! empty($errors)) {
            return $errors;
        }
        $form_id = $values['form_id'];           
        if (isset($_REQUEST['option']) && 'chatondesk_frm_show_form_otp' === sanitize_text_field(wp_unslash($_REQUEST['option']))) {
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        } else {
            return;
        }        
        $datas = self::get_form_settings($form_id);
        $visitor_phone         = isset($datas['visitor_phone'])?$datas['visitor_phone']:'';
        $phone = !empty($_POST['item_meta'][$visitor_phone])?$_POST['item_meta'][$visitor_phone]:'';          
        if (isset($phone) && \ChatOnDesk\SmsAlertUtility::isBlank($phone)) {            
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(__('Please enter phone number.', 'chat-on-desk'), \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }
        return $this->processFormFields($phone);            
    }
    
    /**
     * This function processed form fields.
     *
     * @param string $phone User phone.
     *
     * @return bool
     */
    public function processFormFields( $phone )
    {
        global $phoneCodLogic;
        $phone_num = preg_replace('/[^0-9]/', '', $phone);
        if (! isset($phone_num) || ! \ChatOnDesk\SmsAlertUtility::validatePhoneNumber($phone_num) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(str_replace('##phone##', $phone_num, $phoneCodLogic->_get_otp_invalid_format_message()), \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }        
        chatondesk_site_challenge_otp('test', null, null, $phone_num, 'phone', null, null, 'ajax');
    }

    /**
     * Add smsalert shortcode
     *
     * @param string $button button.
     * @param array  $args   args.
     *
     * @return void
     */
    function addCustomHtmlToSubmitButton( $button, $args )
    {
        $form_id = $args['form']->id;
        global $wpdb;
        $datas = self::get_form_settings($form_id);
        if (!empty($datas)) {
            $chatondesk_enable_message     = isset($datas['chatondesk_enable_message'])?$datas['chatondesk_enable_message']:'';
            $enable_otp                 = isset($datas['chatondesk_enable_otp'])?$datas['chatondesk_enable_otp']:'';
            $visitor_phone                 = isset($datas['visitor_phone'])?$datas['visitor_phone']:'';

            if (( '1' === $chatondesk_enable_message || '1' === $enable_otp ) && $visitor_phone!='') {
                $field_table_name = $wpdb->prefix . 'frm_fields';
                $results = $wpdb->get_results("SELECT * FROM $field_table_name where `id`=$visitor_phone and `form_id`=$form_id");

                if (!empty($results) && '1' === $enable_otp ) {
                    echo do_shortcode('[cod_verify id="form1" phone_selector="#field_'.$results[0]->field_key.'" submit_selector= ".frm_button_submit" ]');
                } else {
                    $formidable_js = '
					var mob_field = jQuery("#field_' . esc_attr($results[0]->field_key) . '");
					
					mob_field.addClass("phone-valid");
					var error_show = "<span class=\"error cod_phone_error\" style=\"display:none\"></span>";
					mob_field.after(error_show);
					var default_cc = (typeof cod_country_settings !="undefined" && cod_country_settings["cod_default_countrycode"] && cod_country_settings["cod_default_countrycode"]!="") ? cod_country_settings["cod_default_countrycode"] : "";
					var show_default_cc = "";
						mob_field.intlTelInput("destroy");
					';

                    wp_add_inline_script("cod-handle-footer", $formidable_js);
                }
            }
        }
        return $button;
    }
    
    /**
     * Show warning if phone field not selected.
     *
     * @param array $errors errors.
     * @param array $values values.
     *
     * @return void
     */
    public function smsalertFormidableShowWarnings($errors, $values )
    {
        $enable_message = !empty($values['options']['chatondesk_enable_message']) ? $values['options']['chatondesk_enable_message'] : "";
        $visitor_phone     = !empty($values['options']['visitor_phone']) ? $values['options']['visitor_phone'] : "";
        $enable_otp     = !empty($values['options']['chatondesk_enable_otp']) ? $values['options']['chatondesk_enable_otp'] : "";

        if ((!empty($enable_message) || !empty($enable_otp)) && empty($visitor_phone)) {                
            $errors[] = esc_html__(
                '
					Please choose Chat On Desk phone field in Chat On Desk tab', 'chat-on-desk' 
            );
        }   
        return $errors;     
    }

    /**
     * Display get form settings
     *
     * @param int $form_id form_id.
     *
     * @return void
     */
    public function get_form_settings( $form_id )
    {
        global $wpdb;
        $form_table_name     = $wpdb->prefix . 'frm_forms';
        $data                 = $wpdb->get_results("SELECT * FROM $form_table_name where `id`=$form_id");
        $datas                 = maybe_unserialize($data[0]->options);
        return $datas;
    }

    /**
     * Display get form fields
     *
     * @param int $form_id form_id.
     *
     * @return void
     */
    public static function getFormFields( $form_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'frm_fields';
        $results     = $wpdb->get_results("SELECT * FROM $table_name where `form_id`=$form_id");
        return $results;
    }

    /**
     * Display smsalert settings
     *
     * @param array $sections sections.
     * @param array $values   values.
     *
     * @return void
     */
    public function frmAddSettings( $sections,$values )
    {
        $sections['chatondesk'] = array(
        'name'     => __('Chat On Desk', 'chat-on-desk'),
        'title'    => __('Chat On Desk Settings', 'chat-on-desk'),
        'function' => array( '\ChatOnDesk\Formidable', 'chatondesk_settings' ),
        'id'       => 'frm_chatondesk_settings',
        'icon'     => 'frm_icon_font frm_mail_bulk_icon',
        );
        return $sections;
    }

    /**
     * Display smsalert settings page
     *
     * @param array $values values.
     *
     * @return void
     */
    public static function chatondesk_settings( $values )
    {
        include plugin_dir_path(__DIR__) . '../views/formidable-settings.php';
    }

    /**
     * Process wp form submission and send sms
     *
     * @param int $entry_id entity id.
     * @param int $form_id  form id.
     *
     * @return void
     */
    public function formidableFormSubmit( $entry_id, $form_id )
    {
        $datas = self::get_form_settings($form_id);
    
        if (!empty($datas)) {
            $enable_message     = isset($datas['chatondesk_enable_message'])?$datas['chatondesk_enable_message']:'';	
			
            $visitor_phone         = isset($datas['visitor_phone'])?$datas['visitor_phone']:'';			
            $visitor_message     = isset($datas['visitor_message'])? wp_unslash($datas['visitor_message']):'';
            $admin_number         = isset($datas['admin_number'])?$datas['admin_number']:'';
            $admin_message         = isset($datas['admin_message'])? wp_unslash($datas['admin_message']):'';
            if ('1' === $enable_message && '' != $visitor_message ) {
                if (isset($_POST['item_meta'][$visitor_phone])) {
                    $phone = $_POST['item_meta'][$visitor_phone];
                    do_action('cod_send_sms', $phone,  self::parseSmsContent($form_id, $visitor_message));
                }
            }
            if (!empty($admin_number) ) {
                do_action('cod_send_sms', $admin_number, self::parseSmsContent($form_id, $admin_message));
            }
        }
    }

    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public static function isFormEnabled()
    {
        $user_authorize = new \ChatOnDesk\chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        return ( $islogged && is_plugin_active('formidable/formidable.php') ) ? true : false;
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
        if (! empty($_REQUEST['option']) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'chatondesk-validate-otp-form' ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'verification_failed';
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
		$_SESSION['sa_mobile_verified'] = true;
        if (! empty($_REQUEST['option']) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'chatondesk-validate-otp-form' ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'validated';
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
        return isset($_SESSION[ $this->form_session_var ]) ? true : $is_ajax;
    }

    /**
     * Replace variables for sms contennt
     *
     * @param int    $form_id form id.
     * @param string $content sms content to be sent.
     *
     * @return string
     */
    public static function parseSmsContent( $form_id, $content = null )
    {
        $find=array();$replace=array();
        $fields = self::getFormFields($form_id);
        foreach ($fields as $field) {
            $find[]        = '['.$field->name.'_'.$field->id.']';
            $val         = !empty($_POST['item_meta'][$field->id])?$_POST['item_meta'][$field->id]:'';
            $replace[]     = is_array($val) ? current($val) : $val;
        }
        $content = str_replace($find, $replace, $content);		
        return $content;
    }

    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {  
    }
}
new Formidable();
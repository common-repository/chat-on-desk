<?php
/**
 * Shortcode helper.
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
 * SAVerify class
 */
class SAVerify
{

    public static $response_array = array();
    private $formSessionVar       = \ChatOnDesk\FormSessionVars::SA_SHORTCODE_FORM_VERIFY;

    /**
     * Construct function
     *
     * @return string
     */
    public function __construct()
    {
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        if (! $islogged ) {
            return;
        }
        add_action('otp_verification_failed', array( $this, 'handle_failed_verification' ), 10, 3);
        add_action('otp_verification_successful', array( $this, 'handle_post_verification' ), 10, 6);
        add_action('wp_enqueue_scripts', array( $this, 'enqueue_otp_js_script' ));
        add_shortcode('cod_verify', array( $this, 'cod_verify_form' ), 100);
        $this->routeData();
        add_filter('cod_ajax', array( $this, 'is_ajax_form_in_play' ), 1, 1);
		add_action('wp_footer', array( $this, 'add_chat_widget' ), 10); 
    }
	
	 /**
     * Add popup html function
     *
     * @return string
     */
    public static function add_chat_widget()
    {
		$enable_chat_widget = chatondesk_get_option('enable_chat_widget', 'chatondesk_general');
		if ( 'on' === $enable_chat_widget )
		{
          echo '<script>	  (function(d,t){		var BASE_URL="https://app.chatondesk.com/";		var g=d.createElement(t),s=d.getElementsByTagName(t)[0];		g.src=BASE_URL+"chatbot/js/sdk.js";		g.defer = true;		g.async = true;		s.parentNode.insertBefore(g,s);		g.onload=function(){			window.cod.run({			baseUrl: BASE_URL,			resource_id: "70d19fce-bd56-11e7-97b0-eca86b71ad6e",					 })	   }	  })(document,"script");	</script>';
		}
    }
    
    /**
     * Add popup html function
     *
     * @return string
     */
    public static function add_shortcode_popup_html()
    {
        echo stripslashes(get_chatondesk_template('template/otp-popup.php', array(), true));
    }

    /**
     * Sa verify form function
     *
     * @param array $callback callback.
     *
     * @return string
     */
    public function cod_verify_form( $callback )
    {
        //ob_start();
        $phone_selector    = ( ! empty($callback['phone_selector']) ) ? $callback['phone_selector'] : '';
        $submit_selector   = ( ! empty($callback['submit_selector']) ) ? $callback['submit_selector'] : '';
        $username_selector = ( ! empty($callback['user_selector']) ) ? $callback['user_selector'] : '';
        $password_selector = ( ! empty($callback['pwd_selector']) ) ? $callback['pwd_selector'] : '';
        $placeholder       = ( ! empty($callback['placeholder']) ) ? $callback['placeholder'] : '';
        
        $uniqueNo            = rand();
        if (! empty($submit_selector) && ! preg_match('/[#.]/', $submit_selector) ) {
            $submit_selector = '[name=' . $submit_selector . ']';
        }
        if (! empty($username_selector) && ! preg_match('/[#.]/', $username_selector) ) {
            $username_selector = '[name=' . $username_selector . ']';
        }
        if (! empty($password_selector) && ! preg_match('/[#.]/', $password_selector) ) {
            $password_selector = '[name=' . $password_selector . ']';
        }

        if (! empty($phone_selector) && ! preg_match('/[#.]/', $phone_selector) ) {
            $phone_selector = 'input[name=' . $phone_selector . ']';
        }
        
        add_action('wp_footer', array( $this, 'add_shortcode_popup_html' ), 15); 
      
        return '<script>jQuery(window).on(\'load\', function(){
			add_chatondesk_button("' . $submit_selector . '","' . $phone_selector . '","'.$uniqueNo.'");
			jQuery(document).on("click", "#cod_verify_'.$uniqueNo.'",function(event){
				event.preventDefault();	
			    event.stopImmediatePropagation();
				var self = this;
				if(jQuery("' . $phone_selector . '").parents("form").find("[data-sitekey]").length>0 && jQuery("' . $phone_selector . '").parents("form").find("[data-sitekey]").attr("data-size") == "invisible")
				{
					if(grecaptcha)
					{
						grecaptcha.execute().then(function(token) {
						  send_cod_otp(self,"' . $submit_selector . '","' . $phone_selector . '","'.$username_selector.'","'.$password_selector.'");
						});
					}
				}
				else{
					send_cod_otp(self,"' . $submit_selector . '","' . $phone_selector . '","'.$username_selector.'","'.$password_selector.'");
				}				
		    });
			jQuery(document).on("keypress", "input", function(e){
				if (e.which === 13)
				{
					e.preventDefault();
					var pform 	= jQuery(this).parents("form");
					pform.find("#cod_verify_'.$uniqueNo.'").trigger("click");
				}
			});
		});
		</script>';
        //wp_add_inline_script( "cod-handle-footer", $op);
        //$content = ob_get_clean();
        //return $content;
    }

    /**
     * Ajax form function
     *
     * @param boolean $isAjax isAjax.
     *
     * @return boolean
     */
    public function is_ajax_form_in_play( $isAjax )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        return isset($_SESSION[ $this->formSessionVar ]) ? false : $isAjax;
    }

    /**
     * Route data function.
     *
     * @return string
     */
    public function routeData()
    {
        if (! array_key_exists('option', $_GET) ) {
            return;
        }
        switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
        case 'chatondesk-shortcode-ajax-verify':
            $this->_send_otp_shortcode_ajax_verify($_POST);
            exit();
            break;

        case 'chatondesk-validate-otp-form':
            $this->shortcode_otp_validate($_POST);
            exit();
            break;
        }
    }

    /**
     * Shortcode otp validate function.
     *
     * @param array $data data.
     *
     * @return string
     */
    public function shortcode_otp_validate( $data )
    {
        do_action('chatondesk_validate_otp', 'chatondesk_customer_validation_otp_token');
    }

    /**
     * Send otp shortcode function.
     *
     * @param array $getdata getdata.
     *
     * @return string
     */
    public function _send_otp_shortcode_ajax_verify( $getdata )
    {
        global $phoneCodLogic;
        \ChatOnDesk\SmsAlertUtility::checkSession();
        \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->formSessionVar);

        $phone = Chatondesk::checkPhoneNos($getdata['user_phone']);
		
        if (array_key_exists('user_phone', $getdata) && ! \ChatOnDesk\SmsAlertUtility::isBlank($getdata['user_phone']) && ! empty($phone) ) {
            $_SESSION[ $this->formSessionVar ] = $phone;
            chatondesk_site_challenge_otp('test', null, null, $phone, 'phone', null, null, 'ajax');
        } else {
            if (\ChatOnDesk\SmsAlertUtility::isBlank($getdata['user_phone'])) {
                $message = __('Please enter phone number.', 'chat-on-desk');
            } else {
                $message = str_replace('##phone##', $getdata['user_phone'], $phoneCodLogic->_get_otp_invalid_format_message());
            }
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response($message, \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }
    }

    /**
     * Handle failed verification function.
     *
     * @param string $user_login   user_login.
     * @param string $user_email   user_email.
     * @param string $phone_number phone_number.
     *
     * @return string
     */
    public function handle_failed_verification( $user_login, $user_email, $phone_number )
    {
    
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->formSessionVar ]) ) {
            return;
        }
        if (! empty($_REQUEST['option']) && 'chatondesk-validate-otp-form' === sanitize_text_field($_REQUEST['option']) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
            exit();
        } else {
            $_SESSION[ $this->formSessionVar ] = 'verification_failed';
        }
    }

    /**
     * Handle post verification function.
     *
     * @param string $redirect_to  redirect_to.
     * @param string $user_login   user_login.
     * @param string $user_email   user_email.
     * @param string $password     password.
     * @param string $phone_number phone_number.
     * @param string $extra_data   extra_data.
     *
     * @return string
     */
    public function handle_post_verification( $redirect_to, $user_login, $user_email, $password, $phone_number, $extra_data )
    {
		\ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->formSessionVar ]) ) {
            return;
        }
        if (! empty($_REQUEST['option']) && 'chatondesk-validate-otp-form' === sanitize_text_field($_REQUEST['option']) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
            exit();
        } else {
            $_SESSION[ $this->formSessionVar ] = 'validated';
        }
    }

    /**
     * Enqueue otp js function.
     *
     * @return string
     */
    public static function enqueue_otp_js_script()
    {
        $enabled_login_with_otp = chatondesk_get_option('login_with_otp', 'chatondesk_general');
        $default_login_otp      = chatondesk_get_option('buyer_login_otp', 'chatondesk_general');
        $signup_with_mobile     = chatondesk_get_option('signup_with_mobile', 'chatondesk_general', 'off');
        
        wp_enqueue_script("cod-handle-footer", COD_MOV_URL . 'js/otp-sms.min.js', array( 'jquery' ), \ChatOnDesk\SmsAlertConstants::SA_VERSION, true); //\ChatOnDesk\SmsAlertConstants::SA_VERSION, true );
        
        wp_enqueue_style('cod-login-css', COD_MOV_CSS_URL, array(), \ChatOnDesk\SmsAlertConstants::SA_VERSION, false);
        
        $wpml_lang = (apply_filters('wpml_default_language', null) != apply_filters('wpml_current_language', null))?apply_filters('wpml_current_language', null):'';
        $otp_resend_timer = !empty(SmsAlertUtility::get_elementor_data("cod_otp_re_send_timer"))?SmsAlertUtility::get_elementor_data("cod_otp_re_send_timer"):chatondesk_get_option('otp_resend_timer', 'chatondesk_general', '15');
        
        wp_localize_script(
            'cod-handle-footer',
            'cod_otp_settings',
            array(
            'otp_time'                => $otp_resend_timer,
            'valid_otp'                => SmsAlertMessages::showMessage('VALID_OTP'),
            'show_countrycode'        => chatondesk_get_option('checkout_show_country_code', 'chatondesk_general', 'off'),
            'site_url'                => site_url(),
            'ajax_url'          => admin_url('admin-ajax.php'),
            'is_checkout'             => ( ( function_exists('is_checkout') && is_checkout() ) ? true : false ),
            'login_with_otp'          => ( 'on' === $enabled_login_with_otp ? true : false ),
            'buyer_login_otp'         => ( 'on' === $default_login_otp ? true : false ),
            'hide_default_login_form' => chatondesk_get_option('hide_default_login_form', 'chatondesk_general'),
            'signup_with_mobile'      => ( 'on' === $signup_with_mobile ? true : false ),
            'lang' => $wpml_lang

            )
        );
        //wp_enqueue_script( 'chatondesk-auth' );
        
        \ChatOnDesk\SmsAlertUtility::enqueue_script_for_intellinput();
    }

    /**
     * Unset otp session function.
     *
     * @return string
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[ $this->txSessionId ]);
        unset($_SESSION[ $this->formSessionVar ]);
    }
}
new SAVerify();

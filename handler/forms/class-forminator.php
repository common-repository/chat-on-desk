<?php
/**
 * This file handles wp forms via sms notification
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

if (! is_plugin_active('forminator/forminator.php') ) {
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
 * SA_Forminator class.
 */
class SA_Forminator extends \ChatOnDesk\FormInterface
{
    
    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::FORMINATOR;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter('forminator_custom_form_submit_field_data', array( $this, 'forminatorFormResponseMessage' ), 10, 2);
        add_action('forminator_after_form_render', array( $this, 'addSmsalertShortcode' ), 10, 5);
    }
    
    /**
     * Add Shortcode for OTP and Add additional js code to your script
     *
     * @param array $id            form id.
     * @param array $form_type     form_type.  
     * @param array $post_id       form post_id.
     * @param array $form_fields   form_fields.
     * @param array $form_settings form_settings.
     *
     * @return void     
     * */     
    public function addSmsalertShortcode($id, $form_type, $post_id, $form_fields, $form_settings)
    {
        $unique_class    = 'cod-class-'.mt_rand(1, 100);
        $form_enable = chatondesk_get_option('forminator_form_status_' . $id, 'chatondesk_forminator_general', 'on');
        $otp_enable  = chatondesk_get_option('forminator_otp_' . $id, 'chatondesk_forminator_general', 'on');
        $uniqueNo = rand();
        $phone_field = chatondesk_get_option('forminator_sms_phone_' . $id, 'chatondesk_forminator_general', '');
        echo '<script>
					jQuery(document).ready(function(){
						jQuery("input[name='.$phone_field.']").addClass("phone-valid"); 
						jQuery(".forminator-custom-form .iti--separate-dial-code").css({width:"100%"});
				    });
		        </script>';  
        if ('on' === $form_enable && 'on' === $otp_enable && '' !== $phone_field ) {
              echo '<script>
			  jQuery(document).ready(function(){
			    setTimeout(function(){addSmsalertShortcode();}, 200);
			  });
					function addSmsalertShortcode()
					{				jQuery(".forminator-button-next").on("forminator.front.pagination.move", function (e) {
					 if(jQuery(".forminator-button").hasClass("forminator-button-submit") && jQuery(".'.$unique_class.'.cod-wp-form").find(".cod-otp-btn-init").length == 0)
					 {						 
				       add_chatondesk_button(".'.$unique_class.' .forminator-button-submit","input[name=' . esc_attr($phone_field) . ']","'.$uniqueNo.'");
						jQuery(document).on("click", "#cod_verify_'.$uniqueNo.'",function(event){
						event.preventDefault();
						event.stopImmediatePropagation();
						send_cod_otp(this,".'.$unique_class.' .forminator-button-submit","input[name=' . esc_attr($phone_field) . ']","","");
						});			
						jQuery(document).on("keypress", "input", function(e){
							if (e.which === 13)
							{
								e.preventDefault();
								var pform 	= jQuery(this).parents("form");
								pform.find("#cod_verify_'.$uniqueNo.'").trigger("click");
							}						
						});	
					 }
			        });
                   }
					jQuery("form#forminator-module-' . esc_attr($id) . '").each(function () 
					{
						if(!jQuery(this).hasClass("cod-wp-form"))
						{
						  jQuery(this).addClass("'.$unique_class.' cod-wp-form");
						}		
				    });
				</script>';  
            echo do_shortcode('[cod_verify phone_selector="' . esc_attr($phone_field) . '" submit_selector= ".'.$unique_class.' .forminator-button-submit" ]');
        }        
    }     
    
    /**
     * Process forminator form submission and send sms
     *
     * @param array $datas   form datas.
     * @param array $form_id form_id.
     *
     * @return void
     */
    public function forminatorFormResponseMessage($datas,$form_id)
    {
        $form_enable      = chatondesk_get_option('forminator_form_status_' . $form_id, 'chatondesk_forminator_general', 'on');
        $phone_field      = chatondesk_get_option('forminator_sms_phone_'. $form_id, 'chatondesk_forminator_general', '');
        $buyer_sms_notify = chatondesk_get_option('forminator_message_' . $form_id, 'chatondesk_forminator_general', 'on');
        $admin_sms_notify = chatondesk_get_option('forminator_admin_notification_' . $form_id, 'chatondesk_forminator_general', 'on');        
        if ('on' === $form_enable && 'on' === $buyer_sms_notify) {
            $buyer_sms_content = chatondesk_get_option('forminator_sms_body_'. $form_id, 'chatondesk_forminator_message', '');
            $mobile ='';
            foreach ($datas as $kd=>$vd) {
                $phone = $vd['name'];
                if ($phone == $phone_field) {
                    $mobile = $vd['value'];
                }
            }
            do_action('cod_send_sms', $mobile, self::parseSmsContent($buyer_sms_content, $datas));
        }
        if ('on' === $admin_sms_notify ) {
            $admin_phone_number = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
            $admin_phone_number = str_replace('post_author', '', $admin_phone_number);
            if (! empty($admin_phone_number) ) {
                $admin_sms_content = chatondesk_get_option('forminator_admin_sms_body_' . $form_id, 'chatondesk_forminator_message', '');
                do_action('cod_send_sms', $admin_phone_number, self::parseSmsContent($admin_sms_content, $datas));
            }
        }
        return $datas;
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
        return ( is_plugin_active('forminator/forminator.php') && $islogged ) ? true : false;
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
        if (! empty($_REQUEST['option']) && 'chatondesk-validate-otp-form' === sanitize_text_field(wp_unslash($_REQUEST['option'])) ) {
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
        if (! empty($_REQUEST['option']) && 'chatondesk-validate-otp-form' === sanitize_text_field(wp_unslash($_REQUEST['option'])) ) {
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
     * @param string $content   sms content to be sent.
     * @param array  $formdatas values of varibles.
     *
     * @return string
     */
    public static function parseSmsContent( $content = null, $formdatas = array() )
    {        
        $find          = array();
        $replace       = array();
        foreach ( $formdatas as $key => $data ) {
            $find[]    = '['.$data['name'].']';
            $replace[] = $data['value'];
        }
        $content      = str_replace($find, $replace, $content);    
        return $content;
    }

    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {
        if (is_plugin_active('forminator/forminator.php') ) {
            add_filter('codDefaultSettings', __CLASS__ . '::add_default_settings', 1, 2);
            add_action('cod_addTabs', array( $this, 'addTabs' ), 10);
        }
    }

    /**
     * Add tabs to smsalert settings at backend
     *
     * @param array $tabs list of tabs data.
     *
     * @return array
     */
    public static function addTabs( $tabs = array() )
    {
        $tabs['forminator']['nav']   = 'Forminator Form';
        $tabs['forminator']['icon']  = 'dashicons-list-view';
        $tabs['forminator']['inner_nav']['forminator_cust']['title']        = 'Customer Notifications';
        $tabs['forminator']['inner_nav']['forminator_cust']['tab_section']  = 'forminatorcsttemplates';
        $tabs['forminator']['inner_nav']['forminator_cust']['first_active'] = true;
        $tabs['forminator']['inner_nav']['forminator_cust']['tabContent']   = array();
        $tabs['forminator']['inner_nav']['forminator_cust']['filePath']     = 'views/forminator_customer_template.php';
        $tabs['forminator']['inner_nav']['forminator_admin']['title']       = 'Admin Notifications';
        $tabs['forminator']['inner_nav']['forminator_admin']['tab_section'] = 'forminatoradmintemplates';
        $tabs['forminator']['inner_nav']['forminator_admin']['tabContent']  = array();
        $tabs['forminator']['inner_nav']['forminator_admin']['filePath']    = 'views/forminator_admin_template.php';
        $tabs['forminator']['inner_nav']['forminator_admin']['icon']        = 'dashicons-list-view';
        $tabs['forminator']['inner_nav']['forminator_cust']['icon']         = 'dashicons-admin-users';
        return $tabs;
    }

    /**
     * Get variables to show variables above sms content template at backend settings.
     *
     * @param int $id form id.
     *
     * @return array
     */
    public static function getForminatorVariables(  $id = null )
    {
        $form      = \Forminator_API::get_form_fields($id);    
        $variables = array();
        foreach ( $form as $kss => $vss ) { 
            $field_name             = $vss->slug;
            $field_label            = !empty($vss->raw['field_label'])?$vss->raw['field_label']:''; 
            $variables[$field_name] = $field_label;
        } 
        return $variables; 
    }

    /**
     * Get default settings for the smsalert forminator forms.
     *
     * @param array $defaults smsalert backend settings default values.
     *
     * @return array
     */
    public static function add_default_settings( $defaults = array() )
    {
        $wpam_statuses = self::getForminatorForms();
        foreach ( $wpam_statuses as $ks => $vs ) {
            $defaults['chatondesk_forminator_general'][ 'forminator_admin_notification_' . $ks ] = 'off';
            $defaults['chatondesk_forminator_general'][ 'forminator_form_status_' . $ks ]        = 'off';
            $defaults['chatondesk_forminator_general'][ 'forminator_message_' . $ks ]            = 'off';
            $defaults['chatondesk_forminator_message'][ 'forminator_admin_sms_body_' . $ks ]     = '';
            $defaults['chatondesk_forminator_message'][ 'forminator_sms_body_' . $ks ]           = '';
            $defaults['chatondesk_forminator_general'][ 'forminator_sms_phone_' . $ks ]          = '';
            $defaults['chatondesk_forminator_general'][ 'forminator_sms_otp_' . $ks ]            = '';
            $defaults['chatondesk_forminator_general'][ 'forminator_otp_' . $ks ]                = '';
            $defaults['chatondesk_forminator_message'][ 'forminator_otp_sms_' . $ks ]            = '';
        }
        return $defaults;
    }

    /**
     * Get forminator forms.
     *
     * @return array
     */
    public static function getForminatorForms()
    {
        $form_list = array();        
        $forms     = \Forminator_API::get_forms(null, 1, 100, \Forminator_Form_Model::STATUS_PUBLISH);
        foreach ( $forms as $form ) {
            $form_id             = $form->id;
            $form_list[$form_id] = $form->name;
        }
        return $form_list; 
    } 
}
new SA_Forminator();

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

if (! is_plugin_active('fluentform/fluentform.php') ) {
    return; 
}
use FluentForm\App\Helpers\Protector;
use fluentform\app\Helpers\Helper;    
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * FluentForm class.
 */
class FluentForm extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::FLUENT_FORM;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_action('fluentform_submission_inserted', array( $this, 'fluentformSubmissionComplete' ), 10, 3);
        add_action('fluentform_after_form_render', array( $this, 'addSmsalertShortcode' ), 10, 1);
        add_filter('fluentform_is_form_renderable', array($this, 'addSmsalertConversational'), 10, 2);
    }
     
    /**
     * Add smsalert conversational
     *
     * @param string $renderable renderable.
     * @param string $form       form.
     *
     * @return void
     */     
    public function addSmsalertConversational($renderable, $form)
    {
                
        $formId = $form->id;
        $uniqueNo = rand();
        $form_enable = chatondesk_get_option('fluent_order_status_' .$formId, 'chatondesk_fluent_general', 'on');
        $otp_enable  = chatondesk_get_option('fluent_otp_' .$formId, 'chatondesk_fluent_general', 'on');        
        $phone_field = chatondesk_get_option('fluent_sms_phone_' .$formId, 'chatondesk_fluent_general', '');
        if ('on' === $form_enable && 'on' === $otp_enable && '' !== $phone_field ) {
            $conversational_fluent_js = '
			    jQuery(document).ready(function(){ 
					jQuery("input[name='.$phone_field.']").addClass("phone-valid");
					jQuery("div.ff_conv_app ").prepend("<form id=\"cod_conv_form\"></form>");					jQuery("#cod_conv_form").html(jQuery("#cod_conv_form").next("div.ffc_conv_form"));
					jQuery("#cod_conv_form").addClass("cod-wp-form");
					jQuery("#cod_conv_form").css({width:"100%"});					
					jQuery("#cod_conv_form input, #cod_conv_form textarea, #cod_conv_form li ").keypress(function(){ 
							addSmsalertButton();
					});					
					jQuery("#cod_conv_form input, #cod_conv_form textarea, #cod_conv_form li ").focus(function(){ 
							addSmsalertButton();
					});
					jQuery("#cod_conv_form input, #cod_conv_form textarea, #cod_conv_form li ").click(function(){ 
							setTimeout(function(){addSmsalertButton();}, 20);
					});					
                    jQuery(document).on("input","#cod_conv_form input, #cod_conv_form textarea, #cod_conv_form li",function(){
						setTimeout(function(){addSmsalertButton();}, 200);
					});
					jQuery(document).on("input","#cod_conv_form input, #cod_conv_form textarea, #cod_conv_form li",function(){						
						setTimeout(function(){addSmsalertButton();}, 200);
					});                    		
				    addSmsalertButton();
					function addSmsalertButton(){
						if(jQuery(".ff-btn.cod-default-btn-hide").length == 0){
							jQuery(".ff-btn-submit-left .f-enter-desc,.footer-inner-wrap,.ff-btn-submit-left .ff-btn").addClass("cod-default-btn-hide");	jQuery(".ff-btn-submit-left .ff-btn").clone().removeClass("cod-default-btn-hide").insertAfter(".ff-btn-submit-left .f-enter-desc");jQuery(".ff-btn-submit-left .ff-btn").not(".cod-default-btn-hide").addClass("cod-otp-btn-init chatondesk_otp_btn_submit").attr("id" ,"cod_verify_'.$uniqueNo.'").attr("name" ,"cod_verify_'.$uniqueNo.'");
						}
					}					
			    });
			';                
            wp_add_inline_script("cod-handle-footer", $conversational_fluent_js);        
            $short_codes_js = do_shortcode('[cod_verify phone_selector="' . esc_attr($phone_field) . '" submit_selector= ".ff-btn-submit-left .ff-btn" ]');            
            wp_add_inline_script("cod-handle-footer", $short_codes_js);                
            $conversational_fluent_js1 = '		
				jQuery(document).ready(function(){
					add_chatondesk_button(".ff-btn-submit-left .ff-btn","input[name='.$phone_field.']","'.$uniqueNo.'");
					jQuery(document).on("click", "#cod_verify_'.$uniqueNo.'",function(event){
					event.preventDefault();
					send_cod_otp(this,".ff-btn-submit-left .ff-btn","input[name='.$phone_field.']","","");
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
			';
            wp_add_inline_script("cod-handle-footer", $conversational_fluent_js1);        
        }
        return $renderable;
    }
    
    /**
     * Add smsalert shortcode
     *
     * @param string $form form.
     *
     * @return void
     */    
    public function addSmsalertShortcode($form)
    {
        $unique_class    = 'cod-class-'.mt_rand(1, 100);
        $form_id     = $form->id;
        $form_enable = chatondesk_get_option('fluent_order_status_' . $form_id, 'chatondesk_fluent_general', 'on');
        $otp_enable  = chatondesk_get_option('fluent_otp_' . $form_id, 'chatondesk_fluent_general', 'on');
        $phone_field = chatondesk_get_option('fluent_sms_phone_' . $form_id, 'chatondesk_fluent_general', '');
        echo '<script>
				jQuery("input[name='.$phone_field.']").addClass("phone-valid");
		</script>';
        if ('on' === $form_enable && 'on' === $otp_enable && '' !== $phone_field ) {
			$uniqueNo = rand();
            echo '<script>
				jQuery("form#fluentform_' . esc_attr($form_id) . '").each(function () 
				{
				  	if(!jQuery(this).hasClass("cod-wp-form"))
					{
					    jQuery(this).addClass("'.$unique_class.' cod-wp-form");
					}		
				});
                jQuery(document).on("elementor/popup/show", (event, id, instance) => {
					debugger;
				   add_chatondesk_button(".'.$unique_class.' .ff-btn-submit","input[name=' . esc_attr($phone_field) . ']","'.$uniqueNo.'");
					jQuery(document).on("click", "#cod_verify_'.$uniqueNo.'",function(event){
						event.stopImmediatePropagation();
						send_cod_otp(this,".'.$unique_class.' .ff-btn-submit","input[name=' . esc_attr($phone_field) . ']","","");
						});	
						initialiseCodCountrySelector(".phone-valid");	
			   });				
		   </script>';
            echo do_shortcode('[cod_verify id="form1" phone_selector="' . esc_attr($phone_field) . '" submit_selector= ".'.$unique_class.' .ff-btn-submit" ]');
        }
    }


    /**
     * Process fluent form submission and send sms
     *
     * @param array $entry_id  entry id.
     * @param array $form_data form data.
     * @param int   $form      form.
     *
     * @return void
     */
    public function fluentformSubmissionComplete( $entry_id, $form_data, $form )
    {
        $form_id          = $form->id;
        $form_enable      = chatondesk_get_option('fluent_order_status_' . $form_id, 'chatondesk_fluent_general', 'on');
        $phone_field      = chatondesk_get_option('fluent_sms_phone_' . $form_id, 'chatondesk_fluent_general', '');
        $buyer_sms_notify = chatondesk_get_option('fluent_message_' . $form_id, 'chatondesk_fluent_general', 'on');
        $admin_sms_notify = chatondesk_get_option('fluent_admin_notification_' . $form_id, 'chatondesk_fluent_general', 'on');
        
        $feeds = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $form_id)
            ->where('meta_key', '_pdf_feeds')
            ->get();
        if (!empty($feeds)) {
            $pdf_links = array();
            foreach ($feeds as $feed) {
                $hashedEntryID = base64_encode(Protector::encrypt($entry_id));
                $hashedFeedID = base64_encode(Protector::encrypt($feed->id));
                $pdf_link = admin_url('admin-ajax.php?action=fluentform_pdf_download_public&submission_id=' . $hashedEntryID . '&id=' . $hashedFeedID);
                $form_data['pdf.download_link.'.$feed->id] = $pdf_link;
                $pdf_links[] = $pdf_link;
            } 
            $form_data['pdf.download_link'] = implode(' , ', $pdf_links);            
        }        
        
        if ('on' === $form_enable && 'on' === $buyer_sms_notify && array_key_exists($phone_field, $form_data) ) {
            $buyer_sms_content = chatondesk_get_option('fluent_sms_body_' . $form_id, 'chatondesk_fluent_message', '');
            do_action('cod_send_sms', $form_data[ '' . $phone_field . '' ], self::parseSmsContent($buyer_sms_content, $form_data));
        }
        if ('on' === $admin_sms_notify ) {

            $admin_phone_number = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
            $admin_phone_number = str_replace('post_author', '', $admin_phone_number);

            if (! empty($admin_phone_number) ) {
                $admin_sms_content = chatondesk_get_option('fluent_admin_sms_body_' . $form_id, 'chatondesk_fluent_message', '');
                do_action('cod_send_sms', $admin_phone_number, self::parseSmsContent($admin_sms_content, $form_data));
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
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        return ( is_plugin_active('fluentform/fluentform.php') && $islogged ) ? true : false;
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
     * @param string $content   sms content to be sent.
     * @param array  $formdatas values of varibles.
     *
     * @return string
     */
    public static function parseSmsContent( $content = null, $formdatas = array() )
    {
        $datas = array();
        foreach ( $formdatas as $key => $data ) {
            if (is_array($data) ) {
                foreach ( $data as $k => $v ) {
                    $datas[ '[' . $k . ']' ] = $v;
                }
            } else {
                $datas[ '[' . $key . ']' ] = $data;
            }
        }
        
        $find    = array_keys($datas);
        $replace = array_values($datas);
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
        if (is_plugin_active('fluentform/fluentform.php') ) {
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
        $tabs['fluent']['nav']  = 'Fluent Form';
        $tabs['fluent']['icon'] = 'dashicons-list-view';

        $tabs['fluent']['inner_nav']['fluent_cust']['title']        = 'Customer Notifications';
        $tabs['fluent']['inner_nav']['fluent_cust']['tab_section']  = 'fluentcsttemplates';
        $tabs['fluent']['inner_nav']['fluent_cust']['first_active'] = true;
        $tabs['fluent']['inner_nav']['fluent_cust']['tabContent']   = array();
        $tabs['fluent']['inner_nav']['fluent_cust']['filePath']     = 'views/fluent_customer_template.php';

        $tabs['fluent']['inner_nav']['fluent_admin']['title']       = 'Admin Notifications';
        $tabs['fluent']['inner_nav']['fluent_admin']['tab_section'] = 'fluentadmintemplates';
        $tabs['fluent']['inner_nav']['fluent_admin']['tabContent']  = array();
        $tabs['fluent']['inner_nav']['fluent_admin']['filePath']    = 'views/fluent_admin_template.php';

        $tabs['fluent']['inner_nav']['fluent_admin']['icon'] = 'dashicons-list-view';
        $tabs['fluent']['inner_nav']['fluent_cust']['icon']  = 'dashicons-admin-users';
        $tabs['fluent']['help_links']                        = array(
        'youtube_link' => array(
        'href'   => 'https://www.youtube.com/watch?v=1l3_RPAlxZU',
        'target' => '_blank',
        'alt'    => 'Watch steps on Youtube',
        'class'  => 'btn-outline',
        'label'  => 'Youtube',
        'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

        ),
        'kb_link'      => array(
        'href'   => 'https://kb.smsalert.co.in/knowledgebase/integrate-with-fluent-forms/',
        'target' => '_blank',
        'alt'    => 'Read how to integrate with fluent form',
        'class'  => 'btn-outline',
        'label'  => 'Documentation',
        'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
        ),

        );

        return $tabs;
    }

    /**
     * Get variables to show variables above sms content template at backend settings.
     *
     * @param int $form_id form id.
     *
     * @return array
     */
    public static function getFluentVariables( $form_id = null )
    {
        $variables = array();
        $form      = wpFluent()->table('fluentform_forms')->find($form_id);
        $fields    = json_decode($form->form_fields, true);
        if (!empty($fields['fields'])) {
            foreach ($fields['fields'] as $field) {          
                if ('container' === $field['element'] ) {
                    foreach ( $field['columns'] as $ffield ) {
                        $variables = array_merge($variables, self::createVariables($ffield['fields']));
                    }
                } else {
                    $variables = array_merge($variables, self::createVariables(array($field)));
                }
            }
        }
        if (is_plugin_active('fluentforms-pdf/fluentforms-pdf.php') ) {
            $feeds = wpFluent()->table('fluentform_form_meta')
                ->where('form_id', $form_id)
                ->where('meta_key', '_pdf_feeds')
                ->get();
            $variables['pdf.download_link'] = 'Submission PDF link';            
            foreach ($feeds as $feed) {
                $feedSettings = json_decode($feed->value);
                $key = 'pdf.download_link.' . $feed->id . '';
                $variables[$key] = $feedSettings->name . ' feed PDF link';
                
            }            
        }
        return $variables;
    }

    /**
     * Set variables.
     *
     * @param array $datas fluent form datas.
     *
     * @return array
     */
    public static function createVariables( $datas = array() )
    {
        $variables = array();
        foreach ( $datas as $field ) {
            if (array_key_exists('fields', $field) ) {
                foreach ( $field['fields'] as $key => $farray ) {
                    $variables[ '' . $key . '' ] = ucwords(str_replace('_', ' ', $key));
                }
            } else {
                if (array_key_exists('name', $field['attributes']) ) {
                    $variables[ '' . $field['attributes']['name'] . '' ] = ucwords(str_replace('_', ' ', $field['attributes']['name']));
                }
            }
        }
        return $variables;
    }

    /**
     * Get default settings for the smsalert fluent forms.
     *
     * @param array $defaults smsalert backend settings default values.
     *
     * @return array
     */
    public static function add_default_settings( $defaults = array() )
    {
        $wpam_statuses = self::getFluentForms();
        foreach ( $wpam_statuses as $ks => $vs ) {
            $defaults['chatondesk_fluent_general'][ 'fluent_admin_notification_' . $ks ] = 'off';
            $defaults['chatondesk_fluent_general'][ 'fluent_order_status_' . $ks ]       = 'off';
            $defaults['chatondesk_fluent_general'][ 'fluent_message_' . $ks ]            = 'off';
            $defaults['chatondesk_fluent_message'][ 'fluent_admin_sms_body_' . $ks ]     = '';
            $defaults['chatondesk_fluent_message'][ 'fluent_sms_body_' . $ks ]           = '';
            $defaults['chatondesk_fluent_general'][ 'fluent_sms_phone_' . $ks ]          = '';
            $defaults['chatondesk_fluent_general'][ 'fluent_sms_otp_' . $ks ]            = '';
            $defaults['chatondesk_fluent_general'][ 'fluent_otp_' . $ks ]                = '';
            $defaults['chatondesk_fluent_message'][ 'fluent_otp_sms_' . $ks ]            = '';
        }
        return $defaults;
    }

    /**
     * Get fluent forms.
     *
     * @return array
     */
    public static function getFluentForms()
    {
        $fluent_forms = array();
        $forms        = wpFluent()->table('fluentform_forms')
            ->select(array( 'id', 'title' ))
            ->orderBy('id', 'DESC')
            ->get();
        foreach ( $forms as $form ) {
            $form_id                  = $form->id;
            $fluent_forms[ $form_id ] = $form->title;
        }
        return $fluent_forms;
    }
}
new FluentForm();

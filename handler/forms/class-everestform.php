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

if (! is_plugin_active('everest-forms/everest-forms.php')) {
    return; 
}


/**
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 *
 * EverestForm class.
 */
class EverestForm extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::EVEREST_FORM;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter(
            'everest_forms_builder_settings_section', array( $this,
            'saEverestformBuilderSettingsSections'), 10, 2
        );
        add_action(
            'everest_forms_builder_content_settings', array( $this,
            'saEverestformFormSettingsPanelContent' ), 10
        );        
        add_action(
            'everest_forms_process_complete', array( 
            $this, 'saSendSmsOnSubmission' ), 10, 4
        );
        add_action(
            'everest_forms_display_submit_after', array( 
            $this, 'saHandleOtpEvfForm' ), 10, 1
        );  
    }
    
    /**
     * Handle chatondesk everest form otp shortcode.
     *
     * @param rray $form_data form_data.
     *
     * @return string
     */     
    public function saHandleOtpEvfForm( $form_data)
    {    
        $form_id         = $form_data['id'];
        $form_field     = $form_data['form_fields'];        
        $unique_class   = 'cod-class-'.mt_rand(1, 100);
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        $phone_field    = !empty(
            $form_data['settings']
            ['chatondesk']['visitor_phone']
        )? $form_data['settings']
        ['chatondesk']['visitor_phone']:''; 
        $otp_enable = $form_data['settings']
        ['chatondesk']['otp_enable'];
        if (!empty($otp_enable) && !empty($phone_field)) {                
            $phone_fields = '';
            foreach ($form_field as $field) {                                    
                $phone_field == $form_data['settings']
                ['chatondesk']['visitor_phone'];
                if (strpos($phone_field, $field['id'])) {
                    $phone_fields = $field['id'];    
                }                    
            }    
            $field_id = "evf-". $form_id . "-field_" . $phone_fields;        
            echo do_shortcode(
                '[cod_verify  phone_selector="#'
                .$field_id .'" submit_selector= ".evf-submit" ]'
            );
        }            
    }    
    
    /**
     * Get form data
     *
     * @return array form data.
     */
    public function form_data()
    {
        $form_data = array();
        if (! empty($_GET['form_id']) ) {
            $form_data = evf()->form->get(
                absint(
                    $_GET['form_id']
                ), array( 'content_only' => true )
            );    
        }
        return $form_data;
    }

    /**
     * Add Tab chatondesk setting in evrestform builder section
     *
     * @param array $tab       form tab.
     * @param array $form_data form data.
     *
     * @return array
     */
    public function saEverestformBuilderSettingsSections($tab, $form_data)
    {
        $tab['chatondesk']= esc_html__(
            'Chat On Desk', 'chat-on-desk'
        );
        return $tab;
    }

    /**
     * Add Tab panel chatondesk setting in evrestform builder section
     *
     * @return void
     */
    public function saEverestformFormSettingsPanelContent()
    {
        $form_data = $this->form_data();
        $settings = isset($form_data['settings']) ? 
        $form_data['settings'] : array();        
        echo '<div class="evf-content-section evf-content-chatondesk-settings">';
        echo '<div class="evf-content-section-title">';
        esc_html_e('Chat On Desk Message Configuration', 'chat-on-desk');
        echo '</div>';
		$templates = \ChatOnDesk\Chatondesk::getTemplates();
		$alltemplates = array(''=>'Select Template');
		foreach($templates['description'] as  $template){		
			$templa = json_encode($template); 		    
			$name = $template['Structuredtemplate']['name'];
			$alltemplates[$templa] = $name;
		}        
        everest_forms_panel_field(
            'checkbox',
            'chatondesk',
            'enable_message',
            $form_data,
            esc_html__('Enable Message', 'chat-on-desk'),
            array(
            'default' => isset($this->form->enable_message) ? 
            $this->form->enable_message : '',
            'tooltip' => esc_html__(
                'Enable to send customer and admin notifications', 'chat-on-desk'
            ),
            'parent'     => 'settings',
            )
        ); 
        everest_forms_panel_field(
            'checkbox',
            'chatondesk',
            'otp_enable',
            $form_data,
            esc_html__('Enable Mobile Verification', 'chat-on-desk'),
            array(
            'default' => isset($this->form->otp_enable) ? 
            $this->form->otp_enable : '',
            'tooltip' => esc_html__('Enable Mobile Verification', 'chat-on-desk'),
            'parent'     => 'settings',
            )
        );
        everest_forms_panel_field(
            'text',
            'chatondesk',
            'admin_number',
            $form_data,
            esc_html__('Send Admin SMS To', 'chat-on-desk'),            
            array(
            'default' => isset($this->form->admin_number) ?
            $this->form->admin_number : '',
            'tooltip' => esc_html__(
                'Admin sms notifications will be sent to this number', 'chat-on-desk'
            ),
            'smarttags'  => array(
                                'type'        => 'fields',
                                'form_fields' => 'chatondesk',
                            ),
                            'parent'     => 'settings',
            )
        );
        everest_forms_panel_field(
            'select',
            'chatondesk',
            'admin_message',
            $form_data,
            esc_html__('Admin Message', 'chat-on-desk'),            
            array(
            'options' => $alltemplates,
             'parent'     => 'settings',                
            )
        );
		$token = array();
	    foreach ( $form_data['form_fields'] as $key=>$form_field ) {
			$token['['.$key .']'] = $form_field['label'];					
		}
		echo "<div id='token_field' class='hide'>
				<div class='cod_map_variable'>
				<table class='form-table' style='table-layout: fixed;'>
					<tbody id='template_list'>
					<tr class='token-row'>
					<td class='td-heading'>
					</td>
					<td class='td-dropdown'>
					</td>
					<td class='td-input'>
					</td>
					</tr>
					<tbody>
				</table>
				</div>
		<div class='cod-browse-btn'>";
		?>
		<select name="cod_token" class="cod-token hide">
		<option value="">Select Token</option>
		<option value="custom">Custom</option>
		<?php
		foreach ( $token as $vk => $vv ) {
			echo  "<option value='".esc_attr($vk)."'>".esc_attr($vv)."</option>";
		}
		?>
		</select>
		<?php
			echo '</div></div><input type="hidden" name="settings[chatondesk][admin_message_data]" class="cod_admin_data" value="'.htmlspecialchars(!empty($form_data['settings']['chatondesk']['admin_message_data'])?$form_data['settings']['chatondesk']['admin_message_data']:'').'">';	
        everest_forms_panel_field(
            'text',
            'chatondesk',
            'visitor_phone',
            $form_data,
            esc_html__('Select Phone Field', 'chat-on-desk'),            
            array(
            'default' => isset($this->form->visitor_phone) ?
            $this->form->visitor_phone : '',
            'tooltip' => esc_html__(
                'Customer sms notifications will be sent to this number', 'chat-on-desk'
            ),
            'smarttags'  => array(
                                'type'        => 'fields',
                                'form_fields' => 'chatondesk',
                            ),
                            'parent'     => 'settings',                
            )            
        );
        everest_forms_panel_field(
            'select',            
            'chatondesk',
            'visitor_message',
            $form_data,
            esc_html__('Visitor Message', 'chat-on-desk'),            
            array(
            'options' => $alltemplates,
             'parent'     => 'settings',                
            )
        );	
		echo "<div id='vtoken_field' class='hide'>	
				<div class='codv_map_variable'>
				<table class='form-table' style='table-layout: fixed;'>
					<tbody id='template_list'>
					<tr class='token-row'>
					<td class='td-heading'>
					</td>
					<td class='td-dropdown'>
					</td>
					<td class='td-input'>
					</td>
					</tr>
					<tbody>
				</table>
				</div>
		<div class='cod-browse-btn'>";
		?>
	<select name="cod_token" class="cod-token hide">
	<option value="">Select Token</option>
	<option value="custom">Custom</option>
	<?php
	foreach ( $token as $vk => $vv ) {
		echo  "<option value='".esc_attr($vk)."'>".esc_attr($vv)."</option>";
	}
	?>
	</select>
	<?php
        echo '</div></div><input type="hidden" name="settings[chatondesk][visitor_message_data]" class="cod_visitor_data" value="'.htmlspecialchars(!empty($form_data['settings']['chatondesk']['visitor_message_data'])?$form_data['settings']['chatondesk']['visitor_message_data']:'').'">';
		?>
		<script>
			jQuery(document).ready(function() {
				jQuery('#everest-forms-panel-field-chatondesk-admin_message').change(function() {
					if (jQuery(this).val() != '') {
						jQuery('#token_field .token-row').remove();
						jQuery("#token_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-dropdown'>	</td><td class='td-input'></td></tr>");
					jQuery('#token_field').removeClass('hide');
				} else {
					jQuery('#token_field').addClass('hide');
				}
					var template =jQuery(this).val();
					if(template != undefined)
					{
					 var temp = JSON.parse(template).Structuredtemplate.template;
					 var params = [];					 
					 jQuery('#token_field .td-dropdown').find('.cod-token').last().remove();								
					jQuery.each(temp.match(/##[\w_]+##/g),function(key,param){
						if(jQuery.inArray(param, params) === -1)
							  {
								  var last_ele	= jQuery('#token_field .token-row').last();								 
								  last_ele.find('.td-heading').text(param);								 
								  last_ele.find('.td-dropdown').html(jQuery('#token_field .cod-browse-btn').find('.cod-token').attr('name',param.replace(/##/g,'')).clone().removeClass('hide'));
								last_ele.after(last_ele.clone());
							  }
						params.push(param);
					});
					if(params.length > 0)
					{							
						jQuery('#token_field .token-row').last().remove();
						initialiseDropdown();
						var save_data = jQuery('.cod_admin_data').val();
						save_data = isJson(save_data)?JSON.parse(save_data):'';
						if(save_data != '')
						{
							jQuery.each(save_data.data,function(key,value){
								jQuery('#token_field select[name="'+key+'"]').val(value);
								if(jQuery('#token_field select[name="'+key+'"]').val() == null)
								{
									jQuery('#token_field select[name="'+key+'"]').val('custom');
									initialiseDropdown();
									jQuery('#token_field select[name="'+key+'"]').parents('.token-row').find(".td-dropdown .cod-token").trigger('change');
									jQuery('#token_field select[name="'+key+'"]').parents('.token-row').find('.cod_custom_text').val(value);
								}
							});
						}
					}
				}
				
			});
			jQuery('#everest-forms-panel-field-chatondesk-visitor_message').change(function() {
					if (jQuery(this).val() != '') {
						jQuery('#vtoken_field .token-row').remove();
						jQuery("#vtoken_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-dropdown'>	</td><td class='td-input'></td></tr>");
					jQuery('#vtoken_field').removeClass('hide');
				} else {
					jQuery('#vtoken_field').addClass('hide');
				}
					var template =jQuery(this).val();
					if(template != undefined)
					{
					 var temp = JSON.parse(template).Structuredtemplate.template;
					 var params = [];					 
					 jQuery('#vtoken_field .td-dropdown').find('.cod-token').last().remove();								
					jQuery.each(temp.match(/##[\w_]+##/g),function(key,param){
						if(jQuery.inArray(param, params) === -1)
							  {
								  var last_ele	= jQuery('#vtoken_field .token-row').last();								 
								  last_ele.find('.td-heading').text(param);								 
								  last_ele.find('.td-dropdown').html(jQuery('#vtoken_field .cod-browse-btn').find('.cod-token').attr('name',param.replace(/##/g,'')).clone().removeClass('hide'));
								last_ele.after(last_ele.clone());
							  }
						params.push(param);
					});
					if(params.length > 0)
					{							
						jQuery('#vtoken_field .token-row').last().remove();
						initialiseDropdown();
						var save_data = jQuery('.cod_visitor_data').val();
						save_data = isJson(save_data)?JSON.parse(save_data):'';
						if(save_data != '')
						{
							jQuery.each(save_data.data,function(key,value){
								jQuery('#vtoken_field select[name="'+key+'"]').val(value);
								if(jQuery('#vtoken_field select[name="'+key+'"]').val() == null)
								{
									jQuery('#vtoken_field select[name="'+key+'"]').val('custom');
									initialiseDropdown();
									jQuery('#vtoken_field select[name="'+key+'"]').parents('.token-row').find(".td-dropdown .cod-token").trigger('change');
									jQuery('#vtoken_field select[name="'+key+'"]').parents('.token-row').find('.cod_custom_text').val(value);
								}
							});
						}
					}
				}
				
			});
			jQuery('#everest-forms-panel-field-chatondesk-admin_message').trigger('change');
			jQuery('#everest-forms-panel-field-chatondesk-visitor_message').trigger('change');
		});
	function initialiseDropdown()
	{
		jQuery(".td-dropdown .cod-token").change(function() {
		var selected = jQuery(this).find("option:selected").text();
		if(selected == 'Custom')
		{
			jQuery(this).parents('.token-row').find('.td-input').html('<input name="cod_custom_text" type="text" class="cod_custom_text" style="width:100%" placeholder="<?php esc_html_e('Enter Value', 'chat-on-desk'); ?>">');
			initialiseTextbox();
		}
		else{
			jQuery(this).parents('.token-row').find('.td-input').html('');
		}
		});
	} 
	 function initialiseTextbox()
	{
		jQuery(".td-input .cod_custom_text").keyup(function() {
		var text_data = jQuery(this).val();
		jQuery(this).parents('.token-row').find(".cod-token option:contains(Custom)").attr('value',text_data);
		});
	} 
	jQuery(document).ready(function() {
		 jQuery('.everest-forms-save-button').click(function() {			 
			var token_array = {};
			jQuery('.cod_map_variable .cod-token').each(function(n, i){
				if(jQuery(this).attr('name') != 'cod_custom_text' && jQuery(this).val() != 'custom')
				{
				 token_array[jQuery(this).attr('name')] = jQuery(this).val();
				}
				else{
				 token_array[jQuery(this).attr('name')] = jQuery(this).parents('.token-row').find('.cod_custom_text').val();
				}
			});
			var template  = JSON.parse(jQuery("#everest-forms-panel-field-chatondesk-admin_message").val());
	        template['data'] = token_array;
			jQuery('.cod_admin_data').val(JSON.stringify(template));
			var token_array = {};
			jQuery('.codv_map_variable .cod-token').each(function(n, i){
				if(jQuery(this).attr('name') != 'cod_custom_text' && jQuery(this).val() != 'custom')
				{
				 token_array[jQuery(this).attr('name')] = jQuery(this).val();
				}
				else{
				 token_array[jQuery(this).attr('name')] = jQuery(this).parents('.token-row').find('.cod_custom_text').val();
				}
			});
			var template  = JSON.parse(jQuery("#everest-forms-panel-field-chatondesk-visitor_message").val());
	        template['data'] = token_array;
			jQuery('.cod_visitor_data').val(JSON.stringify(template));
		}); 
	});
     function isJson(str) {
		if(typeof str == 'object')
		{
			return true;
		}
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
   }
		</script>
		<?php
    }

    /**
     * Process everest form submission and send sms
     *
     * @param array $fields    form fields.
     * @param array $entry     form entries.
     * @param array $form_data form data.
     * @param int   $entry_id  entity id.
     *
     * @return void
     */
    public function saSendSmsOnSubmission($fields, $entry, $form_data, $entry_id)
    {        
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        $msg_enable     = !empty(
            $form_data['settings']
            ['chatondesk']['enable_message']
        )?
        $form_data['settings']['chatondesk']['enable_message']:'';        
        if ($msg_enable && $islogged) {            
            $phone_field     = $form_data['settings']
            ['chatondesk']['visitor_phone'];             
            $admin_number    = $form_data['settings']
            ['chatondesk']['admin_number'];        
            $visitor_message = $form_data['settings']
            ['chatondesk']['visitor_message_data'];            
            $admin_message   = $form_data['settings']
            ['chatondesk']['admin_message_data'];            
            if (! empty($phone_field) ) {
                $phone = ''; $datas = array();               
                foreach ( $fields as $key => $field ) {
					$datas['['.$key.']'] = $field['value'];
                    $evf_field  =! empty($field['value']['name']) ?
                    $field['value']['name'] : $field['name'];
                    $ev_field   = strtolower($evf_field);
                    $evf_fields = explode(" ", $ev_field);
                    $first_word = array_shift($evf_fields);                     
                    $new_words  = array_map(
                        function ($data) {
                            return ucwords($data);
                        }, $evf_fields
                    );             
                    $label_name   = implode("", $new_words);
                    $evform_field = $first_word.$label_name;    
                    $search       = '{field_id="'.$evform_field.'_' . $key . '"}';                                 
                    if ($phone_field == $search ) {
                         $phone = $field['value'];
                    }
                }                
                if (! empty($msg_enable) 
                    && ! empty($visitor_message) 
                    && ! empty($phone_field)
                ) {
                    $cst_sms = self::parseSmsContent($visitor_message, $datas);             
                    do_action('cod_send_sms', $phone, $cst_sms);
                }
                if (! empty($admin_number) ) {
                    $admin_sms = self::parseSmsContent($admin_message, $datas); 
                    do_action('cod_send_sms', $admin_number, $admin_sms);
                }
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
        return ( $islogged && (
        is_plugin_active('everest-forms/everest-forms.php') )) ? true : false;
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
    public function handle_failed_verification($user_login,$user_email,$phone_number)
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        if (! empty($_REQUEST['option']) && sanitize_text_field(
            wp_unslash($_REQUEST['option'])
        ) === 'chatondesk-validate-otp-form' 
        ) {
            wp_send_json(
                \ChatOnDesk\SmsAlertUtility::_create_json_response(
                    SmsAlertMessages::showMessage('INVALID_OTP'), 'error'
                )
            );
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
        if (! empty($_REQUEST['option']) 
            && sanitize_text_field(
                wp_unslash(
                    $_REQUEST['option']
                )
            )==='chatondesk-validate-otp-form' 
        ) {
            wp_send_json(
                \ChatOnDesk\SmsAlertUtility::_create_json_response(
                    'OTP Validated Successfully.', 'success'
                )
            );
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
     * @param string $content sms content to be sent.
     * @param array  $datas  datas.
     *
     * @return string
     */
    public static function parseSmsContent( $content = null, $datas)
    {            
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
    }
}
new EverestForm();
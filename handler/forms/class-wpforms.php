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

if (! is_plugin_active('wpforms-lite/wpforms.php') && ! is_plugin_active('wpforms/wpforms.php') ) {
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
 * WpForm class.
 */
class WpForm extends \ChatOnDesk\FormInterface
{

    /**
     * Form Session Variable.
     *
     * @return void
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::WPFORM;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_action('wpforms_process_complete', array( $this, 'wpfDevProcessComplete' ), 10, 4);
        add_filter('wpforms_field_properties', array( $this, 'wpfAddPhoneClass' ), 10, 3);
        add_filter('wpforms_display_field_after', array( $this, 'wpfDevProcessFilter' ), 10, 2);
        add_filter('wpforms_save_form_args', array( $this, 'chatondeskWpformShowWarnings' ), 10, 3);
        add_action('wpforms_process', array( $this, 'validateFields' ), 20, 3);     
        add_filter('wpforms_process_bypass_captcha', array( $this, 'beforeValidateFields' ), 10, 3);		
        $user_authorize = new \ChatOnDesk\chatondesk_Setting_Options();
        if ($user_authorize->is_user_authorised() ) {
            add_action('wpforms_form_settings_panel_content', array( $this, 'customWpformsFormSettingsPanelContent' ), 10, 1);
            add_filter('wpforms_builder_settings_sections', array( $this, 'customWpformsBuilderSettingsSections' ), 10, 2);
        }    
    }
	
	/**
     * This function by Pass Fields.
     *
     * @param $fals      Form fals
     * @param $entry     entry
     * @param $form_data form_data
     *
     * @return void.
     */
    public function beforeValidateFields( $fals, $entry, $form_data)
    {
        \ChatOnDesk\SmsAlertUtility::checkSession(); 		
        if (isset($_SESSION['sa_mobile_verified'])  ) {
            unset($_SESSION['sa_mobile_verified']);           
            return $entry;
        }
         
    }
    
    /**
     * This function shows validation error message.
     *
     * @param $fields    Form fields
     * @param $entry     entry
     * @param $form_data form_data
     *
     * @return void.
     */
    public function validateFields($fields, $entry, $form_data)
    {
        if (isset($_REQUEST['option']) && 'chatondesk_wpforms_otp' === sanitize_text_field(wp_unslash($_REQUEST['option']))) {
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        } else {
            return;
        }        
        $phone_field     = !empty($form_data['settings']['chatondesk']['visitor_phone'])?$form_data['settings']['chatondesk']['visitor_phone']:'';        
        $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
		$phone = '';
        if (! empty($phone_field_id) ) {
            $datas = array();
            foreach ( $fields as $key => $field ) {
                if ($phone_field_id == $key ) {
                    $phone = $field['value'];   
                }
            }
        }
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
     * Show warning if phone field not selected.
     *
     * @param array $form form_data.
     * @param array $data data.
     * @param array $args args.
     *
     * @return void
     */    
    public function chatondeskWpformShowWarnings($form, $data, $args)
    {
        $is_msg_enabled   = !empty($data['settings']['chatondesk']['message_enable'])?$data['settings']['chatondesk']['message_enable']:'';
        $is_otp_enable    = !empty($data['settings']['chatondesk']['otp_enable'])?$data['settings']['chatondesk']['otp_enable']:''; 
        $is_visitor_phone = !empty($data['settings']['chatondesk']['visitor_phone'])?$data['settings']['chatondesk']['visitor_phone']:'';
        
        if ((!empty($is_msg_enabled) || !empty($is_otp_enable)) && empty($is_visitor_phone)) {
            wp_send_json_error(esc_html__('Please choose Chat On Desk phone field in Chat On Desk tab.', 'chat-on-desk'));
        }
        return $form;
    } 
    
     
    /**
     * Wpf dev process filter.
     *
     * @param array $field     field.
     * @param array $form_data form_data.
     *
     * @return void
     */      
    public function wpfDevProcessFilter( $field, $form_data )
    {
        $unique_class    = 'cod-class-'.mt_rand(1, 100);
        $user_authorize  = new \ChatOnDesk\chatondesk_Setting_Options();
        $islogged        = $user_authorize->is_user_authorised();
        $phone_field     = !empty($form_data['settings']['chatondesk']['visitor_phone'])?$form_data['settings']['chatondesk']['visitor_phone']:'';
        $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
        $enabled_country = chatondesk_get_option('checkout_show_country_code', 'chatondesk_general', '');
        
        if (isset($form_data['settings']['chatondesk']['otp_enable']) && $islogged && ($field['id'] === $phone_field_id) ) {
            
            $otp_enable = $form_data['settings']['chatondesk']['otp_enable'];
            
            if ($otp_enable ) {
                echo '<script>
				jQuery("form#wpforms-form-' . esc_attr($form_data['id']) . '").each(function () 
				{
				  	if(!jQuery(this).hasClass("cod-wp-form"))
					{
					jQuery(this).addClass("'.$unique_class.' cod-wp-form");
					}		
				});		
				</script>';
                echo do_shortcode('[cod_verify id="" phone_selector=".chatondesk-phone #wpforms-' . esc_attr($form_data['id']) . '-field_' . esc_attr($phone_field_id) . '" submit_selector= ".'.$unique_class.' .wpforms-submit" ]');
            }
        }
        
        if ('on' === $enabled_country && !array_key_exists('otp_enable', $form_data['settings']['chatondesk']) ) {
            echo '<script>
			jQuery(document).ready(function(){
				initialiseCodCountrySelector(".chatondesk-phone #wpforms-' . esc_attr($form_data['id']) . '-field_' . esc_attr($phone_field_id) . '");
			})
			</script>';            
        }
    }

    /**
     * Add Tab chatondesk setting in wpform builder section
     *
     * @param array $sections  form section.
     * @param array $form_data form datas.
     *
     * @return array
     */
    public function customWpformsBuilderSettingsSections( $sections, $form_data )
    {
        $sections['chatondesk'] = 'Chat On Desk';
        return $sections;
    }

    /**
     * Add Tab panel chatondesk setting in wpform builder section
     *
     * @param object $instance tab panel object.
     *
     * @return void
     */
    public function customWpformsFormSettingsPanelContent( $instance )
    { 
		$templates = Chatondesk::getTemplates();
		$alltemplates = array();
		if(!empty($templates['description']))
		{
			foreach($templates['description'] as  $template){		
				$templa = json_encode($template); 		    
				$name = $template['Structuredtemplate']['name'];
				$alltemplates[$templa] = $name;
			}
		}
        $form_data = $instance->form_data;
		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-chatondesk">';

        echo '<div class="wpforms-panel-content-section-title"><span id="wpforms-builder-settings-notifications-title">Chat On Desk Message Configuration</span>
		</div>';
        echo '<div>
	
		<a href="https://www.youtube.com/watch?v=iYvHz6wrBbA" target="_blank" class="btn-outline"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>
		
		<a href="https://kb.chatondesk.co.in/knowledgebase/integrate-with-wpforms/#configuration" target="_blank" class="btn-outline"><span class="dashicons dashicons-format-aside"></span> Documentation</a>
		
		</div>';
        $plugin_file = is_plugin_active('wpforms-lite/wpforms.php')?'/wpforms-lite/wpforms.php':'/wpforms/wpforms.php';
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR.$plugin_file);
        $checkbox = (!empty($plugin_data['Version']) && $plugin_data['Version'] < '1.6.2.3') ? 'checkbox':'toggle';
        wpforms_panel_field(
            $checkbox,
            'chatondesk',
            'message_enable',
            $instance->form_data,
            esc_html__('Enable Message', 'chat-on-desk'),
            array( 'parent' => 'settings' )
        );
        wpforms_panel_field(
            $checkbox,
            'chatondesk',
            'otp_enable',
            $instance->form_data,
            esc_html__('Enable Mobile Verification', 'chat-on-desk'),
            array( 'parent' => 'settings' )
        );
        wpforms_panel_field(
            'text',
            'chatondesk',
            'admin_number',
             $instance->form_data,
            __('Send Admin SMS To', 'chat-on-desk'),
            array(
            'default' => '',
            'parent'  => 'settings',
            'after'   => '<p class="note">' .
                                __('Admin sms notifications will be sent to this number.', 'chat-on-desk') . '</p>',
            )
        );
		?>
		<div id="wpforms-panel-field-chatondesk-admin_message-wrap" class="wpforms-panel-field eml-msg wpforms-panel-field-select"><label for="wpforms-panel-field-chatondesk-admin_message">Admin Message</label>
		<select id="admin_message" name="settings[chatondesk][admin_message]">
		<option value="">Select Template</option>
		<?php
		foreach($alltemplates as $key=>$value)
		{
			$template = json_decode($key,true);
			$template_id = !empty($template['Structuredtemplate']['id'])?$template['Structuredtemplate']['id']:'';
			$selected = (!empty($form_data['settings']['chatondesk']['admin_message']) && $form_data['settings']['chatondesk']['admin_message'] == $template_id)?'selected':'';
			echo '<option temp="'.htmlspecialchars($key).'" value="'.$template_id.'" '.$selected.'>'.$value.'</option>';
		}
		?>
		</select>
		</div>
		<?php	
		$token = array();
	    foreach ( $form_data['fields'] as $form_field ) {
			$token['['.$form_field['label'] .']'] = $form_field['label'];					
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
        wpforms_panel_field(
            'text',
            'chatondesk',
            'visitor_phone',
             $instance->form_data,
            __('Select Phone Field', 'chat-on-desk'),
            array(
            'default'   => $form_data['settings']['chatondesk']['visitor_phone'],
            'smarttags' => array(
            'type' => 'all',
            ),
            'parent'    => 'settings',
            )
        );
		?>
		<div id="wpforms-panel-field-chatondesk-visitor_message-wrap" class="wpforms-panel-field wpforms-panel-field-select"><label for="wpforms-panel-field-chatondesk-visitor_message">Visitor Message</label>
		<select id="visitor_message" name="settings[chatondesk][visitor_message]">
		<option value="">Select Template</option>
		<?php
		foreach($alltemplates as $key=>$value)
		{
			$template = json_decode($key,true);
			$template_id = !empty($template['Structuredtemplate']['id'])?$template['Structuredtemplate']['id']:'';
			$selected = (!empty($form_data['settings']['chatondesk']['visitor_message']) && $form_data['settings']['chatondesk']['visitor_message'] == $template_id)?'selected':'';
			echo '<option temp="'.htmlspecialchars($key).'" value="'.$template_id.'" '.$selected.'>'.$value.'</option>';
		}
		?>
		</select>
		</div>
		<?php	
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
        $admin_number = isset($form_data['settings']['chatondesk']['admin_number'])?$form_data['settings']['chatondesk']['admin_number']:'';    
        echo '</div>';
        echo "<script>
		var adminnumber = '" . $admin_number . "';
		var tagInput1 	= new TagsInput({
			selector: 'wpforms-panel-field-chatondesk-admin_number',
			duplicate : false,
			max : 10,
		});
		var number = (adminnumber!='') ? adminnumber.split(',') : [];
		if(number.length > 0){
			tagInput1.addData(number);
		}	
		</script>";
		?>
		<script>
			jQuery(document).ready(function() {
				jQuery('#admin_message').change(function() {
					if (jQuery(this).val() != '') {
						jQuery('#token_field .token-row').remove();
						jQuery("#token_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-dropdown'>	</td><td class='td-input'></td></tr>");
					jQuery('#token_field').removeClass('hide');
				} else {
					jQuery('#token_field').addClass('hide');
				}
					var template =jQuery(this).find('option:selected').attr('temp');
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
					var save_data = jQuery('.cod_admin_data').val();
					save_data = isJson(save_data)?JSON.parse(save_data):'';
					if(save_data != '')
					{
						jQuery.each(save_data,function(key,value){
							jQuery('#token_field select[name="'+key+'"]').val(value);
						});
					}
					 if(params.length > 0)
					{							
						jQuery('#token_field .token-row').last().remove();
						initialiseDropdown();
						var save_data = jQuery('.cod_admin_data').val();
						save_data = isJson(save_data)?JSON.parse(save_data):'';
						if(save_data != '')
						{
							jQuery.each(save_data,function(key,value){
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
			jQuery('#visitor_message').change(function() {
					if (jQuery(this).val() != '') {
						jQuery('#vtoken_field .token-row').remove();
						jQuery("#vtoken_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-dropdown'>	</td><td class='td-input'></td></tr>");
					jQuery('#vtoken_field').removeClass('hide');
				} else {
					jQuery('#vtoken_field').addClass('hide');
				}
					var template =jQuery(this).find('option:selected').attr('temp');
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
							jQuery.each(save_data,function(key,value){
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
			jQuery('#admin_message').trigger('change');
			jQuery('#visitor_message').trigger('change');
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
		 jQuery('#wpforms-save').click(function() {			 
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
			jQuery('.cod_admin_data').val(JSON.stringify(token_array));
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
			jQuery('.cod_visitor_data').val(JSON.stringify(token_array));
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
     * Process wp form submission and send sms
     *
     * @param array $properties properties.
     * @param array $field      field.
     * @param array $form_data  form data.
     *
     * @return void
     */
    public function wpfAddPhoneClass( $properties, $field, $form_data )
    {
        $phone_field    = !empty($form_data['settings']['chatondesk']['visitor_phone'])?$form_data['settings']['chatondesk']['visitor_phone']:'';
        $phone_field_id = preg_replace('/[^0-9]/', '', $phone_field);
        if ($field['id'] === $phone_field_id) {
            $properties['container']['class'][] = 'chatondesk-phone';
        }
        return $properties;
    }

    /**
     * Process wp form submission and send sms
     *
     * @param array $fields    form fields.
     * @param array $entry     form entries.
     * @param array $form_data form data.
     * @param int   $entry_id  entity id.
     *
     * @return void
     */
    public function wpfDevProcessComplete( $fields, $entry, $form_data, $entry_id )
    {
        $user_authorize = new \ChatOnDesk\chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        $msg_enable     = !empty($form_data['settings']['chatondesk']['message_enable'])?$form_data['settings']['chatondesk']['message_enable']:'';
        if ($msg_enable && $islogged ) {
            $phone_field     = $form_data['settings']['chatondesk']['visitor_phone'];
            $admin_number    = $form_data['settings']['chatondesk']['admin_number'];
            $v_template_id = $form_data['settings']['chatondesk']['visitor_message'];
            $v_template_data = $form_data['settings']['chatondesk']['visitor_message_data'];
            $a_template_id   = $form_data['settings']['chatondesk']['admin_message'];
            $a_template_data   = $form_data['settings']['chatondesk']['admin_message_data'];
            $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
            if (! empty($phone_field_id) && !empty($v_template_id) ) {
                $phone = '';
                $datas = array();
				foreach ( $fields as $key => $field ) {
                    $datas[ '[' . $field['name'] . ']' ] = $field['value'];
                    //Please do not use === triple equal to here(Key does not match after use).
                    if ($phone_field_id == $key ) {
                        $phone = $field['value'];
                    }
                }
				$template_data = !empty($v_template_data)?self::parseSmsContent($v_template_data, $datas):array();
                do_action('cod_send_sms', $phone, json_encode(array('Structuredtemplate'=>array('id'=>$v_template_id),'data'=>$template_data)));
            }
			if (! empty($admin_number) && !empty($a_template_id) ) {
				$template_data = !empty($a_template_data)?self::parseSmsContent($a_template_data, $datas):array();
                do_action('cod_send_sms', $admin_number, json_encode(array('Structuredtemplate'=>array('id'=>$a_template_id),'data'=>$template_data)));
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
        return ( $islogged && (is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php') )) ? true : false;
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
     * @param string $content sms content to be sent.
     * @param array  $datas   values of varibles.
     *
     * @return string
     */
    public static function parseSmsContent( $content = null, $datas = array() )
    {
        $find    = array_keys($datas);
        $replace = array_values($datas);
        $content = str_replace($find, $replace, $content);
        return json_decode($content,true);
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
new WpForm();
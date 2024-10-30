<?php
/**
 * This elementor form via sms notification
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
    exit; // Exit if accessed directly.
}

if (! is_plugin_active('elementor/elementor.php') ) {
    return; 
}

if (! is_plugin_active('elementor-pro/elementor-pro.php') ) {
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
 * class CODElementor
 */  

 include __DIR__ .'../../../../elementor-pro/modules/forms/fields/field-base.php';
 include __DIR__ .'../../../../elementor-pro/modules/forms/classes/action-base.php';
class CODElementor extends \ChatOnDesk\FormInterface
{
    /**
     * Elementor form key
     *
     * @var $form_session_var
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::ELEMENTOR_FORM;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_action('elementor_pro/forms/validation', [ $this, 'checkPhoneVerified' ], 9, 2);
        add_action('elementor_pro/forms/validation', [ $this, 'elementorFormValidationErrors' ], 11, 2);
    }
    
    /**
     * This function shows validation error message.
     *
     * @param $record       Form Record
     * @param $ajax_handler Ajax Handler 
     *
     * @return void.
     */
    public function checkPhoneVerified( $record, $ajax_handler )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (isset($_SESSION['sa_mobile_verified'])  ) {
            unset($_SESSION['sa_mobile_verified']);
            $fields = $record->get_field(
                [
                'type' => 'recaptcha',
                ] 
            );

            if (empty($fields) ) {
                $fields = $record->get_field(
                    [
                    'type' => 'recaptcha_v3',
                    ] 
                );
                if (empty($fields) ) {
                      return;
                }
            }
            $field = current($fields);
            $record->remove_field($field['id']);
            return;
        }
    }

    /**
     * This function shows validation error message.
     *
     * @param $record       Form Record
     * @param $ajax_handler Ajax_Handler
     *
     * @return void.
     */
    public function elementorFormValidationErrors( $record, $ajax_handler )
    {
        if (!$ajax_handler->is_success) {
            return;
        }
        if (isset($_REQUEST['option']) && 'chatondesk_elementor_form_otp' === sanitize_text_field(wp_unslash($_REQUEST['option']))) {
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        } else {
            return;
        }

        $fields = $record->get_field(
            [
            'type' => 'cod_billing_phone',
             ] 
        );
        $field = current($fields);
        $user_phone = $field['value'];
        if (isset($user_phone) && \ChatOnDesk\SmsAlertUtility::isBlank($user_phone) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(__('Please enter phone number.', 'chat-on-desk'), \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }

        return $this->processFormFields($user_phone);
    }

    /**
     * This function processed form fields.
     *
     * @param string $user_phone User phone.
     *
     * @return bool
     */
    public function processFormFields( $user_phone )
    {
        global $phoneCodLogic;
        $phone_num = preg_replace('/[^0-9]/', '', $user_phone);

        if (! isset($phone_num) || ! \ChatOnDesk\SmsAlertUtility::validatePhoneNumber($phone_num) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(str_replace('##phone##', $getdata['user_phone'], $phoneCodLogic->_get_otp_invalid_format_message()), \ChatOnDesk\SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }
        
        chatondesk_site_challenge_otp('test', null, null, $phone_num, 'phone', null, null, 'ajax');
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
        return ( is_plugin_active('elementor/elementor.php') && $islogged && is_plugin_active('elementor-pro/elementor-pro.php') ) ? true : false;
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
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(\ChatOnDesk\SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
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
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(\ChatOnDesk\SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
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
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {

    }
}
new CODElementor();

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 *
 * class CODElementor
 */
class Elementors extends \ElementorPro\Modules\Forms\Fields\Field_Base
{
     /**
      * Get type
      *
      * @return void
      */
    public function get_type()
    {
        return 'cod_billing_phone';
    }

     /**
      * Get name
      *
      * @return void
      */
    public function get_name()
    {
        return __('ChatOnDesk', 'chat-on-desk');
    }


     /**
      * Construct
      *
      * @return void
      */
    public function __construct()
    {
        $user_authorize = new \ChatOnDesk\chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        if (!$islogged ) { 
            return; 
        }
        
        parent::__construct();

        add_action('elementor_pro/init', [ $this, 'addCustomAction' ]);
        add_action('elementor/widget/before_render_content', [ $this, 'addShortcode' ]);    
        add_filter('elementor_pro/forms/field_types', [ $this, 'registerFieldType' ]);
        add_action('elementor/preview/init', [ $this, 'editorInlineJS' ]);
        add_filter('elementor/document/before_save', array( $this, 'checkSmsalertField' ), 100, 2);
    }
    
    /**
     * EditorInlineJS
     *
     * @return void
     */
    public function editorInlineJS()
    {
        add_action(
            'wp_footer', function () {
                ?>
        <script>
        var ElementorFormSAField = ElementorFormSAField || {};
        jQuery( document ).ready( function( $ ) {
            function renderField( inputField, item, i, settings ) {
                var itemClasses = item.css_classes,
                    required = '',
                    fieldName = 'form_field_';

                if ( item.required ) {
                    required = 'required';
                }
				var itemLabel = (item.cod_billing_phone != undefined)?item.cod_billing_phone:'';
                return '<input type="cod_billing_phone" class="elementor-field-textual ' + itemClasses + '" name="' + fieldName + '" id="form_field_' + i + '" ' + required + ' placeholder="' + itemLabel + '" value="' + item.cod_default_value + '">';
            }
            
            elementor.hooks.addFilter( 'elementor_pro/forms/content_template/field/cod_billing_phone', renderField, 10, 4 );
        } );
        </script>
                <?php
            } 
        );    
    }
    
    
    /**
     * CheckSmsalertField
     *
     * @param $obj   obj
     * @param $datas datas
     *
     * @return void
     */
    public function checkSmsalertField($obj, $datas)
    {
        if (!empty($datas['elements'])) {
            $chatondesk_action_added = false;
            $chatondesk_field_added = false;    
            foreach ( $datas['elements'] as $data ) {
                if (array_key_exists('elements', $data) ) {
                    foreach ( $data['elements'] as $element ) {
                        if (array_key_exists('elements', $element) ) {
                            foreach ( $element['elements'] as $setting ) {
                                if (array_key_exists('settings', $setting) ) {
                                    if (!empty($setting['settings']['submit_actions']) && in_array("chatondesk", $setting['settings']['submit_actions']) ) {
                                                 $chatondesk_action_added = true;
                                        if (!empty($setting['settings']['form_fields'])) {
                                            foreach ($setting['settings']['form_fields'] as $fields) {
                                                if ($fields['field_type'] == 'cod_billing_phone') {
                                                    $chatondesk_field_added = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($chatondesk_action_added && !$chatondesk_field_added) {
                wp_send_json_error([ 'statusText' => esc_html__('Please add field type Chat On Desk in your form.', 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            }
        }
    }
    
    
    /**
     * RegisterFieldType
     *
     * @param $fields fields
     *
     * @return void
     */
    public function registerFieldType( $fields )
    {
        ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_field_type(self::get_type(), $this);
        $fields[ self::get_type() ] = self::get_name();
        return $fields;
    }


     /**
      * AddShortcode
      *
      * @param $form form
      *
      * @return void
      */
    public function addShortcode($form)
    {
        if ('form' === $form->get_name() ) {
            $country_flag_enable    = \ChatOnDesk\chatondesk_get_option('checkout_show_country_code', 'chatondesk_general');
            
            $settings                 = $form->get_settings();
            $form_name                 = $settings['form_name'];
            $fields                       = $settings['form_fields'];
            
            foreach ($fields as $field) {
                if ($field['field_type'] == 'cod_billing_phone' ) {
                    if ('on' === $country_flag_enable ) {
                        $uniqueNo = rand();
                        $unique  = $form->get_id();
                        echo '<script>
						jQuery(document).ready(function(){
							jQuery(".elementor-form #form-field-'.$field['custom_id'].'").each(function () 
							{
								jQuery(this).addClass("phone-valid");
							});	
							initialiseCodCountrySelector(".phone-valid");						
						})
						</script>';
                    }
                    if ('true' === $settings['otp_verification_enable'] ) {
                        
                        $uniqueNo = rand();
                        $unique  = $form->get_id();
                        echo '<script>
						jQuery(document).on("elementor/popup/show", (event, id, instance) => {
						 add_chatondesk_button(".elementor-element-'.$unique.' .elementor-field-type-submit .elementor-button","#form-field-'.$field['custom_id'].'","'.$uniqueNo.'");
							jQuery(document).on("click", "#cod_verify_'.$uniqueNo.'",function(event){
							event.stopImmediatePropagation();
							send_cod_otp(this,".elementor-element-'.$unique.' .elementor-field-type-submit .elementor-button","#form-field-'.$field['custom_id'].'","","");
							});	
							initialiseCodCountrySelector(".phone-valid");	
						});</script>'; 
                        
                        echo do_shortcode('[cod_verify id="" phone_selector="#form-field-'.$field['custom_id'].'" submit_selector=".elementor-element-'.$unique.' .elementor-field-type-submit .elementor-button"]');
                        ?>
                    <script>
                        jQuery(document).ready(function(){
                            function addModalInForm(){

                                jQuery(".modal.chatondeskModal").each(function(){

                                    var form_id = jQuery(this).attr("data-form-id");

                                    if ( form_id.indexOf("saFormNo_") > -1){

                                        var class_unq = form_id.substring(form_id.indexOf("_")+ 1);                                jQuery("#cod_verify_"+class_unq).parents('form').append(jQuery(".modal.chatondeskModal[data-form-id="+form_id+"]"));
                                    }
                                });
                            }
                            setTimeout(function(){ addModalInForm(); }, 3000);
                        });
                    </script>
                        <?php
                    }
                } elseif ('recaptcha_v3' == $field['field_type'] && 'true' === $settings['otp_verification_enable']) {
                    echo '<script>
					jQuery(document).ready(function(){
						var recaptcha_div = jQuery("#form-field-'.$field['custom_id'].'").parents("form").find("[data-sitekey]");
					    if(recaptcha_div.length>0 && recaptcha_div.attr("data-size") == "invisible")
						{
						  recaptcha_div.removeClass("elementor-g-recaptcha").addClass("g-recaptcha").attr("id","cod-grecaptcha").html("");	
						  var site_key = recaptcha_div.attr("data-sitekey");
						  grecaptcha.ready(function() {  
							grecaptcha.render("cod-grecaptcha", {
								"sitekey" : site_key
						    });
							grecaptcha.execute();
						  }); 	  
						}
					});
						</script>'; 
                }
            }
        }
    }

    /**
     *  Add action smsalert
     *
     * @return void
     */
    public function addCustomAction()
    {
        // Instantiate the action class
        $chatondesk_action = new CODSendmsms_Action_After_Submit;

        // Register the action with form widget
        \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($chatondesk_action->get_name(), $chatondesk_action);
    }
    
    /**
     * Update form widget controls.
     *
     * @param $widget form widget .
     *
     * @return void
     */    
    public function update_controls( $widget )
    {
        $elementor = ElementorPro\Plugin::elementor();

        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data) ) {
            return;
        }

        $field_controls = [
        'cod_billing_phone' => [
                    'name'         => 'cod_billing_phone',
                    'label'        => esc_html__('Placeholder', 'chat-on-desk'),
                    'type'         => Elementor\Controls_Manager::TEXT,
                    'condition'    => [
                        'field_type' => $this->get_type(),
                    ],
                    'tab'          => 'content',
                    'inner_tab'    => 'form_fields_content_tab',
                    'tabs_wrapper' => 'form_fields_tabs',
        ],
        'cod_default_value' => [
        'name'         => 'cod_default_value',
        'label'        => esc_html__('Default Value', 'chat-on-desk'),
        'type'         => Elementor\Controls_Manager::TEXT,
        'default' => '',
        'dynamic' => [
         'active' => true,
        ],
        'condition'    => [
         'field_type' => $this->get_type(),
        ],
        'tab'          => 'advanced',
        'inner_tab'    => 'form_fields_advanced_tab',
        'tabs_wrapper' => 'form_fields_tabs',
        ],
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    /**
     * Render
     *
     * @param string      $item       item
     * @param integer     $item_index item_index
     * @param Widget_Base $form       form
     *
     * @return void
     */
    public function render( $item, $item_index, $form )
    {
        $form->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual');
        
        $form->add_render_attribute('input' . $item_index, 'type', 'cod_billing_phone', true);
        $form->add_render_attribute('input' . $item_index, 'placeholder', $item['cod_billing_phone']);
        $form->add_render_attribute('input' . $item_index, 'value', $item['cod_default_value']);
        
        echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';
    }
}
new Elementors();

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 *
 * Class CODSendmsms_Action_After_Submit
 */

class CODSendmsms_Action_After_Submit extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
	
    /**
     * Get Name
     *
     * @return string
     */
    public function get_name()
    {
        return 'chatondesk';
    }

    /**
     * Get Label
     *
     * @return string
     */
    public function get_label()
    {
        return __('ChatOnDesk', 'chat-on-desk');
    }
	


    /**
     * Register Settings Section
     *
     * @param $widget widget
     *
     * @return void
     */
    public function register_settings_section( $widget )
    {
		$templates = ChatOnDesk\Chatondesk::getTemplates();
        $widget->start_controls_section(
            'section_chatondesk',
            [
            'label' => __('Chat On Desk', 'chat-on-desk'),
            'condition' => [
            'submit_actions' => $this->get_name(),
            ],
            ]
        );
        
        $widget->add_control(
            'otp_verification_enable',
            [
            'label' => __('OTP verification', 'chat-on-desk'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('On', 'chat-on-desk'),
            'label_off' => __('Off', 'chat-on-desk'),
            'return_value' => 'true',
            'default' => 'true',
            ]
        );
        
        $widget->add_control(
            'customer_sms_enable',
            [
            'label' => __('Customer SMS', 'chat-on-desk'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('On', 'chat-on-desk'),
            'label_off' => __('Off', 'chat-on-desk'),
            'return_value' => 'true',
            'default' => 'true',
            ]
        );
		$alltemplates = array(''=>'Select Template');
		if(!empty($templates['description']))
		{
			foreach($templates['description'] as  $template){		
				$templa = json_encode($template); 		    
				$name = $template['Structuredtemplate']['name'];
				$alltemplates[$templa] = $name;
			}
		}
        $widget->add_control(
            'customer_message',
            [
            'label' => __('Customer Message', 'chat-on-desk'),
			'classes'  => 'cod-token',
            'type' => \Elementor\Controls_Manager::SELECT,
			'options' => $alltemplates,
			'default' => '',
            ]
        );	
		
		$widget->add_control(
            'customer_message_data',
            [
             'type' => \Elementor\Controls_Manager::HIDDEN
			]
        );	
		
        $widget->add_control(
            'admin_sms_enable',
            [
            'label' => __('Admin SMS', 'chat-on-desk'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('On', 'chat-on-desk'),
            'label_off' => __('Off', 'chat-on-desk'),
            'return_value' => 'true',
            'default' => 'true',
            ]
        );
        
        $widget->add_control(
            'admin_number',
            [
            'label' => __('Admin Phone', 'chat-on-desk'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('8010551055', 'chat-on-desk'),
            'label_block' => true,
            'render_type' => 'none',
            'classes' => '',
            'description' => __('Send Message to admin on this number', 'chat-on-desk'),
            ]
        );

        $widget->add_control(
            'admin_message',
            [
            'label' => __('Admin Message', 'chat-on-desk'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $alltemplates,
			'default' => '',
            ]
        );
		$widget->add_control(
            'admin_message_data',
            [
            'type' => \Elementor\Controls_Manager::HIDDEN
			]
        );
      add_action(
            'wp_footer', function () {
                ?>
			<script>
			jQuery(document).ready( function() {
		    elementor.channels.editor.on('section:activated', function (sectionName, editor) {
				if(sectionName == 'section_chatondesk')
				{
					mapCustomerTemplate(jQuery(".elementor-control-customer_message select").val(),1);
					mapAdminTemplate(jQuery(".elementor-control-admin_message select").val(),1);
				}
				else{
					jQuery('#token_field').remove();
					jQuery('#atoken_field').remove();
				}
			});
			});
			jQuery(document).on("change",".elementor-control-customer_message select",function(){
				mapCustomerTemplate(jQuery(this).val());
			});
			function mapCustomerTemplate(template,old=0)
			{
				jQuery('#token_field').remove();
				jQuery('.elementor-control-customer_message').after("<div id='token_field' class='hide elementor-control'><div class='cod_map_variable'><p><small>Use fields shortcodes for send form data or write your custom text.</small></p><table class='form-table' style='table-layout: fixed;'><tbody id='template_list'><tr class='token-row'><td class='td-heading'></td><td class='td-input'></td></tr><tbody></table></div></div>");
				if(old == 0)
				{
				 jQuery('[data-setting="customer_message_data"]').val(template).trigger('input');
				}
					if (template != '') {
						jQuery('#token_field .token-row').remove();
						jQuery("#token_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-input'></td></tr>");
					jQuery('#token_field').removeClass('hide');
				} else {
					jQuery('#token_field').addClass('hide');
				}
					if(template != undefined)
					{
					 var temp = JSON.parse(template).Structuredtemplate.template;
					 var params = [];										
					jQuery.each(temp.match(/##[\w_]+##/g),function(key,param){
						if(jQuery.inArray(param, params) === -1)
							  {
								  var last_ele	= jQuery('#token_field .token-row').last();								 
								  last_ele.find('.td-heading').text(param);								 
								  last_ele.find('.td-input').html('<input name="'+param.replace(/##/g,'')+'" type="text" class="cod_custom_text cod-token" style="width:100%" placeholder="<?php esc_html_e('Enter Value', 'chat-on-desk'); ?>">');
								last_ele.after(last_ele.clone());
							  }
						params.push(param);
					});
					if(params.length > 0)
					{	
						jQuery('#token_field .token-row').last().remove();
						var save_data = jQuery('[data-setting="customer_message_data"]').val();
						save_data = isJson(save_data)?JSON.parse(save_data):'';
						if(save_data != '')
						{
							jQuery.each(save_data.data,function(key,value){
								jQuery('#token_field .cod-token[name="'+key+'"]').val(value);
							});
						}
					}
				}
			}
			jQuery(document).on("change",".elementor-control-admin_message select",function(){
				mapAdminTemplate(jQuery(this).val());
			});
			function mapAdminTemplate(template,old=0)
			{
				jQuery('#atoken_field').remove();
				jQuery('.elementor-control-admin_message').after("<div id='atoken_field' class='hide elementor-control'><div class='cod_map_variable'><p><small>Use fields shortcodes for send form data or write your custom text.</small></p><table class='form-table' style='table-layout: fixed;'><tbody id='template_list'><tr class='token-row'><td class='td-heading'></td><td class='td-input'></td></tr><tbody></table></div></div>");
				if(old == 0)
				{
				 jQuery('[data-setting="admin_message_data"]').val(template).trigger('input');
				}
					if (template != '') {
						jQuery('#atoken_field .token-row').remove();
						jQuery("#atoken_field #template_list").html("<tr class='token-row'><td class='td-heading'></td><td class='td-input'></td></tr>");
					jQuery('#atoken_field').removeClass('hide');
				} else {
					jQuery('#atoken_field').addClass('hide');
				}
					if(template != undefined)
					{
					 var temp = JSON.parse(template).Structuredtemplate.template;
					 var params = [];										
					jQuery.each(temp.match(/##[\w_]+##/g),function(key,param){
						if(jQuery.inArray(param, params) === -1)
							  {
								  var last_ele	= jQuery('#atoken_field .token-row').last();								 
								  last_ele.find('.td-heading').text(param);								 
								  last_ele.find('.td-input').html('<input name="'+param.replace(/##/g,'')+'" type="text" class="cod_custom_text cod-token" style="width:100%" placeholder="<?php esc_html_e('Enter Value', 'chat-on-desk'); ?>">');
								last_ele.after(last_ele.clone());
							  }
						params.push(param);
					});
					if(params.length > 0)
					{							
						jQuery('#atoken_field .token-row').last().remove();
						var save_data = jQuery('[data-setting="admin_message_data"]').val();
						save_data = isJson(save_data)?JSON.parse(save_data):'';
						if(save_data != '')
						{
							jQuery.each(save_data.data,function(key,value){
								jQuery('#atoken_field .cod-token[name="'+key+'"]').val(value);
							});
						}
					}
				}
			}
			jQuery(document).on('keyup','#token_field .cod_custom_text',function(){
				var token_array = {};
				jQuery('#token_field .cod_map_variable .cod-token').each(function(n, i){
					 token_array[jQuery(this).attr('name')] = jQuery(this).val();
				});
				var template  = JSON.parse(jQuery('[data-setting="customer_message_data"]').val());
				template['data'] = token_array;
				jQuery('[data-setting="customer_message_data"]').val(JSON.stringify(template)).trigger('input');
			});
			jQuery(document).on('keyup','#atoken_field .cod_custom_text',function(){
				var token_array = {};
				jQuery('#atoken_field .cod_map_variable .cod-token').each(function(n, i){
					 token_array[jQuery(this).attr('name')] = jQuery(this).val();
				});
				var template  = JSON.parse(jQuery('[data-setting="admin_message_data"]').val());
				template['data'] = token_array;
				jQuery('[data-setting="admin_message_data"]').val(JSON.stringify(template)).trigger('input');
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
        );	   
        $widget->end_controls_section();
    }


    /**
     * On Export
     *
     * @param array $element element
     *
     * @return Void
     */
    public function on_export( $element )
    {
        unset(
            $element['settings']['otp_verification_enable'],
            $element['settings']['admin_sms_enable'],
            $element['settings']['admin_number'],
            $element['settings']['admin_message'],
            $element['settings']['customer_sms_enable'],
            $element['settings']['customer_message'],
            $element['settings']['customer_message_data'],
            $element['settings']['admin_message_data']
        );
        return $element;
    }


    /**
     * Runs the action after submit
     *
     * @param $record       record
     * @param $ajax_handler Ajax_Handler
     *
     * @return void
     */
    public function run( $record, $ajax_handler )
    {
		if (!$ajax_handler->is_success) {
            return;
        }
        $admin_number             = $record->get_form_settings('admin_number');
        $admin_message             = $record->get_form_settings('admin_message_data');
        $customer_message         = $record->get_form_settings('customer_message_data');
        $customer_sms_enable    = $record->get_form_settings('customer_sms_enable');
        $admin_sms_enable         = $record->get_form_settings('admin_sms_enable');
        // get form fields
        $fields                  = $record->get('fields');
        if ('true' === $customer_sms_enable && '' !== $customer_message ) {

            $cust_phone = '';
            foreach ( $fields as $field ) {
                if ($field['type'] == 'cod_billing_phone' ) {
                    $cust_phone = $field['value'];
                }
            }
            $message = $this->parseSmsBody($fields, $customer_message);
            do_action('cod_send_sms', $cust_phone, $message);
        }

        if ('true' === $admin_sms_enable && '' !== $admin_message && '' !== $admin_number) {

            $message = $this->parseSmsBody($fields, $admin_message);
            do_action('cod_send_sms', $admin_number, $message);
        }
    }
 
    /**
     * Parse sms body
     *
     * @param $fields  fields
     * @param $message message
     *
     * @return void
     */
    public function parseSmsBody( $fields, $message )
    {

        $replaced_arr = array();

        foreach ( $fields as $key => $val ) {

            $replaced_arr['[field id=\"'.$key.'\"]'] = $val['value'];
        }

        $message = str_replace(array_keys($replaced_arr), array_values($replaced_arr), $message);
        return $message;
		
    }
	
	

}
new CODSendmsms_Action_After_Submit();
?>